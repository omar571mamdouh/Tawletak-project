<?php

namespace App\Filament\Resources\MenuSections\Pages;

use App\Filament\Resources\MenuSections\MenuSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuSections extends ListRecords
{
    protected static string $resource = MenuSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
