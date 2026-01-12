<?php

namespace App\Filament\Resources\TableStatuses\Pages;

use App\Filament\Resources\TableStatuses\TableStatusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTableStatuses extends ListRecords
{
    protected static string $resource = TableStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
