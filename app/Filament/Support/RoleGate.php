<?php

namespace App\Filament\Support;

class RoleGate
{
    public static function role(): ?string
    {
        return auth()->user()?->role;
    }

    public static function isAny(array $roles): bool
    {
        return in_array(self::role(), $roles, true);
    }
}
