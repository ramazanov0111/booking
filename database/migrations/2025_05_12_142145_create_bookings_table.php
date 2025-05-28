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
        Schema::create('bookings', function (Blueprint $table) {
            $table->comment('Бронирования');
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('room_id')
                ->constrained()
                ->onDelete('cascade');
            $table->date('check_in')->comment('Дата заселения');
            $table->date('check_out')->comment('Дата выселения');
            $table->decimal('total_price', 10)->comment('Общая сумма');
            $table->enum('status', ['pending', 'confirmed', 'paid', 'canceled'])->default('pending');
            $table->enum('payment_method', ['online', 'on_site'])->default('on_site');
            $table->string('stripe_payment_id')->comment('ID платежа в Stripe')->nullable();
            $table->timestamps();
            $table->primary('id');
            $table->index(['check_in', 'check_out'], 'idx_bookings_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
