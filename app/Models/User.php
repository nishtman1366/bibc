<?php


namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'administrators';

    public function adminGroups()
    {
        return $this->belongsTo(AdminGroup::class,'iGroupId','iGroupId');
    }
}