<?php

namespace App\Filament\Resources\TableStatusHistories\Pages;

use App\Filament\Resources\TableStatusHistories\TableStatusHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTableStatusHistory extends EditRecord
{
    protected static string $resource = TableStatusHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
