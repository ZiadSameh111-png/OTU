<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->comment('University latitude coordinates');
            $table->decimal('longitude', 10, 7)->nullable()->comment('University longitude coordinates');
            $table->integer('geofence_radius')->default(100)->comment('Radius in meters for attendance validation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'geofence_radius']);
        });
    }
};
