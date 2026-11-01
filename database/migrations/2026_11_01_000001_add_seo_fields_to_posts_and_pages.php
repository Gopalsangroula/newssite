<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['posts', 'pages'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('seo_title')->nullable();
                $table->string('seo_description', 500)->nullable();
                $table->string('focus_keyword')->nullable();
                $table->string('og_image')->nullable();
                $table->boolean('noindex')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['posts', 'pages'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['seo_title', 'seo_description', 'focus_keyword', 'og_image', 'noindex']);
            });
        }
    }
};
