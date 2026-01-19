<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 🔥 ضيف الحقول دي في الأول
                Select::make('restaurant_id')
                    ->relationship('restaurant', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Select::make('menu_section_id')
                    ->relationship('section', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live() // في Filament v4 بدل reactive
                    ->afterStateUpdated(function ($state, callable $set) {
                        // لما تختار section، املي الـ restaurant_id تلقائياً
                        if ($state) {
                            $section = \App\Models\MenuSection::find($state);
                            $set('restaurant_id', $section?->restaurant_id);
                        }
                    }),
                
                TextInput::make('name')
                    ->required(),
                    
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                    
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                    
                FileUpload::make('image')
                    ->image(),
                    
                Toggle::make('is_available')
                    ->required()
                    ->default(true),
                    
                Toggle::make('is_featured')
                    ->required()
                    ->default(false),
                    
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}