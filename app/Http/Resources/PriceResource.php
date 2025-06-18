<?php

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
{
    public function toArray($request): array
    {
        $room = Room::query()->where('id', $this->room_id)->first();
        $roomData = new RoomResource($room);

        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'price' => $this->price,
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i'),
            'room' => $roomData
        ];
    }
}
