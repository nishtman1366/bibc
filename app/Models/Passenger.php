<?php


namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    protected $table = 'register_user';

    protected $primaryKey = 'iUserId';

    protected $appends = ['fullName'];

    public function getFullNameAttribute()
    {
        return $this->attributes['vName'] . ' ' . $this->attributes['vLastName'];
    }
}