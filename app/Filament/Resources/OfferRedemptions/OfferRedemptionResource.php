<?php

namespace App\Filament\Resources\OfferRedemptions;

use App\Filament\Resources\OfferRedemptions\Pages\CreateOfferRedemption;
use App\Filament\Resources\OfferRedemptions\Pages\EditOfferRedemption;
use App\Filament\Resources\OfferRedemptions\Pages\ListOfferRedemptions;
use App\Filament\Resources\OfferRedemptions\Pages\ViewOfferRedemption;
use App\Filament\Resources\OfferRedemptions\Schemas\OfferRedemptionForm;
use App\Filament\Resources\OfferRedemptions\Schemas\OfferRedemptionInfolist;
use App\Filament\Resources\OfferRedemptions\Tables\OfferRedemptionsTable;
use App\Models\OfferRedemption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\BaseResource;


class OfferRedemptionResource extends BaseResource
{

public static function getNavigationBadge(): ?string
{
    $count = OfferRedemption::query()->count();
    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = OfferRedemption::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Offer-Operations';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function form(Schema $schema): Schema
    {
        return OfferRedemptionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OfferRedemptionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfferRedemptionsTable::configure($table);
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
            'index' => ListOfferRedemptions::route('/'),
            'create' => CreateOfferRedemption::route('/create'),
            'view' => ViewOfferRedemption::route('/{record}'),
            'edit' => EditOfferRedemption::route('/{record}/edit'),
        ];
    }
}
