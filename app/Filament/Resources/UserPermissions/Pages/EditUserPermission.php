<?php

namespace App\Filament\Resources\UserPermissions\Pages;

use App\Filament\Resources\UserPermissions\UserPermissionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUserPermission extends EditRecord
{
    protected static string $resource = UserPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
