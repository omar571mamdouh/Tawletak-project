<?php

namespace App\Filament\Resources\UserPermissions\Pages;

use App\Filament\Resources\UserPermissions\UserPermissionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUserPermission extends ViewRecord
{
    protected static string $resource = UserPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
