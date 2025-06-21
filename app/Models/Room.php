<?php

namespace App\Models;

use App\Traits\HasPhoto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasPhoto;

    protected $fillable = ['name', 'description', 'base_price', 'capacity', 'amenities', 'room_image', 'is_available'];

    protected $casts = [
        'amenities' => 'array',
        'is_available' => 'boolean',
        'base_price' => 'float'
    ];

    // удобства номера
    public const AMENITIES = [
        'Wi-Fi',
        'Телевизор',
        'Холодильник',
        'Кондиционер',
        'Мини-бар',
        'Сейф',
        'Фен',
        'Тапочки'
    ];

    public function prices():HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function blocked_dates(): HasMany
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->room_image ? url('storage/' . $this->room_image) : '';
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }
}
