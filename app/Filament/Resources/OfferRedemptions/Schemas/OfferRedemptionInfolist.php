<?php

namespace App\Filament\Resources\OfferRedemptions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OfferRedemptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('offer.title')
                    ->label('Offer'),
                TextEntry::make('customer.name')
                    ->label('Customer'),
                TextEntry::make('reservation.id')
                    ->label('Reservation')
                    ->placeholder('-'),
                TextEntry::make('visit.id')
                    ->label('Visit')
                    ->placeholder('-'),
                TextEntry::make('redeemed_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
