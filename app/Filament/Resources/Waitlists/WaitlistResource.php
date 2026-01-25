<?php

namespace App\Filament\Resources\Waitlists;

use App\Filament\Resources\Waitlists\Pages\CreateWaitlist;
use App\Filament\Resources\Waitlists\Pages\EditWaitlist;
use App\Filament\Resources\Waitlists\Pages\ListWaitlists;
use App\Filament\Resources\Waitlists\Pages\ViewWaitlist;
use App\Filament\Resources\Waitlists\Schemas\WaitlistForm;
use App\Filament\Resources\Waitlists\Schemas\WaitlistInfolist;
use App\Filament\Resources\Waitlists\Tables\WaitlistsTable;
use App\Models\Waitlist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\BaseResource;

class WaitlistResource extends BaseResource
{

    public static function getNavigationBadge(): ?string
{
    return (string) Waitlist::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = Waitlist::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Customer-Operations';

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return WaitlistForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WaitlistInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaitlistsTable::configure($table);
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
            'index' => ListWaitlists::route('/'),
            'create' => CreateWaitlist::route('/create'),
            'view' => ViewWaitlist::route('/{record}'),
            'edit' => EditWaitlist::route('/{record}/edit'),
        ];
    }
}
