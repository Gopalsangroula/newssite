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
        Schema::create('genealogies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('father_id')->nullable();
            $table->string('mother_name')->nullable();
            $table->unsignedBigInteger('pustanumber'); // Remove ->unique()
            $table->string('name');
            $table->string('wife_name')->nullable();
            $table->enum('sex', ['male', 'female', 'other']);
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_death')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->foreign('father_id')->references('id')->on('genealogies')->onDelete('set null');
            
            // Add index for performance (but not unique)
            $table->index('pustanumber');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genealogies');
    }
};
