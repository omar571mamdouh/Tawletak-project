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

    /**
     * Normalize cached favorites structure:
     * - If string JSON => decode
     * - If not array => []
     * - Ensure each item is an array (object => array), drop scalars
     */
    private function normalizeItems($items): array
    {
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $items = $decoded;
            }
        }

        if (!is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }

            if (is_array($item)) {
                $normalized[] = $item;
            }
        }

        return $normalized;
    }

    private function currentCustomer(Request $request)
    {
        return $request->user('customer');
    }

    /**
     * GET /customer/favorites
     * Get all favorites from cache
     */
   public function index(Request $request)
{
    $customer = $this->currentCustomer($request);
    if (!$customer) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated',
        ], 401);
    }

    $key = $this->cacheKey($customer->id);

    // normalize عشان لو الكاش فيه scalars / json string / objects
    $items = $this->normalizeItems(Cache::get($key, []));

    // Default Fields (شكل ثابت لكل العناصر)
    $items = array_map(function ($item) {
        return [
            'restaurant_id'     => (int)($item['restaurant_id'] ?? 0),
            'restaurant_name'   => (string)($item['restaurant_name'] ?? 'Unknown'),
            'banner_url'        => $item['banner_url'] ?? null,

            'rating'            => isset($item['rating']) ? (float)$item['rating'] : 0.0,
            'reviews_count'     => isset($item['reviews_count']) ? (int)$item['reviews_count'] : 0,

            'category_name'     => $item['category_name'] ?? null,
            'location_text'     => $item['location_text'] ?? null,
            'distance_km'       => isset($item['distance_km']) ? (float)$item['distance_km'] : null,

            'tables_available'  => isset($item['tables_available']) ? (bool)$item['tables_available'] : true,
            'is_favorite'       => true,
        ];
    }, $items);

    // شيل أي item مش منطقي (restaurant_id = 0)
    $items = array_values(array_filter($items, fn ($i) => (int)$i['restaurant_id'] > 0));

    return response()->json([
        'success' => true,
        'data' => [
            'items' => $items,
            'total_favorites' => count($items),
        ],
    ]);
}


    /**
     * POST /customer/favorites
     * Add favorite (store full card)
     */
    public function store(Request $request)
    {
        $customer = $this->currentCustomer($request);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $data = $request->validate([
            'restaurant_id'     => ['required', 'integer', 'min:1'],
            'restaurant_name'   => ['required', 'string', 'max:255'],
            'banner_url'        => ['nullable', 'string'],
            'rating'            => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count'     => ['nullable', 'integer', 'min:0'],
            'category_name'     => ['nullable', 'string'],
            'location_text'     => ['nullable', 'string'],
            'distance_km'       => ['nullable', 'numeric', 'min:0'],
            'tables_available'  => ['nullable', 'boolean'],
        ]);

        $key = $this->cacheKey($customer->id);
        $items = $this->normalizeItems(Cache::get($key, []));

        // Remove if exists (avoid duplicates)
        $items = array_values(array_filter($items, function ($item) use ($data) {
            return (int)($item['restaurant_id'] ?? 0) !== (int)$data['restaurant_id'];
        }));

        $newCard = [
            'restaurant_id'     => (int) $data['restaurant_id'],
            'restaurant_name'   => $data['restaurant_name'],
            'banner_url'        => $data['banner_url'] ?? null,
            'rating'            => isset($data['rating']) ? (float) $data['rating'] : 0.0,
            'reviews_count'     => (int) ($data['reviews_count'] ?? 0),
            'category_name'     => $data['category_name'] ?? null,
            'location_text'     => $data['location_text'] ?? null,
            'distance_km'       => isset($data['distance_km']) ? (float) $data['distance_km'] : null,
            'tables_available'  => (bool) ($data['tables_available'] ?? true),
            'is_favorite'       => true,
        ];

        $items[] = $newCard;

        Cache::put($key, $items, now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Added to favorites',
            'data' => [
                'item' => $newCard,
                'total_favorites' => count($items),
            ],
        ], 201);
    }

    /**
     * DELETE /customer/favorites/{restaurantId}
     * Remove from favorites
     */
    public function destroy(Request $request, $restaurantId)
    {
        $customer = $this->currentCustomer($request);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $restaurantId = (int) $restaurantId;

        $key = $this->cacheKey($customer->id);
        $items = $this->normalizeItems(Cache::get($key, []));

        $items = array_values(array_filter($items, function ($item) use ($restaurantId) {
            return (int)($item['restaurant_id'] ?? 0) !== $restaurantId;
        }));

        Cache::put($key, $items, now()->addDays(30));

        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites',
            'data' => [
                'total_favorites' => count($items),
            ],
        ]);
    }

    /**
     * POST /customer/favorites/check
     * Check if restaurant is favorite
     */
    public function check(Request $request)
    {
        $customer = $this->currentCustomer($request);
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validated = $request->validate([
            'restaurant_id' => ['required', 'integer', 'min:1'],
        ]);

        $restaurantId = (int) $validated['restaurant_id'];

        $key = $this->cacheKey($customer->id);
        $items = $this->normalizeItems(Cache::get($key, []));

        $isFavorite = !empty(array_filter($items, function ($item) use ($restaurantId) {
            return (int)($item['restaurant_id'] ?? 0) === $restaurantId;
        }));

        return response()->json([
            'success' => true,
            'data' => [
                'is_favorite' => $isFavorite,
            ],
        ]);
    }
}
