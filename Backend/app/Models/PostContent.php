<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostContent extends Model
{
    protected $fillable = [
        'content_path',
        'post_id',
    ];

    protected $appends = ['content_url'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function getContentUrlAttribute()
    {
        return Storage::disk('public')->url($this->content_path);
    }
}
