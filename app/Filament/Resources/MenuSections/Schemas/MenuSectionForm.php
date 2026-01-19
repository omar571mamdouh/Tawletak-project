<?php

namespace App\Filament\Resources\MenuSections\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuSectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
              Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required(),

            TextInput::make('name')
                ->required()
                ->maxLength(100),

            TextInput::make('sort_order')
                ->numeric()
                ->default(0),

            Toggle::make('is_active')
                ->default(true),
        ]);
    }
}
