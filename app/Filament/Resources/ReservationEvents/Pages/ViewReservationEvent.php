<?php

namespace App\Filament\Resources\ReservationEvents\Pages;

use App\Filament\Resources\ReservationEvents\ReservationEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewReservationEvent extends ViewRecord
{
    protected static string $resource = ReservationEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
