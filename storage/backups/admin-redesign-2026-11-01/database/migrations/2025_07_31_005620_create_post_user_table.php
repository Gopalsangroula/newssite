<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreatePostUserTable extends Migration
{
    public function up(): void
    {
        // Create post_user table if it doesn't exist
        if (!Schema::hasTable('post_user')) {
            Schema::create('post_user', function (Blueprint $table) {
                $table->foreignId('post_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                $table->primary(['post_id', 'user_id']);
            });
        }

        // Migrate existing user_id data to post_user and drop user_id
        if (Schema::hasColumn('posts', 'user_id')) {
            $posts = DB::table('posts')->select('id', 'user_id')->whereNotNull('user_id')->get();
            foreach ($posts as $post) {
                DB::table('post_user')->insertOrIgnore([
                    'post_id' => $post->id,
                    'user_id' => $post->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('posts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }

    public function down(): void
    {
        // Add user_id back to posts if it doesn't exist
        if (!Schema::hasColumn('posts', 'user_id')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('id');
            });

            // Migrate data back to user_id (take first user_id from post_user)
            $postUsers = DB::table('post_user')->select('post_id', 'user_id')->get();
            foreach ($postUsers as $postUser) {
                DB::table('posts')
                    ->where('id', $postUser->post_id)
                    ->whereNull('user_id')
                    ->update(['user_id' => $postUser->user_id]);
            }
        }

        // Drop post_user table
        Schema::dropIfExists('post_user');
    }
}