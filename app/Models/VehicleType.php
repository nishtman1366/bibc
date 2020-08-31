<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    protected $table = 'vehicle_type';

    protected $primaryKey = 'iVehicleTypeId';

    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class, 'vSavarArea', 'aId');
    }
}