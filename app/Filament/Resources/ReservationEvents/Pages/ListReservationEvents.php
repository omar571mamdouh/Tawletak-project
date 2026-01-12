<?php

namespace App\Filament\Resources\ReservationEvents\Pages;

use App\Filament\Resources\ReservationEvents\ReservationEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReservationEvents extends ListRecords
{
    protected static string $resource = ReservationEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
