<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    public function index(Request $request)
{
    $q = Restaurant::query()->with(['branches','staff']); 

    if ($request->filled('is_active')) {
        $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
    }

    if ($request->filled('category')) {
        $q->where('category', $request->category);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $q->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%$search%")
               ->orWhere('phone', 'like', "%$search%");
        });
    }

    $restaurants = $q->latest()->paginate($request->integer('per_page', 15));

    return RestaurantResource::collection($restaurants);
}
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'category'    => ['nullable', 'string', 'max:100'],
            'price_range' => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $restaurant = Restaurant::create($data);

        return (new RestaurantResource($restaurant))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Restaurant $restaurant)
{
    $restaurant->load(['branches', 'staff']); 
    return new RestaurantResource($restaurant);
}

    public function update(Request $request, Restaurant $restaurant)
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'category'    => ['nullable', 'string', 'max:100'],
            'price_range' => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $restaurant->update($data);

        return new RestaurantResource($restaurant);
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function branches(Restaurant $restaurant)
    {
        $restaurant->load('branches');
        return new RestaurantResource($restaurant);
    }

public function mobileCategories(Request $request)
{
    $data = $request->validate([
        'is_active' => ['nullable'], // 0/1
        'search'    => ['nullable', 'string', 'max:100'],
    ]);

    $q = \App\Models\Restaurant::query();

    if ($request->filled('is_active')) {
        $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $q->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%$search%")
               ->orWhere('phone', 'like', "%$search%");
        });
    }

    // normalize category: trim + lowercase, null/empty => uncategorized
    $rows = $q->selectRaw("
            COALESCE(NULLIF(LOWER(TRIM(category)), ''), 'uncategorized') as `key`,
            COUNT(*) as `count`
        ")
        ->groupBy('key')
        ->orderBy('key')
        ->get();

    $total = (int) $rows->sum('count');

    // ✅ icon base url (غير المسار حسب مكان تخزينك)
    // لو الصور عندك في: public/storage/categories/*
    $iconBase = rtrim(config('app.url'), '/') . '/storage/categories/';

    // ✅ name + icon mapping (اسم الملف فقط)
    $map = [
        'all' => [
            'name' => 'All',
            'icon' => 'ic_all.png',
        ],
        'uncategorized' => [
            'name' => 'Uncategorized',
            'icon' => 'ic_default.png',
        ],
        'restaurant' => [
            'name' => 'Restaurant',
            'icon' => 'ic_restaurant.png',
        ],
        'cafe' => [
            'name' => 'Cafe',
            'icon' => 'ic_cafe.png',
        ],
        'italian' => [
            'name' => 'Italian',
            'icon' => 'ic_italian.png',
        ],
        'sushi' => [
            'name' => 'Sushi',
            'icon' => 'ic_sushi.png',
        ],
        // ضيف أي كاتيجوريز تانية هنا...
    ];

    $defaultIconFile = $map['uncategorized']['icon'] ?? 'ic_default.png';

    // ✅ build response with icon url
    $categories = collect([
        [
            'key'   => 'all',
            'name'  => $map['all']['name'],
            'icon'  => $iconBase . $map['all']['icon'],
            'count' => $total,
        ],
    ])->merge(
        $rows->map(function ($r) use ($map, $iconBase, $defaultIconFile) {
            $key = (string) $r->key;

            $name = $map[$key]['name'] ?? ucfirst($key);
            $iconFile = $map[$key]['icon'] ?? $defaultIconFile;

            return [
                'key'   => $key,
                'name'  => $name,
                'icon'  => $iconBase . $iconFile,   // ✅ URL كامل
                'count' => (int) $r->count,
            ];
        })
    )
    // لو مش عايز تكرار uncategorized مرتين لو موجود ضمن rows، سيبه زي ما هو (مش هيكرر all بس)
    ->values();

    return response()->json([
        'success' => true,
        'data'    => $categories,
    ]);
}



