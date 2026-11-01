<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }

            if (!Schema::hasColumn('posts', 'published_by')) {
                $table->foreignId('published_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (Schema::hasColumn('posts', 'published_by')) {
                $table->dropForeign(['published_by']);
                $table->dropColumn('published_by');
            }

            if (Schema::hasColumn('posts', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
