<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row a day. readings holds the text for each of the 12 signs.
     */
    public function up(): void
    {
        Schema::create('rashifals', function (Blueprint $table) {
            $table->id();
            $table->date('rashifal_date')->unique();
            $table->string('author')->nullable();
            $table->json('readings');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rashifals');
    }
};
