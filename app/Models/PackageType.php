<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PackageType extends Model
{
    protected $table = 'package_type';

    protected $primaryKey = 'iPackageTypeId';

    protected $fillable = ['vName', 'vName_EN', 'vName_PS', 'eStatus'];
}