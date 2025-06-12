<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AdminReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->get('userId');
        $roomId = $request->get('roomId');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');

        $reviews = Review::with(['room', 'user'])
            ->when(!is_null($userId), fn($q) => $q->where('user_id', $userId))
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $booking = Review::create($validated);

            return response()->json([
                'message' => 'Бронирование успешно добавлено!',
                'data' => new ReviewResource($booking)
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
    public function show(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review): Response
    {
        $review->delete();
        return response()->noContent();
    }

    public function publish(Review $review): Response
    {
        $review->published = 1;
        $review->save();
        return response()->noContent();
    }
}
