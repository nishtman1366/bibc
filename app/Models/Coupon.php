<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupon';

    protected $primaryKey = 'iCouponId';

    protected $fillable = ['vCouponCode', 'tDescription', 'fDiscount', 'eType', 'eValidityType',
        'dActiveDate', 'dExpiryDate', 'iUsageLimit', 'iUsed', 'eOnePerUser', 'eForFirstTrip', 'eStatus'];

    protected $appends = ['fDiscountText', 'eStatusText'];

    public function getFDiscountTextAttribute()
    {
        if ($this->attributes['eType'] == 'percentage') {
            return $this->attributes['fDiscount'] . '%';
        } else {
            return addCurrencySymbol($this->attributes['fDiscount']);
        }
    }

    public function getEStatusTextAttribute()
    {
        switch ($this->attributes['eStatus']) {
            case 'Active':
                return 'فعال';
                break;
            case 'Inactive':
                return 'غیرفعال';
                break;
            case 'Deleted':
                return 'حذف شده';
                break;
            default:
                return 'نامشخص';
                break;

        }
    }
}