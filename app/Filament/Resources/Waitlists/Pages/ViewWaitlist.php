<?php

namespace App\Filament\Resources\Waitlists\Pages;

use App\Filament\Resources\Waitlists\WaitlistResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWaitlist extends ViewRecord
{
    protected static string $resource = WaitlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
