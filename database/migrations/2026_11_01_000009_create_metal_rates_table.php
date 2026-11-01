<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gold and silver rates typed in by staff, one row a day, in whole rupees per tola.
     */
    public function up(): void
    {
        Schema::create('metal_rates', function (Blueprint $table) {
            $table->id();
            $table->date('rate_date')->unique();
            $table->unsignedInteger('fine_gold');
            $table->unsignedInteger('tejabi_gold');
            $table->unsignedInteger('silver');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metal_rates');
    }
};
