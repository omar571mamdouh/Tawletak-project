<?php

namespace App\Filament\Resources\TableStatusHistories\Pages;

use App\Filament\Resources\TableStatusHistories\TableStatusHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTableStatusHistories extends ListRecords
{
    protected static string $resource = TableStatusHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
