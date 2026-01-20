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
}
