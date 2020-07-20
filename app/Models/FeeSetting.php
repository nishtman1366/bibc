<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeSetting extends Model
{
    protected $table = 'SnapSettings';

    protected $fillable = ['setting_name', 'setting_value'];
}