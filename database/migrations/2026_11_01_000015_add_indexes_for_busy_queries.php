<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The lookups every listing makes, and the words of every story for search.
     * Without the last one each search reads the full text of every post.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['status', 'published_at'], 'posts_status_published_index');
            $table->fullText(['title', 'sub_title', 'content'], 'posts_search');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['post_id', 'is_approved', 'created_at'], 'comments_post_approved_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_published_index');
            $table->dropFullText('posts_search');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_post_approved_index');
        });
    }
};
