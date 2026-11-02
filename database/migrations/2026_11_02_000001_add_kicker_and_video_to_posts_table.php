<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The label above the title when a post leads the homepage, and a YouTube or
     * Facebook video to play in place of the featured image.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('kicker', 60)->nullable()->after('sub_title');
            $table->string('video_url', 500)->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['kicker', 'video_url']);
        });
    }
};
