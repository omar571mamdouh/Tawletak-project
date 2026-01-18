<?php

namespace App\Filament\Resources\LoyaltyTiers;

use App\Filament\Resources\LoyaltyTiers\Pages\CreateLoyaltyTier;
use App\Filament\Resources\LoyaltyTiers\Pages\EditLoyaltyTier;
use App\Filament\Resources\LoyaltyTiers\Pages\ListLoyaltyTiers;
use App\Filament\Resources\LoyaltyTiers\Pages\ViewLoyaltyTier;
use App\Filament\Resources\LoyaltyTiers\Schemas\LoyaltyTierForm;
use App\Filament\Resources\LoyaltyTiers\Schemas\LoyaltyTierInfolist;
use App\Filament\Resources\LoyaltyTiers\Tables\LoyaltyTiersTable;
use App\Models\LoyaltyTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTierResource extends Resource
{

public static function canViewAny(): bool
{
    return RG::isAny(['super_admin','owner',]);
}

public static function canView(Model $record): bool
{
    return RG::isAny(['super_admin','owner',]);
}

public static function canCreate(): bool
{
    return RG::isAny(['super_admin','owner',]);
}

public static function canEdit(Model $record): bool
{
    return RG::isAny(['super_admin','owner',]);
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
    return (string) LoyaltyTier::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = LoyaltyTier::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Tier-Operations';

    protected static ?int $navigationSort = 1;


    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LoyaltyTierForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LoyaltyTierInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyTiersTable::configure($table);
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
            'index' => ListLoyaltyTiers::route('/'),
            'create' => CreateLoyaltyTier::route('/create'),
            'view' => ViewLoyaltyTier::route('/{record}'),
            'edit' => EditLoyaltyTier::route('/{record}/edit'),
        ];
    }
}
