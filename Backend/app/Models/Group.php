<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'created_by',
        'created_at',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

   public function userGroups()
    {
        return $this->hasMany(UserGroup::class);
    }

    public function groupLocations()
    {
        return $this->hasMany(GroupLocation::class);
    }
}
