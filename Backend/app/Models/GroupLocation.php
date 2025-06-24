<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupLocation extends Model
{
    protected $fillable = [
        'group_id',
        'location_id',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
