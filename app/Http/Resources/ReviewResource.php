<?php

namespace App\Http\Resources;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        $room = Room::query()->where('id', $this->room_id)->first();
        $roomData = new RoomResource($room);
        $user = User::query()->where('id', $this->user_id)->first();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'room_id' => $this->room_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'room' => $roomData,
            'user' => $user
        ];
    }
}
