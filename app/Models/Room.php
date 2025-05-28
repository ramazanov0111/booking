<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'description', 'base_price', 'capacity', 'amenities', 'profile_photo_path', 'is_available'];

    protected $casts = [
        'amenities' => 'array',
        'is_available' => 'boolean',
        'base_price' => 'float'
    ];

    public function prices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Price::class);
    }
}
