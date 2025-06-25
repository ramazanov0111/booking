<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConfigRequest;
use App\Http\Requests\UpdateConfigRequest;
use App\Http\Resources\ConfigResource;
use App\Models\Config;
use Illuminate\Http\JsonResponse;

class AdminConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $configs = Config::query()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => ConfigResource::collection($configs->items()),
            'meta' => [
                'total' => $configs->total(),
                'per_page' => $configs->perPage(),
                'from' => $configs->firstItem(),
                'to' => $configs->lastItem(),
                'current_page' => $configs->currentPage(),
                'last_page' => $configs->lastPage(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConfigRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['value'] = json_encode($validated['value']);

        $config = Config::create($validated);

        return response()->json([
            'message' => 'Конфигурация успешно создана',
            'data' => new ConfigResource($config)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Config $config): JsonResponse
    {
        return response()->json([
            'data' => new ConfigResource($config)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConfigRequest $request, Config $config): JsonResponse
    {
        $validated = $request->validated();
        $validated['value'] = json_encode($validated['value']);

        $config->update($validated);

        return response()->json([
            'message' => 'Данные конфигурации обновлены',
            'data' => new ConfigResource($config)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Config $config): JsonResponse
    {
        $config->delete();
        return response()->json(null, 204);
    }
}
