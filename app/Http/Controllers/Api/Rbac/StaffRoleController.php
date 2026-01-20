<?php

namespace App\Http\Controllers\Api\Rbac;

use App\Http\Controllers\Controller;
use App\Models\RestaurantRole;
use App\Models\RestaurantStaff;
use App\Models\RestaurantStaffRoleAssignment;
use Illuminate\Http\Request;

class StaffRoleController extends Controller
{
    private function currentRestaurantId(): int
    {
        return auth('staff')->user()->restaurant_id;
    }

    public function assign(Request $request, RestaurantStaff $staff)
    {
        $restaurantId = $this->currentRestaurantId();

        // staff لازم يكون في نفس المطعم
        if ($staff->restaurant_id !== $restaurantId) {
            return response()->json(['message' => 'Staff not in your restaurant'], 403);
        }

        $data = $request->validate([
            'role' => ['required', 'string', 'max:50'], // owner/manager/host/staff
        ]);

        $role = RestaurantRole::query()
            ->where('restaurant_id', $restaurantId)
            ->where('name', $data['role'])
            ->first();

        if (!$role) {
            return response()->json(['message' => 'Role not found in this restaurant'], 404);
        }

        RestaurantStaffRoleAssignment::updateOrCreate(
            ['staff_id' => $staff->id],
            ['restaurant_role_id' => $role->id]
        );

        return response()->json([
            'message' => 'Staff role assigned',
            'data' => [
                'staff_id' => $staff->id,
                'role' => $role->name,
            ],
        ]);
    }
}
