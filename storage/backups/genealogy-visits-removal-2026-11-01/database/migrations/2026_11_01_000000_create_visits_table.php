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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('path', 1024);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('device', 10);
            $table->string('browser', 30);
            $table->string('referrer', 1024)->nullable();
            $table->char('visitor_key', 40);
            $table->timestamp('created_at')->useCurrent();

            // Each index starts with what the dashboard filters on and carries the
            // columns it counts, so those queries never have to read the table rows.
            $table->index(['created_at', 'device', 'visitor_key'], 'visits_created_device_visitor_index');
            $table->index(['created_at', 'ip_address'], 'visits_created_ip_index');
            $table->index(['post_id', 'created_at', 'visitor_key'], 'visits_post_created_visitor_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
