<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlockedDateResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ConfigResource;
use App\Http\Resources\PriceResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\RoomResource;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Config;
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
        $checkIn = $request->get('checkIn') ?? null;
        $checkOut = $request->get('checkOut') ?? null;
        $amenities = $request->get('amenities', []);

        $checkIn = $checkIn ? Carbon::parse($checkIn) : null;
        $checkOut = $checkOut ? Carbon::parse($checkOut) : null;

        $rooms = Room::with(['prices' => function ($query) {
            $query->orderBy('updated_at', 'desc'); // Сортируем цены
        }])
            ->where('is_available', true)
            ->when(!is_null($capacity), fn($q) => $q->where('capacity', '>=', $capacity))
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $data = [];
        foreach ($rooms->all() as $room) {
            // проверка наличия удобств
            $issetAmenities = !count($amenities);
            $roomAmenities = json_decode($room->amenities);
            foreach ($amenities as $amenity) {
                $issetAmenities = in_array($amenity, $roomAmenities);
            }

            $newPrice = 0;
            if ($checkIn && $checkOut) {
                // получение цены за период из фильтра
                foreach ($room->prices->all() as $price) {
                    if ($checkIn->isBetween($price->start_date, $price->end_date)
                        && $checkOut->isBetween($price->start_date, $price->end_date)) {
                        $newPrice = $price;
                        break;
                    }
                }
            }

            if ($issetAmenities) {
                $data[] = [
                    'room' => $room,
                    'price' => $newPrice
                ];
            }
        }

        return response()->json([
            'data' => RoomResource::collection($data),
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

        $prices = Price::query()
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->where('start_date', '<=', $checkOutDate)
            ->where('end_date', '>=', $checkInDate)
            ->orderBy('updated_at', 'desc')
            ->get();

        $priceData = $prices ? PriceResource::collection($prices) : [];

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
                $dates[] = Carbon::parse($value);
            }
        }

        return response()->json($dates);
    }

    public function getBookingDatesByRoom(int $roomId): JsonResponse
    {
        $bookingDates = Booking::query()
            ->where('room_id', $roomId)
            ->whereNot('status', 'canceled')
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
                $dates[] = Carbon::parse($value);
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

    public function getAmenities(Request $request): JsonResponse
    {
        $amenities = Config::query()
            ->where('key', 'Удобства')
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json(new ConfigResource($amenities));
    }

    public function getContacts(Request $request): JsonResponse
    {
        $contacts = Config::query()
            ->whereNot('key', 'Удобства')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(ConfigResource::collection($contacts));
    }

}
