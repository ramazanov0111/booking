<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockedDateRequest;
use App\Http\Requests\UpdateBlockedDateRequest;
use App\Http\Resources\BlockedDateResource;
use App\Models\BlockedDate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminBlockedDateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $roomId = $request->get('room_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $startDate = $startDate ? Carbon::parse($startDate) : null;
        $endDate = $endDate ? Carbon::parse($endDate) : null;

        $blockedDates = BlockedDate::with('room')
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_start', [$startDate, $endDate])
                    ->orWhereBetween('date_end', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json($blockedDates);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlockedDateRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $blockedDate = BlockedDate::create($validated);

            return response()->json([
                'message' => 'Новая цена успешно добавлена!',
                'data' => new BlockedDateResource($blockedDate)
            ], 201);

        } catch (\Exception $e) {
            // Добавьте логирование ошибки
            Log::error('BlockedDate creation error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BlockedDate $blockedDate): JsonResponse
    {
        return response()->json([
            'data' => new BlockedDateResource($blockedDate)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlockedDateRequest $request, BlockedDate $blockedDate): JsonResponse
    {
        $blockedDate->update($request->validated());
        return response()->json($blockedDate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlockedDate $blockedDate): \Illuminate\Http\Response
    {
        $blockedDate->delete();
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function getAllBlockedDates(Request $request): JsonResponse
    {
        $roomId = $request->get('room_id', null);
        $startDate = $request->get('start_date', null);
        $endDate = $request->get('end_date', null);

        $price = BlockedDate::with('room')
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date_start', [$startDate, $endDate])
                    ->whereBetween('date_end', [$startDate, $endDate]);
            })
            ->first();

        return response()->json([
            'data' => new BlockedDateResource($price)
        ]);
    }
}
