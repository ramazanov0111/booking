<?php

namespace App\Models;

use App\Traits\HasPhoto;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasPhoto;

    protected $fillable = ['name', 'description', 'base_price', 'capacity', 'amenities', 'room_image', 'is_available'];

    protected $casts = [
        'amenities' => 'array',
        'is_available' => 'boolean',
        'base_price' => 'float'
    ];

    public function prices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function blocked_dates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function getImageUrlAttribute(): string
    {
        return url('storage/' . $this->room_image);
    }
}
