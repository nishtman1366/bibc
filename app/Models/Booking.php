<?php


namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'cab_booking';

    protected $primaryKey = 'iCabBookingId';

    public function passenger()
    {
        return $this->hasOne(\App\Models\Passenger::class, 'iUserId', 'iUserId');
    }

    public function company()
    {
        return $this->hasOne(\App\Models\Company::class, 'iCompanyId', 'iCompanyId');
    }

    public function driver()
    {
        return $this->hasOne(\App\Models\Driver::class, 'iDriverId', 'iDriverId');
    }

    public function vehicleType()
    {
        return $this->hasOne(\App\Models\VehicleType::class, 'iVehicleTypeId', 'iVehicleTypeId');
    }

    public function trip()
    {
        return $this->hasOne(\App\Models\Trip::class, 'iTripId', 'iTripId');
    }
}