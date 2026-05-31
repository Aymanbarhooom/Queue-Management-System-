<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->comment('manager');
            $table->foreignId('category_id')
                  ->constrained('categories');
            $table->string('name');
            $table->text('description')->nullable();
           // $table->time('open_hour')->nullable();
            //$table->time('close_hour')->nullable();
            $table->decimal('longitude', 10, 7);
            $table->decimal('latitude', 10, 7);
            $table->string('phone');
            $table->string('image')->nullable();
            $table->float('avg_rating')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};