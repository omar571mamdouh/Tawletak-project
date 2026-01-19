<?php

namespace App\Filament\Resources\MenuSections;

use App\Filament\Resources\MenuSections\Pages\CreateMenuSection;
use App\Filament\Resources\MenuSections\Pages\EditMenuSection;
use App\Filament\Resources\MenuSections\Pages\ListMenuSections;
use App\Filament\Resources\MenuSections\Pages\ViewMenuSection;
use App\Filament\Resources\MenuSections\Schemas\MenuSectionForm;
use App\Filament\Resources\MenuSections\Schemas\MenuSectionInfolist;
use App\Filament\Resources\MenuSections\Tables\MenuSectionsTable;
use App\Models\MenuSection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\MenuSections\RelationManagers\ItemsRelationManager;

class MenuSectionResource extends Resource
{

public static function getNavigationBadge(): ?string
{
    return (string) MenuSection::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = MenuSection::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Menu-Operations';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MenuSectionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MenuSectionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenuSectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuSections::route('/'),
            'create' => CreateMenuSection::route('/create'),
            'view' => ViewMenuSection::route('/{record}'),
            'edit' => EditMenuSection::route('/{record}/edit'),
        ];
    }
}
