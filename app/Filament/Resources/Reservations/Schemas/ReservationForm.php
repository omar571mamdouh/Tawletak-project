<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Reservation Details'))
                    ->description(__('Basic information about the reservation'))
                    ->schema([
                        Grid::make(1)->schema([
                            Select::make('customer_id')
                                ->label(__('Customer'))
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder(__('Select customer')),

                            Select::make('branch_id')
                                ->label(__('Branch'))
                                ->relationship('branch', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder(__('Select branch')),

                            Select::make('table_id')
                                ->label(__('Table'))
                                ->relationship('table', 'id')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->placeholder(__('Select table (optional)')),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('party_size')
                                ->label(__('Party Size'))
                                ->placeholder('2, 4, 6...')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50),

                            DateTimePicker::make('reservation_time')
                                ->label(__('Reservation Time'))
                                ->required()
                                ->seconds(false)
                                ->default(now()->addHours(2)),

                            TextInput::make('expected_duration_minutes')
                                ->label(__('Duration (Minutes)'))
                                ->placeholder('90')
                                ->numeric()
                                ->default(90)
                                ->minValue(15)
                                ->maxValue(480)
                                ->suffix('min'),
                        ]),
                    ]),

                Section::make(__('Status & Source'))
                    ->description(__('Reservation status and booking source'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label(__('Status'))
                                ->options([
                                    'pending' => __('Pending'),
                                    'confirmed' => __('Confirmed'),
                                    'rejected' => __('Rejected'),
                                    'cancelled' => __('Cancelled'),
                                    'no_show' => __('No Show'),
                                    'seated' => __('Seated'),
                                    'completed' => __('Completed'),
                                ])
                                ->default('pending')
                                ->required()
                                ->placeholder(__('Select status')),

                            Select::make('source')
                                ->label(__('Source'))
                                ->options([
                                    'app' => __('App'),
                                    'walk_in' => __('Walk-in'),
                                    'phone' => __('Phone'),
                                ])
                                ->nullable()
                                ->placeholder(__('Select source')),
                        ]),
                    ]),

                Section::make(__('Timestamps'))
                    ->description(__('Track important reservation events'))
                    ->schema([
                        Grid::make(2)->schema([
                            DateTimePicker::make('confirmed_at')
                                ->label(__('Confirmed At'))
                                ->nullable()
                                ->seconds(false),

                            DateTimePicker::make('cancelled_at')
                                ->label(__('Cancelled At'))
                                ->nullable()
                                ->seconds(false),

                            DateTimePicker::make('seated_at')
                                ->label(__('Seated At'))
                                ->nullable()
                                ->seconds(false),

                            DateTimePicker::make('completed_at')
                                ->label(__('Completed At'))
                                ->nullable()
                                ->seconds(false),
                        ]),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ]);
    }
}