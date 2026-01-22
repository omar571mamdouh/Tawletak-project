<?php

namespace App\Filament\Resources\UserRoles\Pages;

use App\Filament\Resources\UserRoles\UserRoleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUserRole extends EditRecord
{
    protected static string $resource = UserRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
