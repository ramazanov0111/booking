<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraSpaceFieldOnRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('is_available_extra_bed')
                ->after('amenities')
                ->default(0);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('extra_bed')
                ->after('status')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'is_available_extra_bed',
            ]);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'extra_bed',
            ]);
        });
    }
}
