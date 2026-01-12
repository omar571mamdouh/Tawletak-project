<?php

namespace App\Filament\Resources\RestaurantBranches;

use App\Filament\Resources\RestaurantBranches\Pages\CreateRestaurantBranch;
use App\Filament\Resources\RestaurantBranches\Pages\EditRestaurantBranch;
use App\Filament\Resources\RestaurantBranches\Pages\ListRestaurantBranches;
use App\Filament\Resources\RestaurantBranches\Pages\ViewRestaurantBranch;
use App\Filament\Resources\RestaurantBranches\Schemas\RestaurantBranchForm;
use App\Filament\Resources\RestaurantBranches\Schemas\RestaurantBranchInfolist;
use App\Filament\Resources\RestaurantBranches\Tables\RestaurantBranchesTable;
use App\Models\RestaurantBranch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RestaurantBranchResource extends Resource
{



    protected static ?string $model = RestaurantBranch::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Restaurant-Operations';

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RestaurantBranchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RestaurantBranchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestaurantBranchesTable::configure($table);
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
            'index' => ListRestaurantBranches::route('/'),
            'create' => CreateRestaurantBranch::route('/create'),
            'view' => ViewRestaurantBranch::route('/{record}'),
            'edit' => EditRestaurantBranch::route('/{record}/edit'),
        ];
    }
}
