<?php

namespace App\Filament\Resources\TableStatusHistories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TableStatusHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Status Change Information'))
                    ->description(__('Track table status changes'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('table_id')
                                ->label(__('Table'))
                                ->relationship('table', 'id')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder(__('Select table')),

                            Select::make('changed_by_staff_id')
                                ->label(__('Changed By Staff'))
                                ->relationship('changedBy', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->placeholder(__('Select staff member')),
                        ]),
                    ]),

                Section::make(__('Status Details'))
                    ->description(__('Old and new status information'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('old_status')
                                ->label(__('Old Status'))
                                ->options([
                                    'available' => __('Available'),
                                    'reserved' => __('Reserved'),
                                    'occupied' => __('Occupied'),
                                    'out_of_service' => __('Out of Service'),
                                ])
                                ->required()
                                ->placeholder(__('Select old status')),

                            Select::make('new_status')
                                ->label(__('New Status'))
                                ->options([
                                    'available' => __('Available'),
                                    'reserved' => __('Reserved'),
                                    'occupied' => __('Occupied'),
                                    'out_of_service' => __('Out of Service'),
                                ])
                                ->required()
                                ->placeholder(__('Select new status')),
                        ]),

                        Grid::make(2)->schema([
                            DateTimePicker::make('timestamp')
                                ->label(__('Timestamp'))
                                ->required()
                                ->default(now())
                                ->seconds(false),

                            TextInput::make('note')
                                ->label(__('Note'))
                                ->placeholder(__('Optional note about this change'))
                                ->nullable()
                                ->maxLength(500),
                        ]),
                    ]),
            ]);
    }
}