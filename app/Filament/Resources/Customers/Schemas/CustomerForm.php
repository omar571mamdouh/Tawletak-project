<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Customer Information'))
                    ->description(__('Basic details about the customer'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label(__('Name'))
                                ->placeholder(__('Enter customer name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label(__('Phone'))
                                ->placeholder('+20 123 456 7890')
                                ->tel()
                                ->required()
                                ->maxLength(20)
                                ->unique(ignoreRecord: true),
                        ]),
                    ]),

                Section::make(__('Contact & Status'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label(__('Email'))
                                ->placeholder('example@domain.com')
                                ->email()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->nullable(),

                            Toggle::make('is_active')
                                ->label(__('Active'))
                                ->default(true),
                        ]),
                    ]),
            ]);
    }
}