<?php

namespace App\Filament\Resources\OfferRedemptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class OfferRedemptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('offer_id')
                    ->relationship('offer', 'title')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('reservation_id')
                    ->relationship('reservation', 'id')
                    ->default(null),
                Select::make('visit_id')
                    ->relationship('visit', 'id')
                    ->default(null),
                DateTimePicker::make('redeemed_at')
                    ->required(),
            ]);
    }
}
