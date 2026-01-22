<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;


class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                    
Select::make('roles')
    ->label('Roles')
    ->relationship('roles', 'name')
    ->multiple()
    ->preload()
    ->searchable(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->default(null),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->default(null),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
