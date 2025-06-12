<?php

namespace App\Http\Resources;

use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        $room = Room::query()->where('id', $this->room_id)->first();
        $roomData = new RoomResource($room);
        $nights = $this->check_in->diffInDays($this->check_out);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'room_id' => $this->room_id,
            'check_in' => $this->check_in->format('Y-m-d'),
            'check_out' => $this->check_out->format('Y-m-d'),
            'total_price' => $this->total_price,
            'nights' => $nights,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i'),
            'room' => $roomData
        ];
    }
}
