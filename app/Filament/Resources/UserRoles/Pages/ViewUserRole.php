<?php

namespace App\Filament\Resources\UserRoles\Pages;

use App\Filament\Resources\UserRoles\UserRoleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUserRole extends ViewRecord
{
    protected static string $resource = UserRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
