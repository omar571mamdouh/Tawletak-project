<?php

namespace App\Filament\Resources\ReservationEvents;

use App\Filament\Resources\ReservationEvents\Pages\CreateReservationEvent;
use App\Filament\Resources\ReservationEvents\Pages\EditReservationEvent;
use App\Filament\Resources\ReservationEvents\Pages\ListReservationEvents;
use App\Filament\Resources\ReservationEvents\Pages\ViewReservationEvent;
use App\Filament\Resources\ReservationEvents\Schemas\ReservationEventForm;
use App\Filament\Resources\ReservationEvents\Schemas\ReservationEventInfolist;
use App\Filament\Resources\ReservationEvents\Tables\ReservationEventsTable;
use App\Models\ReservationEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;
class ReservationEventResource extends Resource
{   

public static function canViewAny(): bool
{
    return RG::isAny(['super_admin','owner','manager']);
}

public static function canView(Model $record): bool
{
    return RG::isAny(['super_admin','owner','manager']);
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
    $count = ReservationEvent::query()->count();
    return $count > 0 ? (string) $count : null;
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}

    protected static ?string $model = ReservationEvent::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Reservation-Operations';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $recordTitleAttribute = 'event_type';

    public static function form(Schema $schema): Schema
    {
        return ReservationEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReservationEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservationEventsTable::configure($table);
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
            'index' => ListReservationEvents::route('/'),
            'create' => CreateReservationEvent::route('/create'),
            'view' => ViewReservationEvent::route('/{record}'),
            'edit' => EditReservationEvent::route('/{record}/edit'),
        ];
    }
}
