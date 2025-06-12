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
        Schema::create('rooms', function (Blueprint $table) {
            $table->comment('Номера');
            $table->id();
            $table->string('name', 100)->comment('"Стандарт", "Люкс"');
            $table->text('description')->nullable();
            $table->integer('capacity')->default(1)->comment('Вместимость (гостей)');
            $table->decimal('base_price', 10)->comment('Базовая цена');
            $table->string('room_image', 2048)->nullable();
            $table->json('amenities')->nullable()->comment('Доп услуги: ["Wi-Fi", "Кондиционер"]');
            $table->boolean('is_available')->default(1)->comment('Доступен/Недоступен');
            $table->boolean('deleted')->default(0)->comment('Удален');
            $table->timestamps();
            $table->primary('id');
            $table->index('is_available', 'idx_rooms_availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
