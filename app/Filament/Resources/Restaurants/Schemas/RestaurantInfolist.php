<?php

namespace App\Filament\Resources\Restaurants\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RestaurantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Restaurant Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('Restaurant Name')
                                ->columnSpanFull(),

                            TextEntry::make('category')
                                ->label('Category'),

                            TextEntry::make('price_range')
                                ->label('Price Range')
                                ->placeholder('-'),
                        ]),
                    ]),

                Section::make('Contact & Status')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('phone')
                                ->label('Phone Number')
                                ->placeholder('-'),

                            IconEntry::make('is_active')
                                ->label('Active')
                                ->boolean(),
                        ]),
                    ]),

                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Restaurant Description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('System Info')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('created_at')
                                ->label('Created At')
                                ->dateTime(),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime(),
                        ]),
                    ]),
            ]);
    }
}
