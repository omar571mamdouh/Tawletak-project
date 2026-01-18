<?php

namespace App\Filament\Resources\OfferRedemptions\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;

class OfferRedemptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('offer_id')
                    ->relationship('offer', 'title')
                    ->required(),

           Toggle::make('all_customers')
    ->label('Select all customers')
    ->reactive()
    ->afterStateUpdated(fn (callable $set, $state) =>
        $state ? $set('customer_ids', []) : null
    ),

Select::make('customer_ids')
    ->label('Customers')
    ->multiple()
    ->searchable()
    ->options(fn () => Customer::query()->orderBy('name')->pluck('name', 'id'))
    ->disabled(fn (callable $get) => (bool) $get('all_customers'))
    ->required(fn (callable $get) => ! (bool) $get('all_customers')),

Select::make('reservation_id')
    ->relationship('reservation', 'id')
    ->nullable()
    ->hidden(fn (callable $get) => (bool) $get('all_customers')),

Select::make('visit_id')
    ->relationship('visit', 'id')
    ->nullable()
    ->hidden(fn (callable $get) => (bool) $get('all_customers')),

DateTimePicker::make('redeemed_at')
    ->required(),


        ]);
    }
}
