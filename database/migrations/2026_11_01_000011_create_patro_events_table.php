<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Festivals and holidays on the patro. uid is set on events from the public
     * calendar, so that importing it again never adds them twice.
     */
    public function up(): void
    {
        Schema::create('patro_events', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->nullable()->unique();
            $table->date('event_date')->index();
            $table->string('name');
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patro_events');
    }
};
