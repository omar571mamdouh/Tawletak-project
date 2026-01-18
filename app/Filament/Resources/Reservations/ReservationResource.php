<?php

namespace App\Filament\Resources\Reservations;

use App\Filament\Resources\Reservations\Pages\CreateReservation;
use App\Filament\Resources\Reservations\Pages\EditReservation;
use App\Filament\Resources\Reservations\Pages\ListReservations;
use App\Filament\Resources\Reservations\Pages\ViewReservation;
use App\Filament\Resources\Reservations\Schemas\ReservationForm;
use App\Filament\Resources\Reservations\Schemas\ReservationInfolist;
use App\Filament\Resources\Reservations\Tables\ReservationsTable;
use App\Models\Reservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\reservations\RelationManagers\EventsRelationManager;
use App\Filament\Support\RoleGate as RG;
use Illuminate\Database\Eloquent\Model;
class ReservationResource extends Resource
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
    return (string) Reservation::count();
}

public static function getNavigationBadgeColor(): ?string
{
    return 'success';
}
    protected static ?string $model = Reservation::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Reservation-Operations';

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ReservationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReservationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReservationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'view' => ViewReservation::route('/{record}'),
            'edit' => EditReservation::route('/{record}/edit'),
        ];
    }
}
