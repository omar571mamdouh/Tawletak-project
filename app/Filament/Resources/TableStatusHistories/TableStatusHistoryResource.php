<?php

namespace App\Filament\Resources\TableStatusHistories;

use App\Filament\Resources\TableStatusHistories\Pages\CreateTableStatusHistory;
use App\Filament\Resources\TableStatusHistories\Pages\EditTableStatusHistory;
use App\Filament\Resources\TableStatusHistories\Pages\ListTableStatusHistories;
use App\Filament\Resources\TableStatusHistories\Pages\ViewTableStatusHistory;
use App\Filament\Resources\TableStatusHistories\Schemas\TableStatusHistoryForm;
use App\Filament\Resources\TableStatusHistories\Schemas\TableStatusHistoryInfolist;
use App\Filament\Resources\TableStatusHistories\Tables\TableStatusHistoriesTable;
use App\Models\TableStatusHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\BaseResource;

class TableStatusHistoryResource extends BaseResource
{

public static function getNavigationBadge(): ?string
{
    return (string) TableStatusHistory::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}


    protected static ?string $model = TableStatusHistory::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Table-Operations';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return TableStatusHistoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TableStatusHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TableStatusHistoriesTable::configure($table);
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
            'index' => ListTableStatusHistories::route('/'),
            'create' => CreateTableStatusHistory::route('/create'),
            'view' => ViewTableStatusHistory::route('/{record}'),
            'edit' => EditTableStatusHistory::route('/{record}/edit'),
        ];
    }
}
