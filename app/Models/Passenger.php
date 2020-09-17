<?php


namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    protected $table = 'register_user';

    protected $primaryKey = 'iUserId';

    protected $fillable = ['vName', 'vLastName', 'vEmail', 'vPhone'];

    protected $appends = ['userId', 'fullName'];

    public function getUserIdAttribute()
    {
        return $this->attributes['iUserId'];
    }

    public function getFullNameAttribute()
    {
        return $this->attributes['vName'] . ' ' . $this->attributes['vLastName'];
    }

    public function setToken($token)
    {
        $this->apiToken = $token;
    }
}