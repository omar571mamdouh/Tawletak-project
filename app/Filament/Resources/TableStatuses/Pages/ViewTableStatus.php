<?php

namespace App\Filament\Resources\TableStatuses\Pages;

use App\Filament\Resources\TableStatuses\TableStatusResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTableStatus extends ViewRecord
{
    protected static string $resource = TableStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
