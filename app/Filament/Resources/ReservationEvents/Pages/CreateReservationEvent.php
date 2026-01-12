<?php

namespace App\Filament\Resources\ReservationEvents\Pages;

use App\Filament\Resources\ReservationEvents\ReservationEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReservationEvent extends CreateRecord
{
    protected static string $resource = ReservationEventResource::class;
}
