<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['city'];

    public function cityWeatherConditions()
    {
        return $this->hasMany(CityWeatherCondition::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
