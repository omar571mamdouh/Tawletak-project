<?php

namespace App\Filament\Resources\Offers;

use App\Filament\Resources\Offers\Pages\CreateOffer;
use App\Filament\Resources\Offers\Pages\EditOffer;
use App\Filament\Resources\Offers\Pages\ListOffers;
use App\Filament\Resources\Offers\Pages\ViewOffer;
use App\Filament\Resources\Offers\Schemas\OfferForm;
use App\Filament\Resources\Offers\Schemas\OfferInfolist;
use App\Filament\Resources\Offers\Tables\OffersTable;
use App\Models\Offer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\Offers\RelationManagers\RedemptionsRelationManager;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;
class OfferResource extends Resource
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
    $count = Offer::query()->count();
    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = Offer::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Offer-Operations';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return OfferForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OfferInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OffersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RedemptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffers::route('/'),
            'create' => CreateOffer::route('/create'),
            'view' => ViewOffer::route('/{record}'),
            'edit' => EditOffer::route('/{record}/edit'),
        ];
    }
}
