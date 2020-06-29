<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'savar_area';

    protected $primaryKey = 'aId';
    
    protected $fillable=[
        'sAreaName', 'sAreaNamePersian', 'sSpecialArea', 'sPriority',
        'sPolygonArea', 'sFeatureCollection', 'sActive', 'mapCenter', 'mapZoom'];
}