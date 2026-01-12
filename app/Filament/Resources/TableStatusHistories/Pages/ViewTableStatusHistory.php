<?php

namespace App\Filament\Resources\TableStatusHistories\Pages;

use App\Filament\Resources\TableStatusHistories\TableStatusHistoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTableStatusHistory extends ViewRecord
{
    protected static string $resource = TableStatusHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
