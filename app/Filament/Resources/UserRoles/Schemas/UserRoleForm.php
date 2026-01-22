<?php

namespace App\Filament\Resources\UserRoles\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class UserRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),

            TextInput::make('label')
                ->maxLength(150),

            Textarea::make('description'),

            // ✅ الربط اللي بيكتب في pivot: user_role_permissions
            Select::make('permissions')
                ->label('Permissions')
                ->relationship('permissions', 'name')
                ->multiple()
                ->preload()
                ->searchable(),
        ]);
    }
}
