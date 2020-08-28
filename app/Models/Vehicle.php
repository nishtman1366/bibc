<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'driver_vehicle';

    protected $primaryKey = 'iDriverVehicleId';

    protected $fillable = [
        'iMakeId', 'iModelId', 'iColor', 'iYear',
        'vLicencePlate', 'vLicencePlate_local', 'iCompanyId',
        'iDriverId', 'vCarType', 'eStatus'];

    protected $appends = ['vLicencePlateDetail', 'vCarTypeArray'];

    public function getVLicencePlateDetailAttribute()
    {
        $plate_split = explode('|', $this->attributes['vLicencePlate_local']);
        if (count($plate_split) > 0 && $plate_split[0] = 'IRAN') {
            return [
                'vLicencePlate_place1' => $plate_split[1],
                'vLicencePlate_alphabet' => $plate_split[2],
                'vLicencePlate_place2' => $plate_split[3],
                'vLicencePlate_city' => $plate_split[4]
            ];
        }
        return [
            'vLicencePlate_place1' => null,
            'vLicencePlate_alphabet' => null,
            'vLicencePlate_place2' => null,
            'vLicencePlate_city' => null
        ];
    }

    public function getVCarTypeArrayAttribute()
    {
        return explode(',', $this->attributes['vCarType']);
    }

    public function setVCarTypeAttribute($value)
    {
        $this->attributes['vCarType'] = implode(',', $value);
    }


    public function driver()
    {
        return $this->hasOne(\App\Models\Driver::class, 'iDriverId', 'iDriverId');
    }

    public function company()
    {
        return $this->hasOne(\App\Models\Company::class, 'iCompanyId', 'iCompanyId');
    }

    public function make()
    {
        return $this->hasOne(\App\Models\Make::class, 'iMakeId', 'iMakeId');
    }

    public function model()
    {
        return $this->hasOne(\App\Models\Model::class, 'iModelId', 'iModelId');
    }
}