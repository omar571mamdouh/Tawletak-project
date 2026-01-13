<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\Action;

use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions as SchemaActions;

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
            SchemaActions::make([
                Action::make('set_confirmed_now')
                    ->label(__('Set Confirmed Now'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => filled($record) && blank($record->confirmed_at) && $record->status !== 'cancelled')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'confirmed',
                            'confirmed_at' => now(),
                        ]);

                        Notification::make()->title(__('Confirmed timestamp set'))->success()->send();
                    }),

                Action::make('clear_confirmed')
                    ->label(__('Clear Confirmed'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record) && filled($record->confirmed_at))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'confirmed_at' => null,
                            // اختياري: رجّع status
                            // 'status' => 'pending',
                        ]);

                        Notification::make()->title(__('Confirmed timestamp cleared'))->success()->send();
                    }),
            ])->columns(1),

            SchemaActions::make([
                Action::make('set_cancelled_now')
                    ->label(__('Set Cancelled Now'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => filled($record) && blank($record->cancelled_at) && $record->status !== 'completed')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                        ]);

                        Notification::make()->title(__('Cancelled timestamp set'))->danger()->send();
                    }),

                Action::make('clear_cancelled')
                    ->label(__('Clear Cancelled'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record) && filled($record->cancelled_at))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'cancelled_at' => null,
                        ]);

                        Notification::make()->title(__('Cancelled timestamp cleared'))->success()->send();
                    }),
            ])->columns(1),

            SchemaActions::make([
                Action::make('set_seated_now')
                    ->label(__('Set Seated Now'))
                    ->icon('heroicon-o-user-group')
                    ->color('warning')
                    ->visible(fn ($record) => filled($record)
                        && blank($record->seated_at)
                        && $record->status === 'confirmed'
                        && blank($record->cancelled_at)
                    )
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'seated',
                            'seated_at' => now(),
                        ]);

                        Notification::make()->title(__('Seated timestamp set'))->success()->send();
                    }),

                Action::make('clear_seated')
                    ->label(__('Clear Seated'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record) && filled($record->seated_at))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'seated_at' => null,
                            // اختياري: رجّع status
                            // 'status' => 'confirmed',
                        ]);

                        Notification::make()->title(__('Seated timestamp cleared'))->success()->send();
                    }),
            ])->columns(1),

            SchemaActions::make([
                Action::make('set_completed_now')
                    ->label(__('Set Completed Now'))
                    ->icon('heroicon-o-flag')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record)
                        && blank($record->completed_at)
                        && $record->status === 'seated'
                        && blank($record->cancelled_at)
                    )
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        Notification::make()->title(__('Completed timestamp set'))->success()->send();
                    }),

                Action::make('clear_completed')
                    ->label(__('Clear Completed'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record) && filled($record->completed_at))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'completed_at' => null,
                            // اختياري: رجّع status
                            // 'status' => 'seated',
                        ]);

                        Notification::make()->title(__('Completed timestamp cleared'))->success()->send();
                    }),
            ])->columns(1),
        ]),
    ])
    ->collapsible()
    ->collapsed(true),
            ]);
    }
}