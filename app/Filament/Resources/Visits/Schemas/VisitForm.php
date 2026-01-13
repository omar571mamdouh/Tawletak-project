<?php

namespace App\Filament\Resources\Visits\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VisitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->required(),
                Select::make('reservation_id')
                    ->relationship('reservation', 'id')
                    ->default(null),
                Select::make('table_id')
                    ->relationship('table', 'id')
                    ->default(null),
                DateTimePicker::make('seated_at')
                    ->required(),
                DateTimePicker::make('left_at'),
                TextInput::make('bill_amount')
                    ->numeric()
                    ->default(null),
                Select::make('status')
                    ->options(['active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                    ->default('active')
                    ->required(),
            ]);
    }
}
