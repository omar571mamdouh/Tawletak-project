<?php

namespace App\Filament\Resources\RestaurantStaff\Schemas;

use App\Models\RestaurantRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class RestaurantStaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('restaurant_id')
                ->label('Restaurant')
                ->relationship('restaurant', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->live(),

            Select::make('branch_id')
                ->label('Branch')
                ->relationship(
                    name: 'branch',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn ($query, callable $get) =>
                        $query->when(
                            $get('restaurant_id'),
                            fn ($q, $rid) => $q->where('restaurant_id', $rid)
                        )
                )
                ->searchable()
                ->preload()
                ->nullable()
                ->live(),

            TextInput::make('name')
                ->required()
                ->maxLength(200),

            TextInput::make('phone')
                ->tel()
                ->maxLength(50)
                ->nullable(),

            TextInput::make('email')
                ->label('Email address')
                ->email()
                ->maxLength(200)
                ->nullable()
                // ✅ unique على جدول restaurant_staff
                ->unique(table: 'restaurant_staff', ignorable: fn ($record) => $record),

            // ✅ يحفظ في restaurant_staff.password_hash (مش users.password)
            TextInput::make('password_hash')
                ->label('Password')
                ->password()
                ->required(fn (string $context) => $context === 'create')
                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                ->dehydrated(fn ($state) => filled($state)),

            // ✅ ده حقل UI مش موجود في staff table — بيتحفظ في pivot assignments في صفحات Create/Edit
            Select::make('restaurant_role_id')
                ->label('Role')
                ->required()
                ->searchable()
                ->preload()
                ->options(function (callable $get) {
                    $restaurantId = $get('restaurant_id');

                    return RestaurantRole::query()
                        ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->live(),

            Toggle::make('is_active')
                ->required()
                ->default(true),
        ]);
    }
}
