<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Models\RoomImage;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $name = $request->get('name');
        $minBasePrice = $request->get('base_price_min');
        $maxBasePrice = $request->get('base_price_max');
        $capacity = $request->get('capacity');
        $status = $request->get('status');

        $rooms = Room::query()
            ->when(!is_null($name), fn($q) => $q->where('name', 'like', '%' . $name . '%'))
            ->when(!is_null($capacity), fn($q) => $q->where('capacity', (int)$capacity))
            ->when(!is_null($status), fn($q) => $q->where('is_available', (bool)$status))
            ->when(!is_null($minBasePrice), fn($q) => $q->where('base_price', '>=', $minBasePrice))
            ->when(!is_null($maxBasePrice), fn($q) => $q->where('base_price', '<=', $maxBasePrice))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $rooms->items(),
            'meta' => [
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
                'from' => $rooms->firstItem(),
                'to' => $rooms->lastItem(),
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['amenities'] = json_encode($validated['amenities'] ?? []);

        $room = Room::create($validated);

        return response()->json([
            'message' => 'Номер успешно создан',
            'data' => new RoomResource($room)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room): JsonResponse
    {
        return response()->json([
            'data' => new RoomResource($room)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room): JsonResponse
    {
        $validated = $request->validated();
        $validated['amenities'] = json_encode($validated['amenities'] ?? []);

        $room->update($validated);

        return response()->json([
            'message' => 'Данные номера обновлены',
            'data' => new RoomResource($room)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room): JsonResponse
    {
        $room->delete();
        return response()->json(null, 204);
    }

    public function deletePhoto(Room $room): JsonResponse
    {
        $room->room_image = '';
        $room->save();
        return response()->json(null, 204);
    }

    /**
     * Update the specified resource in storage.
     */
    public function uploadFile(Request $request, Room $room): JsonResponse
    {
        $file = $request->file('room_image'); // Получение файла

        $data['room_image'] = Storage::disk('public')->put('/images', $file);

        $room->update($data);

        return response()->json([
            'message' => 'Файл добавлен'
        ], 204);
    }

    /**
     * Update the specified resource in storage.
     */
    public function uploadGalleryFiles(Request $request, Room $room): JsonResponse
    {
        $gallery = $request->file('gallery');

        foreach ($gallery as $item) {
            $imgPath = Storage::disk('public')->put('/images', $item);
            RoomImage::query()->create([
                'room_id' => $room->id,
                'filename' => $imgPath,
            ]);
        }

        return response()->json([
            'message' => 'Файл добавлен'
        ], 204);
    }

    public function deleteGalleryPhoto(RoomImage $image): JsonResponse
    {
        $image->delete();
        return response()->json(null, 204);
    }

    public function enabledRooms(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $startDate = $startDate ? Carbon::parse($startDate) : Carbon::parse(today());
        $endDate = $endDate ? Carbon::parse($endDate) : Carbon::parse(today()->addDay());
//        dd($startDate, $endDate);

        $rooms = Room::query()
            ->where('is_available', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data = [];
        foreach ($rooms->all() as $room) {
            $isEnabled = true;
            $newPrice = 0;

            $dates = array();
            foreach ($room->blocked_dates->all() as $blockedDate) {
                $period = new DatePeriod(
                    $blockedDate->date_start,
                    new DateInterval('P1D'),
                    new Carbon(strtotime($blockedDate->date_end . ' +1 days'))
                );

                foreach ($period as $key => $value) {
                    $dates[] = Carbon::parse($value)->format('d-m-Y');
                }
            }

            foreach ($room->bookings->all() as $bookingDate) {
                if ($bookingDate->status !== 'canceled') {
                    $period = new DatePeriod(
                        $bookingDate->check_in,
                        new DateInterval('P1D'),
                        new Carbon(strtotime($bookingDate->check_out . ' +1 days'))
                    );

                    foreach ($period as $key => $value) {
                        $dates[] = Carbon::parse($value)->format('d-m-Y');
                    }
                }
            }

            $userPeriod = new DatePeriod(
                $startDate,
                new DateInterval('P1D'),
                new Carbon(strtotime($endDate . ' +1 days'))
            );

            $userDates = array();
            foreach ($userPeriod as $key => $value) {
                $userDates[] = Carbon::parse($value)->format('d-m-Y');
            }

            $enableDate = $startDate;
            foreach ($userDates as $userDate) {
                if (!in_array($userDate, $dates)) {
                    $enableDate = Carbon::parse($userDate);
                    $isEnabled = true;
                    break;
                } else {
                    $isEnabled = false;
                }
            }

            if ($isEnabled) {
                // получение цены за период из фильтра
                foreach ($room->prices->all() as $price) {
                    if ($startDate->isBetween($price->start_date, $price->end_date)
                        || $endDate->isBetween($price->start_date, $price->end_date)) {
                        $newPrice = $price;
                        break;
                    }
                }

                $data[] = [
                    'room' => $room,
                    'price' => $newPrice,
                    'firstDate' => $enableDate,
                ];
            }
        }

        return response()->json(RoomResource::collection($data));
    }
}
