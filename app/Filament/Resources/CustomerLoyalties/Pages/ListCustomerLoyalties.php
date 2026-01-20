<?php

namespace App\Filament\Resources\CustomerLoyalties\Pages;

use App\Filament\Resources\CustomerLoyalties\CustomerLoyaltyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomerLoyalties extends ListRecords
{
    protected static string $resource = CustomerLoyaltyResource::class;

    protected function getHeaderActions(): array
    {
        return [
         
        ];
    }
}
