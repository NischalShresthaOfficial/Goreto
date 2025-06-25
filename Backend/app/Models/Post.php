<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Post extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'likes',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'likes', 'user_id'])
            ->useLogName('post')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
