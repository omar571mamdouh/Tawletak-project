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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RestaurantStaffResource extends Resource
{
    protected static ?string $model = RestaurantStaff::class;

    protected static ?string $navigationLabel = 'Restaurant Staff';
    protected static ?string $modelLabel = 'Staff Member';
    protected static ?string $pluralModelLabel = 'Restaurant Staff';

    protected static string|\UnitEnum|null $navigationGroup = 'Restaurant-Operations';
    protected static ?int $navigationSort = 3;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    // ====== Authorization (admin users) ======
    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    // ====== Navigation badge ======
    public static function getNavigationBadge(): ?string
    {
        return (string) RestaurantStaff::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // ✅ مهم: شيلنا أي فلترة على عمود role (اتحذف من DB)
  public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->with(['restaurantRole']);
}


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
            'index'  => ListRestaurantStaff::route('/'),
            'create' => CreateRestaurantStaff::route('/create'),
            'view'   => ViewRestaurantStaff::route('/{record}'),
            'edit'   => EditRestaurantStaff::route('/{record}/edit'),
        ];
    }
}
