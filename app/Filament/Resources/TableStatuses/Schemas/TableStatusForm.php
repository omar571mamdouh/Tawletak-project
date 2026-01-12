<?php

namespace App\Filament\Resources\TableStatuses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TableStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('Table Status'))
                ->description(__('Manage the current status of the table'))
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('status')
                            ->label(__('Status'))
                            ->options([
                                'available'      => __('Available'),
                                'reserved'       => __('Reserved'),
                                'occupied'       => __('Occupied'),
                                'out_of_service' => __('Out of Service'),
                            ])
                            ->default('available')
                            ->required()
                            ->live()
                            ->placeholder(__('Select status'))
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // occupied: fill times automatically if empty
                                if ($state === 'occupied') {
                                    if (! $get('occupied_since')) {
                                        $set('occupied_since', now());
                                    }

                                    if (! $get('estimated_free_at')) {
                                        $set('estimated_free_at', now()->addMinutes(75));
                                    }
                                }

                                // available / out_of_service: clear everything
                                if (in_array($state, ['available', 'out_of_service'])) {
                                    $set('occupied_since', null);
                                    $set('estimated_free_at', null);
                                    $set('current_reservation_id', null);
                                }

                                // reserved: no occupied_since (table isn't occupied yet)
                                if ($state === 'reserved') {
                                    $set('occupied_since', null);
                                    // estimated_free_at is expected to be set (reservation time / hold-until)
                                }
                            })
                            ->columnSpanFull(),

                        // ✅ مؤقتًا: اخفيه لحد ما تعمل reservations resource/model كامل
                        Select::make('current_reservation_id')
                            ->label(__('Reservation'))
                            ->relationship('currentReservation', 'id')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->hidden()
                            ->columnSpanFull(),
                    ]),
                ]),

            Section::make(__('Time Tracking'))
                ->description(__('Track occupied and estimated free time'))
                ->schema([
                    Grid::make(2)->schema([
                        DateTimePicker::make('occupied_since')
                            ->label(__('Occupied Since'))
                            ->nullable()
                            ->visible(fn (callable $get) => $get('status') === 'occupied')
                            ->seconds(false),

                        DateTimePicker::make('estimated_free_at')
                            ->label(__('Estimated Free At'))
                            ->nullable()
                            ->visible(fn (callable $get) => in_array($get('status'), ['occupied', 'reserved']))
                            ->seconds(false),
                    ]),
                ])
                ->visible(fn (callable $get) => in_array($get('status'), ['occupied', 'reserved'])),
        ]);
    }
}