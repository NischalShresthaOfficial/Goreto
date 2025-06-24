<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'description',
        'likes',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function postContents()
    {
        return $this->hasMany(PostContent::class);
    }

    public function postLocations()
    {
        return $this->hasMany(PostLocation::class);
    }

    public function postReviews()
    {
        return $this->hasMany(PostReview::class);
    }
}
