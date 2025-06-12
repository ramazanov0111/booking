<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AdminBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->get('user_id');
        $roomId = $request->get('room_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $bookings = Booking::with(['user', 'room'])
            ->when(!is_null($userId), fn($q) => $q->where('user_id', $userId))
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in', [$startDate, $endDate])
                    ->whereBetween('check_out', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $booking = Booking::create($validated);

            return response()->json([
                'message' => 'Бронирование успешно добавлено!',
                'data' => new BookingResource($booking)
            ], 201);

        } catch (\Exception $e) {
            // Добавьте логирование ошибки
            Log::error('Booking creation error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): JsonResponse
    {
        return response()->json([
            'data' => new BookingResource($booking)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        try {
            $booking->update($request->validated());

            return response()->json($booking);
        } catch (\Exception $e) {
            // Добавьте логирование ошибки
            Log::error('Booking updating error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): Response
    {
        $booking->delete();
        return response()->noContent();
    }
}
