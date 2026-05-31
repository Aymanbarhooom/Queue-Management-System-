<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users'); 
            $table->foreignId('queue_id')
                  ->constrained('queues')
                  ->cascadeOnDelete();
            $table->unsignedInteger('number')->comment('sequential number within the queue');
            $table->dateTime('service_date')->nullable();
            $table->enum('status', ['pending', 'canceled', 'handling', 'no_show','completed', 'expired'])->default('pending');
            $table->dateTime('expected_start_time')->nullable();
            $table->unsignedSmallInteger('expected_wait_min')->nullable()->comment('in minutes');
          //  $table->unsignedSmallInteger('expected_wait_max')->nullable()->comment('in minutes');
            $table->unsignedSmallInteger('final_session_duration')->nullable()->comment('in minutes, filled after session ends');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['queue_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};