<?php


namespace App\Models;

use \Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class Trip extends Model
{
    protected $table = 'trips';

    protected $primaryKey = 'iTripId';

    protected $fillable=['eDriverPaymentStatus'];

    protected $appends = ['eTypeText', 'jDate', 'iActiveText', 'vTripPaymentModeText'];

    public function getETypeTextAttribute()
    {
        if ($this->attributes['eType'] == 'Ride') {
            return 'مسافر';
        } elseif ($this->attributes['eType'] == 'Deliver') {
            return 'حمل بار';
        }
    }

    public function getJDateAttribute()
    {
        return Jalalian::forge($this->attributes['tTripRequestDate'])->format('Y/m/d');
    }

    public function getIActiveTextAttribute()
    {
        switch ($this->attributes['iActive']) {
            case 'Active':
                return 'فعال';
                break;
            case 'Finished':
                return 'پایان یافته';
                break;
            case 'Canceled':
                return 'کنسل شده';
                break;
            case 'On Going Trip':
                return 'در حال انجام';
                break;
        }
        return 'نامشخص';
    }

    public function getVTripPaymentModeTextAttribute()
    {
        switch ($this->attributes['vTripPaymentMode']) {
            case 'Cash':
                return 'نقدی';
                break;
            case 'Paypal':
                return 'پی پال';
                break;
            case 'Card':
                return 'آنلاین';
                break;
        }
        return 'نامشخص';
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