public function mobileGroupedByCategory(Request $request)
{
    $data = $request->validate([
        'search'        => ['nullable', 'string', 'max:100'],
        'is_active'     => ['nullable'],
        'per_category'  => ['nullable', 'integer', 'min:1', 'max:50'],
        'lat'           => ['nullable', 'numeric'],
        'lng'           => ['nullable', 'numeric'],
    ]);

    $perCategory = (int) ($data['per_category'] ?? 10);

    // ✅ user location as double
    $userLat = $request->filled('lat') ? (double) $request->lat : null;
    $userLng = $request->filled('lng') ? (double) $request->lng : null;

    $q = Restaurant::query()
        ->select([
            'id',
            'name',
            'description',
            'phone',
            'category',
            'price_range',
            'is_active',
            'created_at',
            'updated_at',
        ])
        ->with(['branches:id,restaurant_id,address,lat,lng,is_active']);

    if ($request->filled('is_active')) {
        $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $q->where(function ($qq) use ($search) {
            $qq->where('name', 'like', "%$search%")
               ->orWhere('phone', 'like', "%$search%");
        });
    }

    $restaurants = $q->latest()->get();

   // helper لحساب المسافة (لازم type يكون float مش double في PHP)
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


    $grouped = $restaurants
        ->groupBy(function ($r) {
            $key = strtolower(trim((string) $r->category));
            return $key !== '' ? $key : 'uncategorized';
        })
        ->map(function ($items, $key) use ($perCategory, $userLat, $userLng, $haversineKm) {

            $cards = $items->take($perCategory)->map(function ($r) use ($userLat, $userLng, $haversineKm) {

                $branch = $r->branches->first();

                // ✅ branch lat/lng as double
                $branchLat = ($branch && $branch->lat !== null) ? (double) $branch->lat : null;
                $branchLng = ($branch && $branch->lng !== null) ? (double) $branch->lng : null;

                // ✅ distance as double
                $distanceKm = null;
                if ($userLat !== null && $userLng !== null && $branchLat !== null && $branchLng !== null) {
                    $distanceKm = (double) round($haversineKm($userLat, $userLng, $branchLat, $branchLng), 2);
                }

                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'description' => $r->description,
                    'phone' => $r->phone,
                    'category' => $r->category,
                    'price_range' => $r->price_range,
                    'is_active' => (bool) $r->is_active,
                    'created_at' => $r->created_at,
                    'updated_at' => $r->updated_at,

                    'cover_image' => null,
                    'rating' => 0.0,
                    'reviews_count' => 0,

                    'location' => [
                        'address' => $branch?->address,
                        'lat' => $branchLat, // double|null
                        'lng' => $branchLng, // double|null
                    ],

                    'distance_km' => $distanceKm, // double|null

                    'availability_status' => 'unknown',
                    'is_fav' => false,
                ];
            })->values();

            return [
                'category' => [
                    'key' => $key,
                    'name' => $key === 'uncategorized' ? 'Uncategorized' : ucfirst($key),
                ],
                'restaurants' => $cards,
            ];
        })
        ->values();

    return response()->json([
        'success' => true,
        'data' => $grouped,
    ]);
}


public function mobileNewOnTawletak(Request $request)
{
    $data = $request->validate([
        'search'   => ['nullable', 'string', 'max:100'],
        'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
    ]);

    $perPage = (int) ($data['per_page'] ?? 12);

    $q = Restaurant::query()
        ->select([
            'id',
            'name',
            'category',
            'is_active',
            'created_at',
            // لو عندك logo/cover_image ضيفه هنا
            // 'logo',
            // 'cover_image',
        ])
        ->where('is_active', true)
        ->latest(); // latest created_at

    if ($request->filled('search')) {
        $search = $request->search;
        $q->where('name', 'like', "%$search%");
    }

      $items = $q->limit(8)->get();

     return response()->json([
        'success' => true,
        'data' => [
            'items' => $items,
        ],
    ]);
}
}
