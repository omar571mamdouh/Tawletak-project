<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}
