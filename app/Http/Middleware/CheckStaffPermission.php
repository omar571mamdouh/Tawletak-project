<?php

namespace App\Http\Middleware;

use App\Models\RestaurantStaffRoleAssignment;
use Closure;
use Illuminate\Http\Request;

class CheckStaffPermission
{
    public function handle(Request $request, Closure $next, string $permissionKey)
    {
        // لازم يكون staff authenticated
        $staff = auth('staff')->user();
        if (!$staff) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // هات role assignment
        $assignment = RestaurantStaffRoleAssignment::query()
            ->with(['role.permissions'])
            ->where('staff_id', $staff->id)
            ->first();

        if (!$assignment || !$assignment->role) {
            return response()->json([
                'message' => 'No role assigned for this staff.',
            ], 403);
        }

        // check permission
        $hasPermission = $assignment->role->permissions
            ->contains('key', $permissionKey);

        if (!$hasPermission) {
            return response()->json([
                'message' => 'Forbidden. Missing permission: ' . $permissionKey,
            ], 403);
        }

        // (اختياري) نحقن role/permissions للـ request لو احتجته في الكنترولر
        // $request->attributes->set('staff_role', $assignment->role->name);

        return $next($request);
    }
}
