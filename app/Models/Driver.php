<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

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

    protected $appends = ['fullName'];

    public function getFullNameAttribute()
    {
        return $this->attributes['vName'] . ' ' . $this->attributes['vLastName'];
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