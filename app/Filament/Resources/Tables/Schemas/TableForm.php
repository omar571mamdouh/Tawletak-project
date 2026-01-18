<?php

namespace App\Filament\Resources\Tables\Schemas;

use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Table Information'))
                    ->description(__('Basic details about the table'))
                    ->schema([
                        Grid::make(2)->schema([

                            // ✅ Restaurant first (UI only - not saved)
                            Select::make('restaurant_id')
                                ->label(__('Restaurant'))
                                ->options(fn () => Restaurant::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->dehydrated(false) // 👈 مش هيتخزن في DB
                                ->afterStateHydrated(function (callable $set, callable $get) {
                                    // ✅ في الـ Edit: جبلي restaurant_id من branch_id
                                    $branchId = $get('branch_id');
                                    if (! $branchId) return;

                                    $restaurantId = RestaurantBranch::whereKey($branchId)->value('restaurant_id');
                                    if ($restaurantId) {
                                        $set('restaurant_id', $restaurantId);
                                    }
                                })
                                ->afterStateUpdated(function (callable $set) {
                                    // لما المطعم يتغير امسح البرانش
                                    $set('branch_id', null);
                                }),

                            // ✅ Branch filtered by selected restaurant
                            Select::make('branch_id')
                                ->label(__('Branch'))
                                ->options(function (callable $get) {
                                    $restaurantId = $get('restaurant_id');
                                    if (! $restaurantId) return [];

                                    return RestaurantBranch::query()
                                        ->where('restaurant_id', $restaurantId)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->placeholder(__('Select branch'))
                                ->disabled(function (callable $get) {
                                    $restaurantId = $get('restaurant_id');
                                    if (! $restaurantId) return true;

                                    return ! RestaurantBranch::where('restaurant_id', $restaurantId)->exists();
                                })
                                ->helperText(function (callable $get) {
                                    $restaurantId = $get('restaurant_id');
                                    if (! $restaurantId) return __('Select a restaurant first.');

                                    $hasBranches = RestaurantBranch::where('restaurant_id', $restaurantId)->exists();
                                    return $hasBranches ? null : __('No branches found for this restaurant.');
                                }),

                            TextInput::make('table_code')
                                ->label(__('Table Code'))
                                ->placeholder('T001, A-12...')
                                ->required()
                                ->maxLength(50),
                        ]),
                    ]),

                Section::make(__('Table Details'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('capacity')
                                ->label(__('Capacity'))
                                ->placeholder('2, 4, 6...')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100),

                            Select::make('location_tag')
                                ->label(__('Location'))
                                ->options([
                                    'indoor' => __('Indoor'),
                                    'outdoor' => __('Outdoor'),
                                    'vip' => __('VIP'),
                                ])
                                ->placeholder(__('Select location'))
                                ->nullable(),
                        ]),
                    ]),

                Section::make(__('Status'))
                    ->schema([
                        Toggle::make('is_active')
                            ->label(__('Active'))
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
