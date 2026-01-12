<?php

namespace App\Filament\Resources\Tables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Table Information'))
                    ->description(__('Basic details about the table'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('branch_id')
                                ->label(__('Branch'))
                                ->relationship('branch', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder(__('Select branch')),

                            TextInput::make('table_code')
                                ->label(__('Table Code'))
                                ->placeholder('T001, A-12...')
                                ->required()
                                ->maxLength(50),
                        ]),
                    ]),

                Section::make(__('Table Details'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('capacity')
                                ->label(__('Capacity'))
                                ->placeholder('2, 4, 6...')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100),

                            Select::make('location_tag')
                                ->label(__('Location'))
                                ->options([
                                    'indoor' => __('Indoor'),
                                    'outdoor' => __('Outdoor'),
                                    'vip' => __('VIP'),
                                ])
                                ->placeholder(__('Select location'))
                                ->nullable(),
                        ]),
                    ]),

                Section::make(__('Status'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}