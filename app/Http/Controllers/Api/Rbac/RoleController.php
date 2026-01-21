<?php

namespace App\Http\Controllers\Api\Rbac;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RestaurantRole;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private function currentRestaurantId(): int
    {
        return auth('staff')->user()->restaurant_id;
    }

    public function index()
    {
        $restaurantId = $this->currentRestaurantId();

        $roles = RestaurantRole::query()
            ->where('restaurant_id', $restaurantId)
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $roles]);
    }

    public function showPermissions(RestaurantRole $role)
    {
        $restaurantId = $this->currentRestaurantId();

        if ($role->restaurant_id !== $restaurantId) {
            return response()->json(['message' => 'Role not in your restaurant'], 403);
        }

        $role->load('permissions');

        return response()->json([
            'data' => [
                'role' => $role->only(['id', 'name', 'restaurant_id']),
                'permissions' => $role->permissions->values(),
            ],
        ]);
    }

    public function syncPermissions(Request $request, RestaurantRole $role)
    {
        $restaurantId = $this->currentRestaurantId();

        if ($role->restaurant_id !== $restaurantId) {
            return response()->json(['message' => 'Role not in your restaurant'], 403);
        }

        $data = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'max:150'],
        ]);

        $permissionIds = Permission::query()
            ->whereIn('key', $data['permissions'])
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);

        return response()->json([
            'message' => 'Role permissions updated',
            'data' => [
                'role_id' => $role->id,
                'permissions_count' => count($permissionIds),
            ],
        ]);
    }



    public function store(Request $request)
{
    $restaurantId = $this->currentRestaurantId();

    $data = $request->validate([
        'name' => ['required', 'string', 'max:50'],
    ]);

    $role = RestaurantRole::create([
        'restaurant_id' => $restaurantId,
        'name' => $data['name'],
    ]);

    return response()->json([
        'message' => 'Role created',
        'data' => $role,
    ], 201);
}



public function update(Request $request, RestaurantRole $role)
{
    $restaurantId = $this->currentRestaurantId();

    if ($role->restaurant_id !== $restaurantId) {
        return response()->json(['message' => 'Role not in your restaurant'], 403);
    }

    $data = $request->validate([
        'name' => [
            'required','string','max:50',
            Rule::unique('restaurant_roles', 'name')
                ->where(fn($q) => $q->where('restaurant_id', $restaurantId))
                ->ignore($role->id),
        ],
    ]);

    $role->update($data);

    return response()->json([
        'message' => 'Role updated',
        'data' => $role,
    ]);
}


public function destroy(RestaurantRole $role)
{
    $restaurantId = $this->currentRestaurantId();

    if ($role->restaurant_id !== $restaurantId) {
        return response()->json(['message' => 'Role not in your restaurant'], 403);
    }

    if ($role->name === 'owner') {
        return response()->json(['message' => 'Cannot delete owner role'], 422);
    }

    // لو فيه Staff متعينين عليها
    $assignedCount = \App\Models\RestaurantStaffRoleAssignment::where('restaurant_role_id', $role->id)->count();
    if ($assignedCount > 0) {
        return response()->json([
            'message' => 'Role has assigned staff. Reassign them first.',
            'assigned_staff_count' => $assignedCount,
        ], 422);
    }

    $role->delete(); // pivot permissions هيتحذف cascade

    return response()->json(['message' => 'Role deleted']);
}

}
