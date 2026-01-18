<?php

namespace App\Filament\Resources\RestaurantStaff\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

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
                    ->required()
                    ->unique(ignoreRecord: true),

                // users.password بدل restaurant_staff.password_hash
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => filled($state)),

                Select::make('role')
                    ->options([
                        'owner' => 'Owner',
                        'manager' => 'Manager',
                        'staff' => 'Staff',
                    ])
                    ->required(),

                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
