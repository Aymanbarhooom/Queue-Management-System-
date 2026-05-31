<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_swaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_ticket_id')
                  ->constrained('tickets')
                  ->cascadeOnDelete()
                  ->comment('the ticket that initiated the swap request');
            $table->foreignId('receiver_ticket_id')
                  ->constrained('tickets')
                  ->cascadeOnDelete()
                  ->comment('the ticket that received the swap request');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'canceled'])
                  ->default('pending');
            $table->timestamps();

            $table->unique(['requester_ticket_id', 'receiver_ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_swaps');
    }
};