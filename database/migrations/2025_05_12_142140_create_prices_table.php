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
        Schema::create('prices', function (Blueprint $table) {
            $table->comment('Динамические цены');
            $table->id();
            $table->foreignId('room_id')
                ->constrained()
                ->onDelete('cascade');
            $table->date('start_date')->comment('Начало периода действия');
            $table->date('end_date')->comment('Конец периода действия');
            $table->decimal('price', 10)->comment('Цена в указанный период');
            $table->timestamps();
            $table->primary('id');
            $table->unique(['room_id', 'start_date', 'end_date'], 'room_on_period'); // Цена на номер уникальна для периода
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
