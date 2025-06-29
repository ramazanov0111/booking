<?php

namespace App\Http\Resources;

use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        $room = Room::query()->where('id', $this->room_id)->first();
        $user = User::query()->where('id', $this->user_id)->first();

        $roomData = new RoomResource($room);
        $userData = new UserResource($user);
        $nights = $this->check_in->diffInDays($this->check_out);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'room_id' => $this->room_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'total_price' => $this->total_price,
            'nights' => $nights,
            'status' => $this->status,
            'extra_bed' => $this->extra_bed,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i'),
            'room' => $roomData,
            'user' => $userData
        ];
    }
}
