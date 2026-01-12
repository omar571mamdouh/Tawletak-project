<?php

namespace App\Filament\Resources\TableStatuses\Pages;

use App\Filament\Resources\TableStatuses\TableStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTableStatus extends EditRecord
{
    protected static string $resource = TableStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
