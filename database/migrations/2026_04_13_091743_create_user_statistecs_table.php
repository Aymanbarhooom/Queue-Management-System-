<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->unsignedInteger('total_bookings')->default(0);
            $table->unsignedInteger('total_on_time')->default(0);
            $table->unsignedInteger('total_cancellations')->default(0);
            $table->unsignedInteger('total_moved_to_no_show')->default(0);
            $table->unsignedInteger('total_no_show_present')->default(0);
            $table->unsignedInteger('total_no_show_absent')->default(0);
            $table->decimal('session_avg_duration', 6, 2)->nullable()
                  ->comment('in minutes, auto-updated by the system');
            $table->timestamps();
            $table->unique(['user_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_statistics');
    }
};