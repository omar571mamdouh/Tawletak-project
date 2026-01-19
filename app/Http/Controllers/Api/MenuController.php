<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * POST /restaurants/{restaurant}/menu/preview
     * Body contains categories/items, returns normalized grouped menu
     */
    public function preview(Request $request, Restaurant $restaurant)
    {
        $data = $request->validate([
            'currency' => ['nullable','string','max:10'],
            'available_only' => ['nullable','boolean'],

            'categories' => ['required','array','min:1'],
            'categories.*.name' => ['required','string','max:100'],
            'categories.*.items' => ['required','array'],

            'categories.*.items.*.id' => ['nullable','integer'],
            'categories.*.items.*.name' => ['required','string','max:150'],
            'categories.*.items.*.description' => ['nullable','string','max:1000'],
            'categories.*.items.*.price' => ['required','numeric','min:0'],
            'categories.*.items.*.image' => ['nullable','string','max:2048'],
            'categories.*.items.*.is_available' => ['nullable','boolean'],
            'categories.*.items.*.is_featured' => ['nullable','boolean'],
        ]);

        $currency = $data['currency'] ?? 'JOD';
        $availableOnly = $request->boolean('available_only', true);

        // Normalize into grouped structure: { "Burgers": [..], "Chicken": [..] }
        $grouped = [];
        foreach ($data['categories'] as $cat) {
            $catName = $cat['name'] ?? 'Others';
            $items = $cat['items'] ?? [];

            $normalizedItems = [];
            foreach ($items as $i) {
                $isAvailable = array_key_exists('is_available', $i) ? (bool)$i['is_available'] : true;
                if ($availableOnly && !$isAvailable) {
                    continue;
                }

                $normalizedItems[] = [
                    'id' => $i['id'] ?? null,
                    'name' => $i['name'],
                    'description' => $i['description'] ?? null,
                    'price' => (float) $i['price'],
                    'currency' => $currency,
                    'image' => $i['image'] ?? null,
                    'is_available' => $isAvailable,
                    'is_featured' => array_key_exists('is_featured', $i) ? (bool)$i['is_featured'] : false,
                ];
            }

            $grouped[$catName] = $normalizedItems;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant' => [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                ],
                'menu' => $grouped,
            ],
        ]);
    }

    /**
     * POST /restaurants/{restaurant}/menu/highlights/preview
     * Returns only featured items from the same body format
     */
    public function highlightsPreview(Request $request, Restaurant $restaurant)
    {
        $data = $request->validate([
            'currency' => ['nullable','string','max:10'],
            'limit' => ['nullable','integer','min:1','max:50'],

            'categories' => ['required','array','min:1'],
            'categories.*.items' => ['required','array'],

            'categories.*.items.*.id' => ['nullable','integer'],
            'categories.*.items.*.name' => ['required','string','max:150'],
            'categories.*.items.*.description' => ['nullable','string','max:1000'],
            'categories.*.items.*.price' => ['required','numeric','min:0'],
            'categories.*.items.*.image' => ['nullable','string','max:2048'],
            'categories.*.items.*.is_available' => ['nullable','boolean'],
            'categories.*.items.*.is_featured' => ['nullable','boolean'],
        ]);

        $currency = $data['currency'] ?? 'JOD';
        $limit = $data['limit'] ?? 6;

        $highlights = [];

        foreach ($data['categories'] as $cat) {
            foreach ($cat['items'] as $i) {
                $isAvailable = array_key_exists('is_available', $i) ? (bool)$i['is_available'] : true;
                $isFeatured = array_key_exists('is_featured', $i) ? (bool)$i['is_featured'] : false;

                if (!$isAvailable || !$isFeatured) continue;

                $highlights[] = [
                    'id' => $i['id'] ?? null,
                    'name' => $i['name'],
                    'description' => $i['description'] ?? null,
                    'price' => (float) $i['price'],
                    'currency' => $currency,
                    'image' => $i['image'] ?? null,
                ];

                if (count($highlights) >= $limit) break 2;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $restaurant->id,
                'highlights' => $highlights
            ]
        ]);
    }
}
