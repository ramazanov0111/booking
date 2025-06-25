<?php

namespace App\Models;

use App\Traits\HasPhoto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Config extends Model
{
    protected $fillable = ['key', 'is_array', 'value'];

    protected $casts = [
        'value' => 'array',
        'is_array' => 'boolean',
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

    public function getImageUrlAttribute(): string
    {
        return $this->room_image ? url('storage/' . $this->room_image) : '';
    }
}
