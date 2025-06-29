<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        $firstDate =  $this['firstDate'] ?? null;
        $room = $this['room'] ?? $this;
        $rating = 0;
        $reviews = $room->reviews;
        $reviewsCnt = count($reviews);
        $gallery = ImageResource::collection($room->gallery);

        if ($reviewsCnt) {
            $sum = 0;
            foreach ($reviews as $review) {
                $sum += $review->rating;
            }
            $rating = $sum / $reviewsCnt;
        }

        return [
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'capacity' => $room->capacity,
            'base_price' => $room->base_price,
            'is_available' => $room->is_available,
            'room_image' => $room->room_image,
            'imageUrl' => $room->imageUrl,
            'amenities' => json_decode($room->amenities),
            'is_available_extra_bed' => $room->is_available_extra_bed,
            'created_at' => $room->created_at->format('d-m-Y H:i'),
            'updated_at' => $room->updated_at->format('d-m-Y H:i'),
            'prices' => $room->prices,
            'rating' => $rating,
            'reviewsCnt' => $reviewsCnt,
            'gallery' => $gallery,
            'price' => $this['price'] ?? 0,
            'firstDate' => $firstDate,
        ];
    }
}
