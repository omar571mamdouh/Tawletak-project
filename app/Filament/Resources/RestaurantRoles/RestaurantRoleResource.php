<?php

namespace App\Filament\Resources\RestaurantRoles;

use App\Filament\Resources\RestaurantRoles\Pages\CreateRestaurantRole;
use App\Filament\Resources\RestaurantRoles\Pages\EditRestaurantRole;
use App\Filament\Resources\RestaurantRoles\Pages\ListRestaurantRoles;
use App\Filament\Resources\RestaurantRoles\Pages\ViewRestaurantRole;
use App\Filament\Resources\RestaurantRoles\Schemas\RestaurantRoleForm;
use App\Filament\Resources\RestaurantRoles\Schemas\RestaurantRoleInfolist;
use App\Filament\Resources\RestaurantRoles\Tables\RestaurantRolesTable;
use App\Models\RestaurantRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\BaseResource;


class RestaurantRoleResource extends BaseResource
{
    
public static function getNavigationBadge(): ?string
{
    return (string) RestaurantRole::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';


}
    protected static ?string $model = RestaurantRole::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Roles & Permissions-Operations';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
   
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RestaurantRoleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RestaurantRoleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestaurantRolesTable::configure($table);
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
            'index' => ListRestaurantRoles::route('/'),
            'create' => CreateRestaurantRole::route('/create'),
            'view' => ViewRestaurantRole::route('/{record}'),
            'edit' => EditRestaurantRole::route('/{record}/edit'),
        ];
    }
}
