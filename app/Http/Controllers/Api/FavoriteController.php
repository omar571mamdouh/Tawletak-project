<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FavoriteController extends Controller
{
    private function cacheKey(int $userId): string
    {
        return "favorites:user:{$userId}";
    }

    // GET /favorites
    public function index(Request $request)
    {
        $user = $request->user();
        $ids = Cache::get($this->cacheKey($user->id), []);

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_ids' => array_values(array_unique(array_map('intval', $ids))),
            ],
        ]);
    }

    // POST /favorites  body: { "restaurant_id": 1 }
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'restaurant_id' => ['required','integer','min:1'],
        ]);

        $key = $this->cacheKey($user->id);
        $ids = Cache::get($key, []);

        $ids[] = (int) $data['restaurant_id'];
        $ids = array_values(array_unique($ids));

        // خليه مثلاً شهر (عدّلها)
        Cache::put($key, $ids, now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Added to favorites',
            'data' => [
                'restaurant_ids' => $ids,
            ],
        ], 201);
    }

    // DELETE /favorites/{restaurantId}
    public function destroy(Request $request, $restaurantId)
    {
        $user = $request->user();

        $key = $this->cacheKey($user->id);
        $ids = Cache::get($key, []);

        $restaurantId = (int) $restaurantId;
        $ids = array_values(array_filter($ids, fn ($id) => (int)$id !== $restaurantId));

        Cache::put($key, $ids, now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites',
            'data' => [
                'restaurant_ids' => $ids,
            ],
        ]);
    }
}
