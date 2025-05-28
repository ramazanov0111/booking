<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Review extends Model
{
    protected $fillable = ['user_id', 'room_id', 'rating', 'comment', 'published', 'deleted'];

    protected $casts = [
        'rating' => 'integer',
        'comment' => 'string',
        'published' => 'boolean',
        'deleted' => 'boolean'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function room(): HasOne
    {
        return $this->hasOne(Room::class, 'id', 'room_id');
    }
}
