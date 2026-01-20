<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Restaurant;
use App\Models\RestaurantRole;
use App\Models\RestaurantStaff;
use App\Models\RestaurantStaffRoleAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /**
             * 1) Seed Permissions (حد أدنى للتجربة)
             * تقدر تزود عليهم بعدين بسهولة
             */
            $permissions = [
                // Orders
                ['key' => 'orders.view',   'group' => 'orders'],
                ['key' => 'orders.accept', 'group' => 'orders'],
                ['key' => 'orders.reject', 'group' => 'orders'],

                // Reservations
                ['key' => 'reservations.view',   'group' => 'reservations'],
                ['key' => 'reservations.update', 'group' => 'reservations'],

                // Staff management (لـ owner)
                ['key' => 'staff.view',        'group' => 'staff'],
                ['key' => 'staff.create',      'group' => 'staff'],
                ['key' => 'staff.update',      'group' => 'staff'],
                ['key' => 'staff.assign_role', 'group' => 'staff'],

                // RBAC management (لو هتسمح للـ owner يغير permissions/roles)
                // سيبها دلوقتي أو خليه للـ super admin فقط
                ['key' => 'rbac.manage', 'group' => 'rbac'],
            ];

            foreach ($permissions as $p) {
                Permission::updateOrCreate(
                    ['key' => $p['key']],
                    ['group' => $p['group']]
                );
            }

            // Helper: map key => id
            $permissionIdsByKey = Permission::query()
                ->pluck('id', 'key')
                ->toArray();

            /**
             * 2) Default Roles per restaurant
             */
            $defaultRoles = ['owner', 'manager', 'host', 'staff'];

            /**
             * 3) Role -> Permissions mapping (تقدر تغيّره حسب قراركم)
             */
            $rolePermissionKeys = [
                'owner' => [
                    'orders.view', 'orders.accept', 'orders.reject',
                    'reservations.view', 'reservations.update',
                    'staff.view', 'staff.create', 'staff.update', 'staff.assign_role',
                    // لو عايز owner يقدر يدير الـ RBAC:
                    'rbac.manage',
                ],
                'manager' => [
                    'orders.view', 'orders.accept', 'orders.reject',
                    'reservations.view', 'reservations.update',
                    'staff.view',
                ],
                'host' => [
                    'orders.view',
                    'reservations.view', 'reservations.update',
                ],
                'staff' => [
                    'orders.view', 'orders.accept',
                    'reservations.view',
                ],
            ];

            /**
             * 4) Create roles لكل مطعم + sync permissions
             */
            Restaurant::query()->select('id')->chunkById(200, function ($restaurants) use (
                $defaultRoles, $rolePermissionKeys, $permissionIdsByKey
            ) {
                foreach ($restaurants as $restaurant) {
                    $rolesByName = [];

                    foreach ($defaultRoles as $roleName) {
                        $role = RestaurantRole::firstOrCreate(
                            ['restaurant_id' => $restaurant->id, 'name' => $roleName],
                            ['restaurant_id' => $restaurant->id, 'name' => $roleName]
                        );
                        $rolesByName[$roleName] = $role;

                        // Sync permissions for this role
                        $keys = $rolePermissionKeys[$roleName] ?? [];
                        $ids = array_values(array_filter(array_map(
                            fn($k) => $permissionIdsByKey[$k] ?? null,
                            $keys
                        )));

                        $role->permissions()->sync($ids);
                    }

                    /**
                     * 5) Assign staff الحاليين حسب enum restaurant_staff.role
                     */
                    RestaurantStaff::query()
                        ->where('restaurant_id', $restaurant->id)
                        ->select('id', 'role')
                        ->chunkById(500, function ($staffChunk) use ($rolesByName) {
                            foreach ($staffChunk as $staff) {
                                $roleName = $staff->role; // owner/manager/host/staff

                                if (!isset($rolesByName[$roleName])) {
                                    continue;
                                }

                                RestaurantStaffRoleAssignment::updateOrCreate(
                                    ['staff_id' => $staff->id],
                                    ['restaurant_role_id' => $rolesByName[$roleName]->id]
                                );
                            }
                        });
                }
            });
        });
    }
}
