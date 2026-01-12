<?php

namespace App\Filament\Resources\RestaurantBranches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\ViewField;


class RestaurantBranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('address')
                    ->required(),
                ViewField::make('map')
    ->view('components.osm-map-picker')
    ->columnSpanFull(),


               TextInput::make('lat')
    ->numeric()
    ->required()
    ->live()
    ->readOnly(),

TextInput::make('lng')
    ->numeric()
    ->required()
    ->live()
    ->readOnly(),

                TimePicker::make('opening_time')
                    ->required(),
                TimePicker::make('closing_time')
                    ->required(),
                TextInput::make('timezone')
                    ->required()
                    ->default('Asia/Amman'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
