<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\TableStatus;
use Illuminate\Http\Request;

class TableStatusController extends Controller
{
    private function staffRestaurantId(): int
    {
        return (int) auth('staff')->user()->restaurant_id;
    }

    private function assertTableInStaffRestaurant(Table $table): void
    {
        $restaurantId = $this->staffRestaurantId();

        $allowed = Table::query()
            ->whereKey($table->id)
            ->whereHas('branch', fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->exists();

        abort_unless($allowed, 403, 'Forbidden (table not in your restaurant).');
    }

    // اختياري: لو عايز تجيب statuses كلها لمطعم الـ staff
    public function index(Request $request)
    {
        $restaurantId = $this->staffRestaurantId();

        $query = TableStatus::query()
            ->with(['table.branch'])
            ->whereHas('table.branch', fn ($q) => $q->where('restaurant_id', $restaurantId));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    // عرض status لطاولة معينة
    public function showByTable(Table $table)
    {
        $this->assertTableInStaffRestaurant($table);

        $table->load(['status']);

        return response()->json([
            'success' => true,
            'data' => $table->status, // ممكن ترجع table + status لو تحب
        ]);
    }

    // تعديل/إنشاء status للطاولة (Upsert)
    public function upsertByTable(Request $request, Table $table)
    {
        $this->assertTableInStaffRestaurant($table);

        $data = $request->validate([
            'status' => ['required', 'in:available,reserved,occupied,out_of_service'],
            // زوّد الحقول حسب migration بتاعك
            'current_reservation_id' => ['nullable', 'integer'],
            'occupied_since' => ['nullable', 'date'],
            'estimated_free_at' => ['nullable', 'date'],
        ]);

        $status = TableStatus::updateOrCreate(
            ['table_id' => $table->id],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Table status updated successfully',
            'data' => $status,
        ]);
    }
}
