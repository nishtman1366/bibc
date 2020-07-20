<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use \Illuminate\Database\Schema\Blueprint;

class UpdateDriverVehicleTable
{
    public function up()
    {
        Capsule::schema()->table('driver_vehicle', function (Blueprint $table) {
            $table->timestamps();

            $table->foreign('iDriverId')->references('iDriverId')->on('register_driver')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('iCompanyId')->references('iCompanyId')->on('company')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('iMakeId')->references('iMakeId')->on('make')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('iModelId')->references('iModelId')->on('model')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    public function down()
    {
//        Capsule::schema()->dropIfExists('driver_vehicle');
    }
}