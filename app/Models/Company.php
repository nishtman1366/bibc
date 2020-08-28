<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Morilog\Jalali\Jalalian;

class Company extends Model
{
    protected $table = 'company';

    protected $primaryKey = 'iCompanyId';

    protected $fillable = [
        'vName', 'iCompanyCode', 'iParentId', 'iAreaId',
        'vLastName', 'vEmail', 'vCaddress', 'vCadress2', 'vPassword',
        'vManagerPassword', 'vPhone', 'vCity', 'vCompany', 'iPercentageShare',
        'vInviteCode', 'vVat', 'vCountry', 'eStatus'];

    protected $appends = ['date'];

    public function getDateAttribute()
    {
        return Jalalian::forge($this->attributes['created_at'])->format('%Y %B %d');
    }

    public function setVPasswordAttribute($value)
    {
        $this->attributes['vPassword'] = encrypt($value);
    }

    public function setVManagerPasswordAttribute($value)
    {
        $this->attributes['vManagerPassword'] = encrypt($value);
    }

    public function drivers()
    {
        return $this->hasMany(\App\Models\Driver::class, 'iCompanyId', 'iCompanyId');
    }

    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class, 'iAreaId', 'aId');
    }


    public function setToken($token)
    {
        $this->apiToken = $token;
    }
}