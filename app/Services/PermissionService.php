<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function userHasPermission(int $userId, string $permissionName): bool
    {
        return DB::table('user_roles_assignment as ura')
            ->join('user_role_permissions as urp', 'urp.role_id', '=', 'ura.role_id')
            ->join('user_permissions as up', 'up.id', '=', 'urp.permission_id')
            ->where('ura.user_id', $userId)
            ->where('up.name', $permissionName)
            ->exists();
    }
}
