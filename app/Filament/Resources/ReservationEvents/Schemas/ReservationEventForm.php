<?php

namespace App\Filament\Resources\ReservationEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReservationEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('reservation_id')
                    ->relationship('reservation', 'id')
                    ->required(),
                TextInput::make('event_type')
                    ->required(),
                DateTimePicker::make('event_time')
                    ->required(),
                Select::make('actor_type')
                    ->options(['customer' => 'Customer', 'staff' => 'Staff', 'admin' => 'Admin', 'system' => 'System'])
                    ->default(null),
                TextInput::make('actor_id')
                    ->numeric()
                    ->default(null),
                Textarea::make('meta_json')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
