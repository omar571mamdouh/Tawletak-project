<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\RestaurantBranch;
use Illuminate\Http\Request;

class TableController extends Controller
{
    private function staffRestaurantId(): int
    {
        return (int) auth('staff')->user()->restaurant_id;
    }

    private function tableBelongsToStaffRestaurant(Table $table): bool
    {
        $restaurantId = $this->staffRestaurantId();

        return Table::query()
            ->whereKey($table->id)
            ->whereHas('branch', fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->exists();
    }

    private function assertTableInStaffRestaurant(Table $table): void
    {
        abort_unless($this->tableBelongsToStaffRestaurant($table), 403, 'Forbidden (table not in your restaurant).');
    }

    private function assertBranchInStaffRestaurant(int $branchId): void
    {
        $restaurantId = $this->staffRestaurantId();

        $ok = RestaurantBranch::query()
            ->whereKey($branchId)
            ->where('restaurant_id', $restaurantId)
            ->exists();

        abort_unless($ok, 403, 'Forbidden (branch not in your restaurant).');
    }

    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 20), 100);

        $restaurantId = $this->staffRestaurantId();

        $query = Table::query()
            // ✅ أهم سطر: هات Tables الخاصة بمطعم الـ staff فقط
            ->whereHas('branch', fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->with([
                'status',
                'statusHistory' => function ($q) use ($request) {
                    $limit = min((int) $request->get('history_limit', 20), 200);
                    $q->limit($limit);
                },
            ]);

        // Filters (اختياري)
        if ($request->filled('branch_id')) {
            // ✅ تأكد إن branch_id ده تبع نفس المطعم (عشان ما يفلترش على فرع مطعم تاني)
            $this->assertBranchInStaffRestaurant((int) $request->branch_id);
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            $query->whereHas('status', fn ($q) => $q->where('status', $request->status));
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        $tables = $query->get();

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    public function show(Table $table, Request $request)
    {
        // ✅ امنع عرض Table مش تبع المطعم
        $this->assertTableInStaffRestaurant($table);

        $limit = min((int) $request->get('history_limit', 50), 200);

        $table->load([
            'status',
            'statusHistory' => fn ($q) => $q->limit($limit),
        ]);

        return response()->json([
            'success' => true,
            'data' => $table,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id'    => ['required', 'integer', 'exists:restaurant_branches,id'],
            'table_code'   => ['required', 'string', 'max:50'],
            'capacity'     => ['required', 'integer', 'min:1', 'max:50'],
            'location_tag' => ['nullable', 'string', 'max:100'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        // ✅ لازم الفرع يبقى تبع نفس المطعم
        $this->assertBranchInStaffRestaurant((int) $data['branch_id']);

        $data['is_active'] = $data['is_active'] ?? true;

        $table = Table::create($data);

        $table->load(['status', 'statusHistory']);

        return response()->json([
            'success' => true,
            'message' => 'Table created successfully',
            'data' => $table,
        ], 201);
    }

    public function update(Request $request, Table $table)
    {
        // ✅ لازم الطاولة تبع نفس المطعم
        $this->assertTableInStaffRestaurant($table);

        $data = $request->validate([
            'branch_id'    => ['sometimes', 'integer', 'exists:restaurant_branches,id'],
            'table_code'   => ['sometimes', 'string', 'max:50'],
            'capacity'     => ['sometimes', 'integer', 'min:1', 'max:50'],
            'location_tag' => ['sometimes', 'nullable', 'string', 'max:100'],
            'is_active'    => ['sometimes', 'boolean'],
        ]);

        // ✅ لو هيغير branch_id لازم نتأكد الفرع الجديد تبع نفس المطعم
        if (array_key_exists('branch_id', $data) && $data['branch_id'] !== null) {
            $this->assertBranchInStaffRestaurant((int) $data['branch_id']);
        }

        // يمنع تمرير null بالغلط للأعمدة NOT NULL
        $data = array_filter($data, fn ($v) => !is_null($v));

        $table->update($data);

        $table->load(['status', 'statusHistory']);

        return response()->json([
            'success' => true,
            'message' => 'Table updated successfully',
            'data' => $table,
        ]);
    }

    public function destroy(Table $table)
    {
        // ✅ لازم الطاولة تبع نفس المطعم
        $this->assertTableInStaffRestaurant($table);

        // حماية من حذف طاولة عليها حجز شغال
        if (method_exists($table, 'reservations')) {
            $hasActiveReservation = $table->reservations()
                ->whereIn('status', ['pending', 'confirmed', 'seated'])
                ->exists();

            if ($hasActiveReservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete table while it has active reservations.',
                ], 409);
            }
        }

        $table->delete();

        return response()->json([
            'success' => true,
            'message' => 'Table deleted successfully',
        ]);
    }
}
