<?php

namespace App\Filament\Resources\TableStatuses;

use App\Filament\Resources\TableStatuses\Pages\CreateTableStatus;
use App\Filament\Resources\TableStatuses\Pages\EditTableStatus;
use App\Filament\Resources\TableStatuses\Pages\ListTableStatuses;
use App\Filament\Resources\TableStatuses\Pages\ViewTableStatus;
use App\Filament\Resources\TableStatuses\Schemas\TableStatusForm;
use App\Filament\Resources\TableStatuses\Schemas\TableStatusInfolist;
use App\Filament\Resources\TableStatuses\Tables\TableStatusesTable;
use App\Models\TableStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TableStatusResource extends Resource
{


    protected static ?string $model = TableStatus::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Table-Operations';

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return TableStatusForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TableStatusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TableStatusesTable::configure($table);
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
            'index' => ListTableStatuses::route('/'),
            'create' => CreateTableStatus::route('/create'),
            'view' => ViewTableStatus::route('/{record}'),
            'edit' => EditTableStatus::route('/{record}/edit'),
        ];
    }
}
