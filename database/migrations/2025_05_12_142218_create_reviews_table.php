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
        Schema::create('reviews', function (Blueprint $table) {
            $table->comment('Отзывы');
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('room_id')->constrained();
            $table->enum('rating', [1, 2, 3, 4, 5])
                ->comment('Рейтинг от 1 до 5');
            $table->text('comment');
            $table->boolean('published')->default(0)->comment('Опубликован');
            $table->boolean('deleted')->default(0)->comment('Удален');
            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
