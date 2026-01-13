<?php

namespace App\Filament\Resources\CustomerLoyalties\Pages;

use App\Filament\Resources\CustomerLoyalties\CustomerLoyaltyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerLoyalty extends CreateRecord
{
    protected static string $resource = CustomerLoyaltyResource::class;
}
