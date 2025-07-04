<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LocationImage extends Model
{
    protected $appends = ['image_url'];
    protected $fillable = [
        'image_path',
        'status',
        'location_id',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

     public function getImageUrlAttribute()
    {
        // Returns full URL like http://localhost:8000/storage/locations/filename.jpg
        return url(Storage::url($this->image_path));
    }
}
