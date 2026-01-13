<?php

namespace App\Filament\Resources\CustomerLoyalties\Pages;

use App\Filament\Resources\CustomerLoyalties\CustomerLoyaltyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerLoyalty extends EditRecord
{
    protected static string $resource = CustomerLoyaltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
