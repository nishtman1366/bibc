<?php


namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $table = 'trips';

    protected $primaryKey = 'iTripId';

    protected $appends = ['eTypeText'];

    public function getETypeTextAttribute()
    {
        if ($this->attributes['eType'] == 'Ride') {
            return 'مسافر';
        } elseif ($this->attributes['eType'] == 'Deliver') {
            return 'حمل بار';
        }
    }

    public function driver()
    {
        return $this->hasOne(\App\Models\Driver::class, 'iDriverId', 'iDriverId');
    }

    public function passenger()
    {
        return $this->hasOne(\App\Models\Passenger::class, 'iUserId', 'iUserId');
    }

    public function vehicleType()
    {
        return $this->hasOne(\App\Models\VehicleType::class, 'iVehicleTypeId', 'iVehicleTypeId');
    }

    public function vehicle()
    {
        return $this->hasOne(\App\Models\Vehicle::class, 'iDriverVehicleId', 'iDriverVehicleId');
    }
}