<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = ['user_id', 'room_id', 'check_in', 'check_out', 'total_price', 'status', 'payment_method', 'stripe_payment_id'];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'total_price' => 'float'
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
