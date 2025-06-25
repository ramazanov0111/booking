<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->comment('Конфигурация');
            $table->id();
            $table->string('key', 50)->comment('amenities, contacts');
            $table->boolean('is_array')->default(0);
            $table->json('value')->nullable()->comment('Доп услуги: ["Wi-Fi", "Кондиционер"] | Адрес гостевого дома: "ул. Примерная, д. 10"');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configs');
    }
}
