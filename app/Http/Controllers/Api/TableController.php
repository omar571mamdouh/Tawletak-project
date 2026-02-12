<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\RestaurantBranch;
use Illuminate\Http\Request;
use App\Models\TableStatus;

class TableController extends Controller
{
    private function staffRestaurantId(): int
    {
        return (int) auth('staff')->user()->restaurant_id;
    }

    /**
     * Ensure given branch belongs to the authenticated staff restaurant.
     */
    private function assertBranchInStaffRestaurant(int $branchId): void
    {
        $restaurantId = $this->staffRestaurantId();

        $ok = RestaurantBranch::query()
            ->whereKey($branchId)
            ->where('restaurant_id', $restaurantId)
            ->exists();

        abort_unless($ok, 403, 'Forbidden (branch not in your restaurant).');
    }

    /**
     * Ensure given table belongs to the authenticated staff restaurant (by restaurant_id).
     */
    private function assertTableInStaffRestaurant(Table $table): void
    {
        $restaurantId = $this->staffRestaurantId();

        abort_unless(
            (int) $table->restaurant_id === $restaurantId,
            403,
            'Forbidden (table not in your restaurant).'
        );
    }

    public function index(Request $request)
    {
        $restaurantId = $this->staffRestaurantId();

        $query = Table::query()
            ->where('restaurant_id', $restaurantId)
            ->with([
                'status',
                'statusHistory' => function ($q) use ($request) {
                    $limit = min((int) $request->get('history_limit', 20), 200);
                    $q->limit($limit);
                },
            ]);

        // Optional Filters
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->branch_id;
            $this->assertBranchInStaffRestaurant($branchId);
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('status')) {
            $status = (string) $request->status;
            $query->whereHas('status', fn ($q) => $q->where('status', $status));
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        // If you want pagination later:
        // $perPage = min((int) $request->get('per_page', 20), 100);
        // $tables = $query->paginate($perPage);

        $tables = $query->get();

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    public function show(Table $table, Request $request)
    {
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
            'capacity'     => ['required', 'integer', 'min:1', 'max:100'],
            'location_tag' => ['nullable', 'string', 'max:100'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        // Ensure branch belongs to same restaurant
        $this->assertBranchInStaffRestaurant((int) $data['branch_id']);

        // Force restaurant_id from staff
        $data['restaurant_id'] = $this->staffRestaurantId();

        // Default active
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
        $this->assertTableInStaffRestaurant($table);

        $data = $request->validate([
            'branch_id'    => ['sometimes', 'integer', 'exists:restaurant_branches,id'],
            'table_code'   => ['sometimes', 'string', 'max:50'],
            'capacity'     => ['sometimes', 'integer', 'min:1', 'max:100'],
            'location_tag' => ['sometimes', 'nullable', 'string', 'max:100'],
            'is_active'    => ['sometimes', 'boolean'],
        ]);

        // If branch_id changed, ensure it belongs to same restaurant
        if (array_key_exists('branch_id', $data) && $data['branch_id'] !== null) {
            $this->assertBranchInStaffRestaurant((int) $data['branch_id']);
        }

        // Force restaurant_id from staff (prevent tampering)
        $data['restaurant_id'] = $this->staffRestaurantId();

        // Prevent null overriding NOT NULL columns
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
        $this->assertTableInStaffRestaurant($table);

        // Prevent deleting table with active reservations (if relation exists)
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

    // TableController.php

public function reserve(Table $table)
{
    $this->assertTableInStaffRestaurant($table);

    $tableStatus = TableStatus::updateOrCreate(
        ['table_id' => $table->id],
        ['status' => 'reserved']
    );

    return response()->json([
        'success' => true,
        'message' => 'Table reserved successfully',
        'data' => $tableStatus,
    ]);
}

public function select(Table $table)
{
    $this->assertTableInStaffRestaurant($table);

    $tableStatus = TableStatus::updateOrCreate(
        ['table_id' => $table->id],
        ['status' => 'occupied']  // بدل 'selected'
    );

    return response()->json([
        'success' => true,
        'message' => 'Table marked as occupied successfully',
        'data' => $tableStatus,
    ]);
}


}
