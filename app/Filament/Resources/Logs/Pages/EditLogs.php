<?php

namespace App\Filament\Resources\Logs\Pages;

use App\Filament\Resources\Logs\LogsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLogs extends EditRecord
{
    protected static string $resource = LogsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
