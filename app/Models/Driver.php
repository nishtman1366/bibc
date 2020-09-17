<?php


namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class Driver extends Model
{
    protected $fillable = [
        'vName', 'vLastName', 'vCountry', 'vCode', 'vEmail',
        'vLoginId', 'vPassword', 'iCompanyId', 'vPhone', 'vCity',
        'vImage', 'vPaymentEmail', 'vBankAccountHolderName',
        'vBankLocation', 'vBankName', 'vAccountNumber', 'vBIC_SWIFT_Code',
        'tProfileDescription', 'vCurrencyDriver', 'vLang', 'eStatus'
    ];
    protected $table = 'register_driver';

    protected $primaryKey = 'iDriverId';

    protected $appends = ['userId', 'fullName', 'jLastOnline', 'isDriverOnline'];

    public function getUserIdAttribute()
    {
        return $this->attributes['iDriverId'];
    }

    public function getFullNameAttribute()
    {
        return $this->attributes['vName'] . ' ' . $this->attributes['vLastName'];
    }

    public function getJLastOnlineAttribute()
    {
        if ($this->attributes['tLastOnline'] === '0000-00-00 00:00:00') return '---';
        return Jalalian::forge($this->attributes['tLastOnline'])->format('Y/m/d h:i:s');
    }

    public function getIsDriverOnlineAttribute()
    {
        $now = Carbon::now()->subMinutes(5);
        if ($this->attributes['tLastOnline'] > $now) return true;
        return false;
    }

    public function setVPasswordAttribute($value)
    {
        $this->attributes['vPassword'] = encrypt($value);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'iCompanyId', 'iCompanyId');
    }


    public function setToken($token)
    {
        $this->apiToken = $token;
    }
}