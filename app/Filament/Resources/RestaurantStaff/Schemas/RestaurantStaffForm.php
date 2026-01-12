<?php

namespace App\Filament\Resources\RestaurantStaff\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RestaurantStaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->default(null),
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('password_hash')
                    ->password()
                    ->default(null),
                Select::make('role')
                    ->options(['owner' => 'Owner', 'manager' => 'Manager', 'host' => 'Host', 'staff' => 'Staff'])
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
