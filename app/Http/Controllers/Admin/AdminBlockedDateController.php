<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockedDateRequest;
use App\Http\Requests\UpdateBlockedDateRequest;
use App\Models\BlockedDate;
use Illuminate\Http\JsonResponse;

class AdminBlockedDateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $blockedDates = BlockedDate::query()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $blockedDates->items(),
            'meta' => [
                'current_page' => $blockedDates->currentPage(),
                'total' => $blockedDates->total(),
                'per_page' => $blockedDates->perPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlockedDateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BlockedDate $blockedDate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlockedDateRequest $request, BlockedDate $blockedDate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlockedDate $blockedDate)
    {
        //
    }
}
