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
        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->comment('Заблокированные даты (если номер временно недоступен)');
            $table->id();
            $table->foreignId('room_id')
                ->constrained()
                ->onDelete('cascade');
            $table->date('date_start')->comment('Дата начала когда недоступен номер');
            $table->date('date_end')->comment('Дата которой недоступен номер')->nullable();
            $table->string('reason')->comment('Причина');
            $table->timestamps();
            $table->primary('id');
            $table->unique(['room_id', 'date_start', 'date_end'], 'room_on_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
    }
};
