<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_statistics_id')
                  ->constrained('user_statistics')
                  ->cascadeOnDelete();
            $table->foreignId('ticket_id')
                  ->constrained('tickets')
                  ->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->unsignedSmallInteger('duration')->nullable()->comment('in minutes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sessions');
    }
};