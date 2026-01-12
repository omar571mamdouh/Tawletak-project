<?php

namespace App\Filament\Resources\ReservationEvents\Pages;

use App\Filament\Resources\ReservationEvents\ReservationEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditReservationEvent extends EditRecord
{
    protected static string $resource = ReservationEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
