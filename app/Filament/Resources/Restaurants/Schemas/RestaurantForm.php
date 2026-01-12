<?php

namespace App\Filament\Resources\Restaurants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RestaurantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Restaurant Information')
                    ->description('Basic details about the restaurant')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Restaurant Name')
                                ->required()
                                ->maxLength(200)
                                ->columnSpanFull()
                                ->placeholder('Restaurant Name'),

                            TextInput::make('category')
                                ->required()
                                ->label('Category')
                                ->placeholder('Cafe, Casual Dining, Fast Food'),

                            TextInput::make('price_range')
                                ->label('Price Range')
                                ->placeholder('$, $$, $$$'),
                        ]),
                    ]),

                Section::make('Contact & Status')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('phone')
                                ->label('Phone Number')
                                ->tel()
                                ->placeholder('+962 7X XXX XXXX'),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ]),
                    ]),

                Section::make('Description')
                    ->schema([
                        Textarea::make('description')
                            ->label('Restaurant Description')
                            ->rows(4)
                            ->placeholder('Short description about the restaurant...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
