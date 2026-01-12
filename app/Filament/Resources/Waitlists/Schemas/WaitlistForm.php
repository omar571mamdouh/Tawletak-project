<?php

namespace App\Filament\Resources\Waitlists\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WaitlistForm
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
                TextInput::make('party_size')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
            'waiting' => 'Waiting',
            'notified' => 'Notified',
            'seated' => 'Seated',
            'cancelled' => 'Cancelled',
        ])
                    ->default('waiting')
                    ->required(),
                TextInput::make('estimated_wait_minutes')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('notified_at'),
                DateTimePicker::make('seated_at'),
            ]);
    }
}
