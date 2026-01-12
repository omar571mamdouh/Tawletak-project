<?php

namespace App\Filament\Resources\RestaurantStaff;

use App\Filament\Resources\RestaurantStaff\Pages\CreateRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Pages\EditRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Pages\ListRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Pages\ViewRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Schemas\RestaurantStaffForm;
use App\Filament\Resources\RestaurantStaff\Schemas\RestaurantStaffInfolist;
use App\Filament\Resources\RestaurantStaff\Tables\RestaurantStaffTable;
use App\Models\RestaurantStaff;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RestaurantStaffResource extends Resource
{
    protected static ?string $model = RestaurantStaff::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Restaurant-Operations';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RestaurantStaffForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RestaurantStaffInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestaurantStaffTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestaurantStaff::route('/'),
            'create' => CreateRestaurantStaff::route('/create'),
            'view' => ViewRestaurantStaff::route('/{record}'),
            'edit' => EditRestaurantStaff::route('/{record}/edit'),
        ];
    }
}
