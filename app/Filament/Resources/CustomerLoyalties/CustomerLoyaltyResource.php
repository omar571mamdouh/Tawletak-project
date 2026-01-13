<?php

namespace App\Filament\Resources\CustomerLoyalties;

use App\Filament\Resources\CustomerLoyalties\Pages\CreateCustomerLoyalty;
use App\Filament\Resources\CustomerLoyalties\Pages\EditCustomerLoyalty;
use App\Filament\Resources\CustomerLoyalties\Pages\ListCustomerLoyalties;
use App\Filament\Resources\CustomerLoyalties\Pages\ViewCustomerLoyalty;
use App\Filament\Resources\CustomerLoyalties\Schemas\CustomerLoyaltyForm;
use App\Filament\Resources\CustomerLoyalties\Schemas\CustomerLoyaltyInfolist;
use App\Filament\Resources\CustomerLoyalties\Tables\CustomerLoyaltiesTable;
use App\Models\CustomerLoyalty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerLoyaltyResource extends Resource
{
    protected static ?string $model = CustomerLoyalty::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Customer-Operations';

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function form(Schema $schema): Schema
    {
        return CustomerLoyaltyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerLoyaltyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerLoyaltiesTable::configure($table);
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
            'index' => ListCustomerLoyalties::route('/'),
            'create' => CreateCustomerLoyalty::route('/create'),
            'view' => ViewCustomerLoyalty::route('/{record}'),
            'edit' => EditCustomerLoyalty::route('/{record}/edit'),
        ];
    }
}
