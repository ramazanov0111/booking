<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlockedDateResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PriceResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\RoomResource;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Price;
use App\Models\Review;
use App\Models\Room;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SpaBaseController extends Controller
{
    public function roomList(Request $request): JsonResponse
    {
        $capacity = $request->get('guests');
        $checkIn = $request->get('checkIn');
        $checkOut = $request->get('checkOut');

        $rooms = Room::query()
            ->where('is_available', true)
            ->when(!is_null($capacity), fn($q) => $q->where('capacity', '>=', $capacity))
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

    public function getBlockedDatesByRoom(int $roomId): JsonResponse
    {
        $blockedDates = BlockedDate::query()
            ->where('room_id', $roomId)
            ->orderBy('date_start', 'desc')
            ->get()
            ->all();

        $dates = array();

        foreach ($blockedDates as $blockedDate) {
            $period = new DatePeriod(
                $blockedDate->date_start,
                new DateInterval('P1D'),
                new Carbon(strtotime($blockedDate->date_end . ' +1 days'))
            );

            foreach ($period as $key => $value) {
                $dates[] = $value->format('Y-m-d');
            }
        }

        return response()->json($dates);
    }

    public function getBookingDatesByRoom(int $roomId): JsonResponse
    {
        $bookingDates = Booking::query()
            ->where('room_id', $roomId)
            ->orderBy('check_in', 'desc')
            ->get()
            ->all();

        $dates = array();

        foreach ($bookingDates as $bookingDate) {
            $period = new DatePeriod(
                $bookingDate->check_in,
                new DateInterval('P1D'),
                new Carbon(strtotime($bookingDate->check_out . ' +1 days'))
            );

            foreach ($period as $key => $value) {
                $dates[] = $value->format('Y-m-d');
            }
        }

        return response()->json($dates);
    }

    public function getBookingsForUser(Request $request): JsonResponse
    {
        $userId = $request->get('userId');
        $bookings = Booking::with('room')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();

        return response()->json(BookingResource::collection($bookings));
    }

    public function cancelBooking(Booking $booking): Response
    {
        $booking->status = 'canceled';
        $booking->save();
        return response()->noContent();
    }

    public function getReviews(Request $request): JsonResponse
    {
        $roomId = $request->get('roomId');

        $reviews = Review::query()
            ->where('published', 1)
            ->where('deleted', 0)
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json(ReviewResource::collection($reviews));
    }
}
