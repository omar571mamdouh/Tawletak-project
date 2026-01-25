<?php

namespace App\Filament\Resources\Logs;

use App\Filament\Resources\Logs\Pages\CreateLogs;
use App\Filament\Resources\Logs\Pages\EditLogs;
use App\Filament\Resources\Logs\Pages\ListLogs;
use App\Filament\Resources\Logs\Pages\ViewLogs;
use App\Filament\Resources\Logs\Schemas\LogsForm;
use App\Filament\Resources\Logs\Schemas\LogsInfolist;
use App\Filament\Resources\Logs\Tables\LogsTable;
use App\Models\Logs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class LogsResource extends Resource
{
      public static function getNavigationBadge(): ?string
{
    return (string) Activity::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
   
    protected static ?string $model = Activity::class;

     protected static string|\UnitEnum|null $navigationGroup = 'Logs-Operations';

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return LogsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LogsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogsTable::configure($table);
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
            'index' => ListLogs::route('/'),
            'view' => ViewLogs::route('/{record}'),
        ];
    }
}
