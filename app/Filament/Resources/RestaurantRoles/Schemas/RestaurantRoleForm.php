<?php

namespace App\Filament\Resources\RestaurantRoles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RestaurantRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('restaurant_id')
                ->label('Restaurant')
                ->relationship('restaurant', 'name') // عدّل 'name' حسب اسم عمود اسم المطعم عندك
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('name')
                ->required()
                ->maxLength(50),

            Select::make('permissions')
                ->label('Permissions')
                ->multiple()
                ->relationship('permissions', 'key')
                ->preload()
                ->searchable()
                ->columnSpanFull(),
        ]);
    }
}
