<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 20), 100);

        $query = Table::query()
            ->with([
                'status',
                // لو عايزة تقللي حجم الداتا: حددي أعمدة
                // 'status:id,table_id,status,current_reservation_id,occupied_since,estimated_free_at,updated_at',

                'statusHistory' => function ($q) use ($request) {
                    $limit = min((int) $request->get('history_limit', 20), 200);
                    $q->limit($limit);
                },
            ]);

        // Filters (اختياري)
        if ($request->filled('branch_id')) {
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
}
