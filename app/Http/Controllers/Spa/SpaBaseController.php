<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Http\Resources\RoomResource;
use App\Models\Price;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpaBaseController extends Controller
{
    public function roomList(Request $request): JsonResponse
    {
//        $capacity = $request->get('capacity');
//        $checkIn = $request->get('checkIn');
//        $checkOut = $request->get('checkOut');

        $rooms = Room::query()
            ->where('is_available', true)
//            ->when(!is_null($capacity), fn($q) => $q->where('capacity', $capacity))
//            ->when($checkIn && $checkOut, function ($q) use ($checkIn, $checkOut) {
//                $q->whereBetween('start_date', [$checkIn, $checkOut])
//                    ->whereBetween('end_date', [$checkIn, $checkOut]);
//            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json([
            'data' => RoomResource::collection($rooms),
            'meta' => [
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
            ]
        ]);
    }

    public function priceByDates(Request $request): JsonResponse
    {
        $roomId = $request->get('roomId');
        $checkInDate = $request->get('checkInDate');
        $checkOutDate = $request->get('checkOutDate');

        $price = Price::query()
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->where('start_date', '<=', $checkInDate)
            ->where('end_date', '>=', $checkOutDate)
            ->orderBy('start_date', 'desc')
            ->first();

        $priceData = $price ? new PriceResource($price) : [];

        return response()->json($priceData);
    }
}
