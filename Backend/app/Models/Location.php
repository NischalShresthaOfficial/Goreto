<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'plus_code',
        'city_id',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function locationImages()
    {
        return $this->hasMany(LocationImage::class);
    }

    public function favouriteLocations()
    {
        return $this->hasMany(FavouriteLocation::class);
    }

    public function locationReviews()
    {
        return $this->hasMany(LocationReview::class);
    }

     public function groupLocations()
    {
        return $this->hasMany(GroupLocation::class);
    }

    public function postLocations()
    {
        return $this->hasMany(PostLocation::class);
    }
}
