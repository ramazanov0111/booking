<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePriceRequest;
use App\Http\Requests\UpdatePriceRequest;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AdminPriceController extends Controller
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

        $prices = Price::with('room')
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(25);

        return response()->json($prices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePriceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $price = Price::create($validated);

            return response()->json([
                'message' => 'Новая цена успешно добавлена!',
                'data' => new PriceResource($price)
            ], 201);

        } catch (\Exception $e) {
            // Добавьте логирование ошибки
            Log::error('Price creation error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Price $price): JsonResponse
    {
        return response()->json([
            'data' => new PriceResource($price)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePriceRequest $request, Price $price): JsonResponse
    {
        $price->update($request->validated());
        return response()->json($price);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price): Response
    {
        $price->delete();
        return response()->noContent();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function actualPrice(Request $request): JsonResponse
    {
        $roomId = $request->get('room_id', null);
        $startDate = $request->get('start_date', null);
        $endDate = $request->get('end_date', null);

        $price = Price::with('room')
            ->when(!is_null($roomId), fn($q) => $q->where('room_id', $roomId))
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->whereBetween('end_date', [$startDate, $endDate]);
            })
            ->first();

        return response()->json([
            'data' => new PriceResource($price)
        ]);
    }
}
