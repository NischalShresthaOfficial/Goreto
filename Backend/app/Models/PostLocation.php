<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLocation extends Model
{
    protected $fillable = [
        'post_id',
        'location_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
