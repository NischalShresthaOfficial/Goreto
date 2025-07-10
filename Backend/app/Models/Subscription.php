<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'name',
        'price',
        'currency',
        'duration',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
