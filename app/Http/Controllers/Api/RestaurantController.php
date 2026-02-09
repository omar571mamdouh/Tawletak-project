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

    $q = Restaurant::query();

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

    // Display names (عدّل اللي تحبه)
    $nameMap = [
        'all' => 'All',
        'uncategorized' => 'Uncategorized',
        'restaurant' => 'Restaurant',
        'cafe' => 'Cafe',
        'italian' => 'Italian',
        'steak' => 'Steak',
    ];

    $categories = collect([
        ['key' => 'all', 'name' => $nameMap['all'], 'count' => $total],
    ])->merge(
        $rows->map(function ($r) use ($nameMap) {
            $key = $r->key;
            // default: Capitalize first letter لو مش موجود في map
            $name = $nameMap[$key] ?? ucfirst($key);
            return ['key' => $key, 'name' => $name, 'count' => (int) $r->count];
        })
    )->values();

    return response()->json([
        'success' => true,
        'data' => $categories,
    ]);
}

public function mobileGroupedByCategory(Request $request)
{
    $data = $request->validate([
        'search'        => ['nullable', 'string', 'max:100'],
        'is_active'     => ['nullable'],
        'per_category'  => ['nullable', 'integer', 'min:1', 'max:50'], // عدد المطاعم لكل كاتيجوري
        'with_branches' => ['nullable'], // 0/1
        'with_staff'    => ['nullable'], // 0/1
    ]);

    $perCategory = (int) ($data['per_category'] ?? 10);

    // ✅ خفيف للموبايل
    $q = Restaurant::query()->select([
        'id',
        'name',
        'description',
        'phone',
        'category',
        'price_range',
        'is_active',
        'created_at',
        'updated_at',
        // لو عندك cover_image / rating / reviews_count ضيفهم هنا
    ]);

    // optional relations
    $with = [];
    if ($request->boolean('with_branches')) $with[] = 'branches';
    if ($request->boolean('with_staff')) $with[] = 'staff';
    if (!empty($with)) $q->with($with);

    // filters
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

    // normalize category
    $grouped = $restaurants
        ->groupBy(function ($r) {
            $key = strtolower(trim((string) $r->category));
            return $key !== '' ? $key : 'uncategorized';
        })
        ->map(function ($items, $key) use ($perCategory) {
            return [
                'category' => [
                    'key' => $key,
                    'name' => $key === 'uncategorized' ? 'Uncategorized' : ucfirst($key),
                ],
                'restaurants' => RestaurantResource::collection($items->take($perCategory))->resolve(),
            ];
        })
        ->values();

    return response()->json([
        'success' => true,
        'data' => $grouped,
    ]);
}

}
