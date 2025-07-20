<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Group extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'created_by',
        'created_at',
        'profile_picture',
        'group_chat_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'created_by'])
            ->useLogName('group')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function userGroups()
    {
        return $this->hasMany(UserGroup::class);
    }

    public function groupLocations()
    {
        return $this->hasMany(GroupLocation::class);
    }

    public function groupChat()
    {
        return $this->belongsTo(Chat::class, 'group_chat_id');
    }
}
