<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostContent extends Model
{
    protected $fillable = [
        'content_path',
        'post_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
