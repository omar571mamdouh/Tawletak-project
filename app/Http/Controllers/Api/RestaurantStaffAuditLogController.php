<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; 
use App\Models\RestaurantStaffAuditLog;
use Illuminate\Http\Request;

class RestaurantStaffAuditLogController extends Controller
{
    public function index(Request $request)
    {
        $owner = auth('staff')->user();

        $q = RestaurantStaffAuditLog::query()
            ->where('restaurant_id', $owner->restaurant_id)
            ->with([
                'staff:id,name,email,branch_id',
                'branch:id,name',
            ]);

        // Filters
        if ($request->filled('branch_id')) {
            $q->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('staff_id')) {
            $q->where('staff_id', $request->integer('staff_id'));
        }

        if ($request->filled('action')) {
            $q->where('action', $request->string('action'));
        }

        if ($request->filled('entity_type')) {
            $q->where('entity_type', $request->string('entity_type'));
        }

        if ($request->filled('entity_id')) {
            $q->where('entity_id', $request->integer('entity_id'));
        }

        if ($request->filled('from')) {
            $q->where('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $q->where('created_at', '<=', $request->date('to'));
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        return response()->json([
            'data' => $q->latest()->paginate($perPage),
        ]);
    }

    public function show(RestaurantStaffAuditLog $log)
    {
        $owner = auth('staff')->user();

        // Security: owner يشوف مطعمه فقط
        abort_unless($log->restaurant_id === $owner->restaurant_id, 404);

        $log->load([
            'staff:id,name,email,branch_id',
            'branch:id,name',
        ]);

        return response()->json(['data' => $log]);
    }
}
