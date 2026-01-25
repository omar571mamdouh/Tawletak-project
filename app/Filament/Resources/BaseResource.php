<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Support\Facades\Gate;

abstract class BaseResource extends Resource
{
    protected static function resourceKey(): string
    {
        // default: اسم الـ resource كـ slug
        // مثال: UserResource => users
        return str(static::getSlug())->replace('/', '.')->toString();
    }

    public static function canViewAny(): bool
    {
        return Gate::allows('filament.' . static::resourceKey() . '.viewAny');
    }

    public static function canCreate(): bool
    {
        return Gate::allows('filament.' . static::resourceKey() . '.create');
    }

    public static function canEdit($record): bool
    {
        return Gate::allows('filament.' . static::resourceKey() . '.update');
    }

    public static function canDelete($record): bool
    {
        return Gate::allows('filament.' . static::resourceKey() . '.delete');
    }

    public static function canView($record): bool
    {
        return Gate::allows('filament.' . static::resourceKey() . '.view');
    }
}
