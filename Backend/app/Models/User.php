<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserCategory;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, HasRoles, HasApiTokens, LogsActivity, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'country_id',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'country_id'])
            ->useLogName('user')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept(['roles'])
            ->submitEmptyLogs();
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->role?->name;
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

    public function userReports()
    {
        return $this->hasMany(UserReport::class);
    }

    public function postReports()
    {
        return $this->hasMany(PostReport::class);
    }

    public function searchHistories()
    {
        return $this->hasMany(SearchHistory::class);
    }
}
