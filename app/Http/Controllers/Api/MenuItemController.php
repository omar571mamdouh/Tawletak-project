<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\MenuSection;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function store(Request $request, Restaurant $restaurant, MenuSection $section)
    {
        // تأمين: لازم السيكشن يتبع نفس المطعم
        if ((int) $section->restaurant_id !== (int) $restaurant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Section does not belong to this restaurant.',
            ], 404);
        }

        // Validation (عدّل لو عندك أعمدة زيادة)
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // إنشاء item جوّا section
        $item = $section->items()->create([
            // مهم: خلي restaurant_id ثابت للمطعم من الراوت
            'restaurant_id' => $restaurant->id,

            // FK بتاع section في جدول items
            // لو عندك column اسمها menu_section_id (ده الأشهر) سيبها
            // ولو اسمها section_id غيّرها
            'menu_section_id' => $section->id,

            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'image' => $data['image'] ?? null,
            'is_available' => $data['is_available'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $restaurant->id,
                'section_id' => $section->id,
                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => (float) $item->price,
                    'image' => $item->image,
                    'is_available' => (bool) $item->is_available,
                    'is_featured' => (bool) $item->is_featured,
                    'sort_order' => (int) $item->sort_order,
                ],
            ],
        ], 201);
    }


    /**
 * PUT /restaurants/{restaurant}/menu/sections/{section}/items/{item}
 */
public function update(
    Request $request,
    Restaurant $restaurant,
    MenuSection $section,
    MenuItem $item
) {
    // 1) تأمين: section تابع للمطعم
    if ((int) $section->restaurant_id !== (int) $restaurant->id) {
        return response()->json([
            'success' => false,
            'message' => 'Section does not belong to this restaurant.',
        ], 404);
    }

    // 2) تأمين: item تابع للsection
    if ((int) $item->menu_section_id !== (int) $section->id) {
        return response()->json([
            'success' => false,
            'message' => 'Item does not belong to this section.',
        ], 404);
    }

    // 3) Validation (كلها nullable عشان edit)
    $data = $request->validate([
        'name' => ['sometimes', 'string', 'max:255'],
        'description' => ['sometimes', 'nullable', 'string'],
        'price' => ['sometimes', 'numeric', 'min:0'],
        'image' => ['sometimes', 'nullable', 'string'],
        'is_available' => ['sometimes', 'boolean'],
        'is_featured' => ['sometimes', 'boolean'],
        'sort_order' => ['sometimes', 'integer', 'min:0'],
    ]);

    // 4) Update
    $item->update($data);

    return response()->json([
        'success' => true,
        'data' => [
            'restaurant_id' => $restaurant->id,
            'section_id' => $section->id,
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => (float) $item->price,
                'image' => $item->image,
                'is_available' => (bool) $item->is_available,
                'is_featured' => (bool) $item->is_featured,
                'sort_order' => (int) $item->sort_order,
            ],
        ],
    ]);
}


    /**
     * DELETE /restaurants/{restaurant}/menu/sections/{section}/items/{item}
     */
    public function destroy(Restaurant $restaurant, MenuSection $section, MenuItem $item)
    {
        // تأمين: section تابع للمطعم
        if ((int) $section->restaurant_id !== (int) $restaurant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Section does not belong to this restaurant.',
            ], 404);
        }

        // تأمين: item تابع للsection
        // غيّر 'menu_section_id' لو اسم العمود عندك مختلف
        if ((int) $item->menu_section_id !== (int) $section->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item does not belong to this section.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully.',
            'data' => [
                'restaurant_id' => $restaurant->id,
                'section_id' => $section->id,
                'deleted_item_id' => $item->id,
            ],
        ]);
    }
}
