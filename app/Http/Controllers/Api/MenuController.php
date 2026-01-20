<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuSection;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use App\Models\MenuItem;

class MenuController extends Controller
{
    /**
     * GET /restaurants/{restaurant}/menu/sections
     * Query: include_items=1 (اختياري)
     */
    public function sections(Request $request, Restaurant $restaurant)
    {
        $includeItems = $request->boolean('include_items', false);
        $availableOnly = $request->boolean('available_only', true);
        $currency = $request->get('currency', 'JOD');

        $query = $restaurant->menuSections()
            ->where('is_active', true)
            ->orderBy('sort_order');

        if ($includeItems) {
            $query->with(['items' => function ($q) use ($availableOnly) {
                if ($availableOnly) {
                    $q->where('is_available', true);
                }
                $q->orderBy('sort_order');
            }]);
        }

        $sections = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                ],
                'sections' => $sections->map(function ($section) use ($includeItems, $currency) {
                    $payload = [
                        'id' => $section->id,
                        'name' => $section->name,
                        'sort_order' => (int) $section->sort_order,
                        'is_active' => (bool) $section->is_active,
                    ];

                    if ($includeItems) {
                        $payload['items'] = $section->items->map(function ($item) use ($currency) {
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'description' => $item->description,
                                'price' => (float) $item->price,
                                'currency' => $currency,
                                'image' => $item->image,
                                'is_available' => (bool) $item->is_available,
                                'is_featured' => (bool) $item->is_featured,
                                'sort_order' => (int) $item->sort_order,
                            ];
                        })->values();
                    }

                    return $payload;
                })->values(),
            ],
        ]);
    }

    /**
     * GET /restaurants/{restaurant}/menu/sections/{section}/items
     */
    public function sectionItems(Request $request, Restaurant $restaurant, MenuSection $section)
    {
        // تأمين: لازم السيكشن يتبع نفس المطعم
        if ((int) $section->restaurant_id !== (int) $restaurant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Section does not belong to this restaurant.',
            ], 404);
        }

        $availableOnly = $request->boolean('available_only', true);
        $currency = $request->get('currency', 'JOD');

        $itemsQuery = $section->items()->orderBy('sort_order');
        if ($availableOnly) {
            $itemsQuery->where('is_available', true);
        }

        $items = $itemsQuery->get();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $restaurant->id,
                'section' => [
                    'id' => $section->id,
                    'name' => $section->name,
                ],
                'items' => $items->map(function ($item) use ($currency) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => (float) $item->price,
                        'currency' => $currency,
                        'image' => $item->image,
                        'is_available' => (bool) $item->is_available,
                        'is_featured' => (bool) $item->is_featured,
                        'sort_order' => (int) $item->sort_order,
                    ];
                })->values(),
            ],
        ]);
    }

    /**
     * GET /restaurants/{restaurant}/menu/highlights
     */
    public function highlights(Request $request, Restaurant $restaurant)
    {
        $currency = $request->get('currency', 'JOD');
        $limit = (int) $request->get('limit', 6);

        $items = $restaurant->menuItems()
            ->where('is_available', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $restaurant->id,
                'highlights' => $items->map(function ($item) use ($currency) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'price' => (float) $item->price,
                        'currency' => $currency,
                        'image' => $item->image,
                    ];
                })->values(),
            ],
        ]);
    }
}
