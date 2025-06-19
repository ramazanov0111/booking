<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $rooms = Room::query()
            ->where('is_available', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $rooms->items(),
            'meta' => [
                'total' => $rooms->total(),
                'per_page' => $rooms->perPage(),
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
        $room->room_image = null;
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
        ],204);
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
        ],204);
    }

    public function deleteGalleryPhoto(RoomImage $image): JsonResponse
    {
        $image->delete();
        return response()->json(null, 204);
    }
}
