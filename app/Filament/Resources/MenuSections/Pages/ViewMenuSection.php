<?php

namespace App\Filament\Resources\MenuSections\Pages;

use App\Filament\Resources\MenuSections\MenuSectionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMenuSection extends ViewRecord
{
    protected static string $resource = MenuSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
