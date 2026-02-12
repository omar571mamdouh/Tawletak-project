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

    // user location if provided
    $userLat = $request->filled('lat') ? (double) $request->lat : null;
    $userLng = $request->filled('lng') ? (double) $request->lng : null;

    // helper لحساب المسافة بالكيلومتر
    $haversineKm = function (float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth * $c;
    };

    $restaurants = array_map(function ($item) use ($items, $userLat, $userLng, $haversineKm) {

        // جلب الـ restaurant من الـ DB مع أول فرع
        $restaurant = \App\Models\Restaurant::with(['branches'])->find($item['restaurant_id']);
        $branch = $restaurant?->branches->first();

        $branchLat = $branch?->lat ? (double)$branch->lat : null;
        $branchLng = $branch?->lng ? (double)$branch->lng : null;

        $distanceKm = null;
        if ($userLat !== null && $userLng !== null && $branchLat !== null && $branchLng !== null) {
            $distanceKm = round($haversineKm($userLat, $userLng, $branchLat, $branchLng), 2);
        }

        // ✅ تحقق إذا المطعم موجود في الكاش
        $isFav = !empty(array_filter($items, function ($favItem) use ($restaurant) {
            return (int)($favItem['restaurant_id'] ?? 0) === ($restaurant->id ?? 0);
        }));

        return [
            'id' => $restaurant->id ?? 0,
            'name' => $restaurant->name ?? 'Unknown',
            'description' => $restaurant->description ?? null,
            'phone' => $restaurant->phone ?? null,
            'category' => $restaurant->category ?? null,
            'price_range' => $restaurant->price_range ?? null,
            'is_active' => (bool)($restaurant->is_active ?? true),
            'created_at' => $restaurant->created_at ?? now(),
            'updated_at' => $restaurant->updated_at ?? now(),

            'cover_image' => $branch?->cover_image ? asset('storage/private/' . $branch->cover_image) : null,
            'rating' => isset($item['rating']) ? (float)$item['rating'] : 0,
            'reviews_count' => isset($item['reviews_count']) ? (int)$item['reviews_count'] : 0,

            'location' => [
                'address' => $branch?->address ?? null,
                'lat' => $branchLat,
                'lng' => $branchLng,
            ],

            'distance_km' => $distanceKm,
            'availability_status' => 'unknown',
            'is_fav' => $isFav, // ✅ هنا
        ];
    }, $items);

    return response()->json([
        'success' => true,
        'data' => [
            [
                'restaurants' => $restaurants
            ]
        ]
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

    // Validate فقط الـ restaurant_id
    $validated = $request->validate([
        'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
    ]);

    $restaurantId = (int) $validated['restaurant_id'];

    // جلب المطعم من DB مع أول فرع
    $restaurant = \App\Models\Restaurant::with('branches')->find($restaurantId);
    $branch = $restaurant?->branches->first();

    if (!$restaurant) {
        return response()->json([
            'success' => false,
            'message' => 'Restaurant not found',
        ], 404);
    }

    $favKey = $this->cacheKey($customer->id);
    $items = $this->normalizeItems(Cache::get($favKey, []));

    // منع التكرار
    $items = array_values(array_filter($items, fn($item) => (int)($item['restaurant_id'] ?? 0) !== $restaurantId));

    // إضافة المطعم للمفضلة
    $newItem = [
        'restaurant_id' => $restaurant->id,
        'is_favorite' => true,
    ];

    $items[] = $newItem;

    // حفظ الكاش
    Cache::put($favKey, $items, now()->addDays(30));

    // تكوين الريسبونس بالبيانات الحقيقية من DB
    $branchLat = $branch?->lat ? (double)$branch->lat : null;
    $branchLng = $branch?->lng ? (double)$branch->lng : null;

    $responseData = [
        'id' => $restaurant->id,
        'name' => $restaurant->name,
        'description' => $restaurant->description,
        'phone' => $restaurant->phone,
        'category' => $restaurant->category,
        'price_range' => $restaurant->price_range,
        'is_active' => (bool)$restaurant->is_active,
        'created_at' => $restaurant->created_at,
        'updated_at' => $restaurant->updated_at,
        'cover_image' => $branch?->cover_image ? asset('storage/private/' . $branch->cover_image) : null,
        'rating' => 0, // لو عايز تجيب من جدول التقييمات ممكن تضيف هنا
        'reviews_count' => 0,
        'location' => [
            'address' => $branch?->address ?? null,
            'lat' => $branchLat,
            'lng' => $branchLng,
        ],
        'is_fav' => true,
    ];

    return response()->json([
        'success' => true,
        'message' => 'Added to favorites',
        'data' => $responseData,
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
