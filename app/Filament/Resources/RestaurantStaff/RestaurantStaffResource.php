<?php

namespace App\Filament\Resources\RestaurantStaff;

use App\Filament\Resources\RestaurantStaff\Pages\CreateRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Pages\EditRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Pages\ListRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Pages\ViewRestaurantStaff;
use App\Filament\Resources\RestaurantStaff\Schemas\RestaurantStaffForm;
use App\Filament\Resources\RestaurantStaff\Schemas\RestaurantStaffInfolist;
use App\Filament\Resources\RestaurantStaff\Tables\RestaurantStaffTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class RestaurantStaffResource extends Resource
{


public static function canViewAny(): bool
{
    // مين يشوف الـ resource في الـ sidebar والـ list؟
    return in_array(auth()->user()?->role, [
        'super_admin',
    ], true);
}

public static function canView(Model $record): bool
{
    // مين يفتح صفحة View لعنصر واحد؟
    return in_array(auth()->user()?->role, [
        'super_admin',
      
    ], true);
}

public static function canCreate(): bool
{
    // مين يقدر يعمل Create؟
    return in_array(auth()->user()?->role, [
        'super_admin',
      
    ], true);
}

public static function canEdit(Model $record): bool
{
    // مين يقدر يعمل Edit؟
    return in_array(auth()->user()?->role, [
        'super_admin',
       
    ], true);
}

public static function canDelete(Model $record): bool
{
    // مين يقدر يحذف Record واحد؟
    return in_array(auth()->user()?->role, [
        'super_admin',
       
    ], true);
}

public static function canDeleteAny(): bool
{
    // مين يقدر يعمل Bulk Delete؟
    return auth()->user()?->role === 'super_admin';
}

public static function getNavigationBadge(): ?string
{
    return (string) User::query()
        ->whereIn('role', ['owner', 'manager', 'staff'])
        ->count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->whereIn('role', ['owner', 'manager', 'staff']);
}
protected static ?string $model =  User::class;


protected static ?string $navigationLabel = 'Restaurant Staff';
protected static ?string $modelLabel = 'Staff Member';
protected static ?string $pluralModelLabel = 'Restaurant Staff';

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
