<?php

namespace App\Filament\Resources\CustomerLoyalties\Pages;

use App\Filament\Resources\CustomerLoyalties\CustomerLoyaltyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerLoyalty extends ViewRecord
{
    protected static string $resource = CustomerLoyaltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
