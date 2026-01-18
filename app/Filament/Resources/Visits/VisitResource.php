<?php

namespace App\Filament\Resources\Visits;

use App\Filament\Resources\Visits\Pages\CreateVisit;
use App\Filament\Resources\Visits\Pages\EditVisit;
use App\Filament\Resources\Visits\Pages\ListVisits;
use App\Filament\Resources\Visits\Pages\ViewVisit;
use App\Filament\Resources\Visits\Schemas\VisitForm;
use App\Filament\Resources\Visits\Schemas\VisitInfolist;
use App\Filament\Resources\Visits\Tables\VisitsTable;
use App\Models\Visit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;

class VisitResource extends Resource
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
    return (string) Visit::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = Visit::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Customer-Operations';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    public static function form(Schema $schema): Schema
    {
        return VisitForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VisitInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitsTable::configure($table);
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
            'index' => ListVisits::route('/'),
            'create' => CreateVisit::route('/create'),
            'view' => ViewVisit::route('/{record}'),
            'edit' => EditVisit::route('/{record}/edit'),
        ];
    }
}
