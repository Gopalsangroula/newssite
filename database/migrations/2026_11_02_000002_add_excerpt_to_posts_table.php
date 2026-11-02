<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A summary written in the editor, shown under the headline in place of the opening of the story.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('excerpt', 300)->nullable()->after('kicker');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('excerpt');
        });
    }
};
