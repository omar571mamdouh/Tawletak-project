<?php

namespace App\Filament\Resources\UserPermissions;

use App\Filament\Resources\UserPermissions\Pages\CreateUserPermission;
use App\Filament\Resources\UserPermissions\Pages\EditUserPermission;
use App\Filament\Resources\UserPermissions\Pages\ListUserPermissions;
use App\Filament\Resources\UserPermissions\Pages\ViewUserPermission;
use App\Filament\Resources\UserPermissions\Schemas\UserPermissionForm;
use App\Filament\Resources\UserPermissions\Schemas\UserPermissionInfolist;
use App\Filament\Resources\UserPermissions\Tables\UserPermissionsTable;
use App\Models\UserPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserPermissionResource extends Resource
{

public static function getNavigationBadge(): ?string
{
    return (string) UserPermission::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = UserPermission::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Admin-Operations';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserPermissionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserPermissionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserPermissionsTable::configure($table);
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
            'index' => ListUserPermissions::route('/'),
            'create' => CreateUserPermission::route('/create'),
            'view' => ViewUserPermission::route('/{record}'),
            'edit' => EditUserPermission::route('/{record}/edit'),
        ];
    }
}
