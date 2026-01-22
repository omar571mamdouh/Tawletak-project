<?php

namespace App\Filament\Resources\UserPermissions\Pages;

use App\Filament\Resources\UserPermissions\UserPermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserPermission extends CreateRecord
{
    protected static string $resource = UserPermissionResource::class;
}
