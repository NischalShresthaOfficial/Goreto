<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavouriteLocation extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
