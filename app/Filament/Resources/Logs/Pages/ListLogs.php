<?php

namespace App\Filament\Resources\Logs\Pages;

use App\Filament\Resources\Logs\LogsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLogs extends ListRecords
{
    protected static string $resource = LogsResource::class;

    protected function getHeaderActions(): array
    {
        return [
           
        ];
    }
}
