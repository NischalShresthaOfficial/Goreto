<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityWeatherCondition extends Model
{
    protected $fillable = [
        'description',
        'temperature',
        'humidity',
        'city_id',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
