<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'savar_area';

    protected $primaryKey = 'aId';

    protected $fillable = [
        'sAreaName', 'sAreaNamePersian', 'sSpecialArea', 'sPriority',
        'sPolygonArea', 'sFeatureCollection', 'sActive', 'mapCenter', 'mapZoom', 'price_details'];

    protected $appends = ['userId', 'fullName', 'priceDetails'];

    public function getUserIdAttribute()
    {
        return $this->attributes['aId'];
    }

    public function getFullNameAttribute()
    {
        return $this->attributes['sAreaNamePersian'] . '(' . $this->attributes['sAreaName'] . ')';
    }

    public function getPriceDetailsAttribute()
    {
        return json_decode($this->attributes['price_details']);
    }

    public function companies()
    {
        return $this->hasMany(\App\Models\Company::class, 'iAreaId', 'aId');
    }
}