<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'place_id',
        'name',
        'latitude',
        'longitude',
        'description',
        'city_id',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function locationImages()
    {
        return $this->hasMany(LocationImage::class);
    }

    public function verifiedImages()
    {
        return $this->hasMany(LocationImage::class)->where('status', 'verified');
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

    public function locationNotifications()
    {
        return $this->hasMany(LocationNotification::class);
    }
}
