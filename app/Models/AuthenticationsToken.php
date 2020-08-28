<?php


namespace App\Models;


use Illuminate\Support\Str;

class AuthenticationsToken extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['user_id', 'user_type', 'token'];

    public function SetTokenAttribute($value)
    {
        $this->attributes['token'] = Str::random(64);
    }
}