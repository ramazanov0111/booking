<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        $rating = 0;
        $reviews = $this->reviews;
        $reviewsCnt = count($reviews);

        if ($reviewsCnt) {
            $sum = 0;
            foreach ($reviews as $review) {
                $sum += $review->rating;
            }
            $rating = $sum / $reviewsCnt;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'capacity' => $this->capacity,
            'base_price' => $this->base_price,
            'is_available' => $this->is_available,
            'room_image' => $this->room_image,
            'imageUrl' => $this->imageUrl,
            'amenities' => json_decode($this->amenities),
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at->format('d-m-Y H:i'),
            'prices' => $this->prices,
            'rating' => $rating,
            'reviewsCnt' => $reviewsCnt
        ];
    }
}
