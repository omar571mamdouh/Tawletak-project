<?php

namespace App\Filament\Resources\MenuSections\Pages;

use App\Filament\Resources\MenuSections\MenuSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuSection extends EditRecord
{
    protected static string $resource = MenuSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
