<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'role_id',
        'country_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Configure Spatie Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role_id', 'country_id'])
            ->useLogName('user')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function userCategories()
    {
        return $this->hasMany(UserCategory::class);
    }

    public function profilePicture()
    {
        return $this->hasMany(ProfilePicture::class);
    }

    public function favouriteLocations()
    {
        return $this->hasMany(FavouriteLocation::class);
    }

    public function locationReviews()
    {
        return $this->hasMany(LocationReview::class);
    }

    public function userGroups()
    {
        return $this->hasMany(UserGroup::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postReviews()
    {
        return $this->hasMany(PostReview::class);
    }

    public function userChats()
    {
        return $this->hasMany(UserChat::class);
    }

    public function emailNotifications()
    {
        return $this->hasMany(EmailNotification::class);
    }

    public function locationNotifications()
    {
        return $this->hasMany(LocationNotification::class);
    }
}
