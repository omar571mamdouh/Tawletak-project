<?php

namespace App\Policies;

use App\Models\User;

class GenericResourcePolicy
{
    public function before(User $user, string $ability)
    {
        // لو عندك سوبر ادمن
       if ($user->role === 'super_admin') {
    return true;
}


        return null; // كمّل للـ methods تحت
    }

    protected function has(User $user, string $resourceKey, string $action): bool
    {
        // هنا بتعمل check من جداولك انت (role_permissions + user_role_assign)
        // مثال: permission name = "{$resourceKey}.{$action}"

        $permissionName = "{$resourceKey}.{$action}";

        return app(\App\Services\PermissionService::class)
            ->userHasPermission($user->id, $permissionName);
    }

    public function viewAny(User $user, string $resourceKey): bool
    {
        return $this->has($user, $resourceKey, 'viewAny');
    }

    public function view(User $user, string $resourceKey): bool
    {
        return $this->has($user, $resourceKey, 'view');
    }

    public function create(User $user, string $resourceKey): bool
    {
        return $this->has($user, $resourceKey, 'create');
    }

    public function update(User $user, string $resourceKey): bool
    {
        return $this->has($user, $resourceKey, 'update');
    }

    public function delete(User $user, string $resourceKey): bool
    {
        return $this->has($user, $resourceKey, 'delete');
    }
}
