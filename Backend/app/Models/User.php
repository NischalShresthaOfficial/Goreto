<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'role_id',
        'country_id',
    ];

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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
