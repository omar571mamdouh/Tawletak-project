<?php

namespace App\Filament\Resources\Tables;

use App\Filament\Resources\Tables\Pages\CreateTable;
use App\Filament\Resources\Tables\Pages\EditTable;
use App\Filament\Resources\Tables\Pages\ListTables;
use App\Filament\Resources\Tables\Pages\ViewTable;
use App\Filament\Resources\Tables\Schemas\TableForm;
use App\Filament\Resources\Tables\Schemas\TableInfolist;
use App\Filament\Resources\Tables\Tables\TablesTable;
use App\Models\Table as Table1;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Tables\RelationManagers\StatusRelationManager;
use App\Filament\Resources\Tables\RelationManagers\StatusHistoryRelationManager;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;


class TableResource extends Resource
{

public static function canViewAny(): bool
{
    return RG::isAny(['super_admin','owner','manager','staff']);
}

public static function canView(Model $record): bool
{
    return RG::isAny(['super_admin','owner','manager','staff']);
}

public static function canCreate(): bool
{
    return RG::isAny(['super_admin','owner','manager']);
}

public static function canEdit(Model $record): bool
{
    return RG::isAny(['super_admin','owner','manager']);
}

public static function canDelete(Model $record): bool
{
    return RG::isAny(['super_admin','owner']);
}

public static function canDeleteAny(): bool
{
    return RG::role() === 'super_admin';
}

public static function getNavigationBadge(): ?string
{
    return (string) Table1::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = Table1::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Table-Operations';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $recordTitleAttribute = 'table_code';

    public static function form(Schema $schema): Schema
    {
        return TableForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TableInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TablesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
           StatusRelationManager::class,
           StatusHistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTables::route('/'),
            'create' => CreateTable::route('/create'),
            'view' => ViewTable::route('/{record}'),
            'edit' => EditTable::route('/{record}/edit'),
        ];
    }
}
