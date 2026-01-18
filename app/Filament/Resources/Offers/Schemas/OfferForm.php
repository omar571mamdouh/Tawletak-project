<?php

namespace App\Filament\Resources\Offers\Schemas;

use App\Models\Restaurant;
use App\Models\RestaurantBranch;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /**
             * ✅ Restaurant first (UI only)
             * - Not stored in offers table
             * - In Edit: auto-filled from the selected branch
             */
            Select::make('restaurant_id')
                ->label(__('Restaurant'))
                ->options(fn () => Restaurant::query()->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->reactive()
                ->dehydrated(false) // 👈 لا يُحفظ في DB
                ->afterStateHydrated(function (callable $set, callable $get) {
                    // في حالة Edit: لو branch_id موجود، هات restaurant_id بتاعها
                    $branchId = $get('branch_id');
                    if (! $branchId) {
                        return;
                    }

                    $restaurantId = RestaurantBranch::whereKey($branchId)->value('restaurant_id');
                    if ($restaurantId) {
                        $set('restaurant_id', $restaurantId);
                    }
                })
                ->afterStateUpdated(function (callable $set) {
                    // لما المطعم يتغير: امسح البرانش المختارة
                    $set('branch_id', null);
                }),

            /**
             * ✅ Branch depends on restaurant_id
             * - Disabled until restaurant selected
             * - Shows only branches of selected restaurant
             */
            Select::make('branch_id')
                ->label(__('Branch'))
                ->options(function (callable $get) {
                    $restaurantId = $get('restaurant_id');
                    if (! $restaurantId) {
                        return [];
                    }

                    return RestaurantBranch::query()
                        ->where('restaurant_id', $restaurantId)
                        ->orderBy('name')
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->required()
                ->disabled(function (callable $get) {
                    $restaurantId = $get('restaurant_id');
                    if (! $restaurantId) {
                        return true;
                    }

                    return ! RestaurantBranch::where('restaurant_id', $restaurantId)->exists();
                })
                ->helperText(function (callable $get) {
                    $restaurantId = $get('restaurant_id');
                    if (! $restaurantId) {
                        return __('Select a restaurant first.');
                    }

                    $hasBranches = RestaurantBranch::where('restaurant_id', $restaurantId)->exists();
                    return $hasBranches ? null : __('No branches found for this restaurant.');
                }),

            TextInput::make('title')
                ->required()
                ->maxLength(200),

            Textarea::make('description')
                ->columnSpanFull()
                // ✅ في perk لازم وصف واضح
                ->required(fn (callable $get) => $get('discount_type') === 'perk')
                ->helperText(fn (callable $get) => $get('discount_type') === 'perk'
                    ? __('Describe the perk (e.g., Free dessert, Free drink, Priority seating).')
                    : null
                )
                ->default(null),

            /**
             * ✅ Discount type
             */
            Select::make('discount_type')
                ->options([
                    'percent' => __('Percent (%)'),
                    'fixed'   => __('Fixed Amount'),
                    'perk'    => __('Perk (Free / Special)'),
                ])
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set, callable $get) {
                    // لما النوع يتغير لـ perk امسح discount_value (هتبقى nullable في DB)
                    if ($get('discount_type') === 'perk') {
                        $set('discount_value', null);
                    }
                }),

            /**
             * ✅ Discount value
             * - percent: 1..100
             * - fixed  : > 0
             * - perk   : hidden (stored as null)
             */
            TextInput::make('discount_value')
                ->label(fn (callable $get) =>
                    $get('discount_type') === 'percent'
                        ? __('Discount (%)')
                        : __('Discount Amount')
                )
                ->numeric()
                ->hidden(fn (callable $get) => $get('discount_type') === 'perk')
                ->required(fn (callable $get) => in_array($get('discount_type'), ['percent', 'fixed'], true))
                ->minValue(fn (callable $get) => $get('discount_type') === 'percent' ? 1 : 0.01)
                ->maxValue(fn (callable $get) => $get('discount_type') === 'percent' ? 100 : null),

            DateTimePicker::make('start_at')->required(),
            DateTimePicker::make('end_at')->required(),

            TextInput::make('min_party_size')
                ->numeric()
                ->default(null),

            /**
             * ✅ eligible_loyalty_tier
             * IMPORTANT: this expects DB column to be STRING nullable (NOT enum)
             */
            Select::make('eligible_loyalty_tier')
                ->options([
                    'Bronze'   => 'Bronze',
                    'Silver'   => 'Silver',
                    'Gold'     => 'Gold',
                    'Platinum' => 'Platinum',
                    'Diamond'  => 'Diamond',
                ])
                ->nullable()
                ->default(null),

            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ]);
    }
}
