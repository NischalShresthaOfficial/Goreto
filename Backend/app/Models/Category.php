<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'fsq_category_id',
        'category',
    ];

    public function userCategories()
    {
        return $this->hasMany(UserCategory::class);
    }
}
