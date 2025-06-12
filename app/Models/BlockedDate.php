<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    protected $fillable = ['room_id', 'date_start', 'date_end', 'reason'];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'reason' => 'string'
    ];

    public function room(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Room::class, 'id', 'room_id');
    }
}
