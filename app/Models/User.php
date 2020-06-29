<?php


namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'administrators';

    protected $primaryKey = 'iAdminId';

    protected $fillable = [
        'vFirstName', 'vLastName', 'vEmail', 'vPassword',
        'iGroupId', 'vAccessOptions', 'area', 'vContactNo',
        'eStatus'];

    public function setVPasswordAttribute($value)
    {
        $this->attributes['vPassword'] = encrypt($value);
    }

    public function adminGroups()
    {
        return $this->belongsTo(AdminGroup::class, 'iGroupId', 'iGroupId');
    }
}