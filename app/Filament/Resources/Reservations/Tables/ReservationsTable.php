<?php

namespace App\Filament\Resources\Reservations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;


class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->searchable(),
                TextColumn::make('table.id')
                    ->searchable(),
                TextColumn::make('party_size')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reservation_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expected_duration_minutes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                
                TextColumn::make('source')
                    ->badge(),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
               Action::make('confirm_now')
    ->label(__('Confirm'))
    ->icon('heroicon-o-check')
    ->color('success')
    ->visible(fn ($record) => blank($record->confirmed_at) && blank($record->cancelled_at))
    ->action(function ($record) {
        $record->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        Notification::make()->title(__('Confirmed'))->success()->send();
    }),

Action::make('seat_now')
    ->label(__('Seat'))
    ->icon('heroicon-o-user-group')
    ->color('warning')
    ->visible(fn ($record) => $record->status === 'confirmed' && blank($record->seated_at) && blank($record->cancelled_at))
    ->action(function ($record) {
        $record->update([
            'status' => 'seated',
            'seated_at' => now(),
        ]);

        Notification::make()->title(__('Seated'))->success()->send();
    }),

Action::make('complete_now')
    ->label(__('Complete'))
    ->icon('heroicon-o-flag')
    ->color('gray')
    ->visible(fn ($record) => $record->status === 'seated' && blank($record->completed_at) && blank($record->cancelled_at))
    ->action(function ($record) {
        $record->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Notification::make()->title(__('Completed'))->success()->send();
    }),

Action::make('cancel_now')
    ->label(__('Cancel'))
    ->icon('heroicon-o-x-mark')
    ->color('danger')
    ->requiresConfirmation()
    ->visible(fn ($record) => blank($record->cancelled_at) && $record->status !== 'completed')
    ->action(function ($record) {
        $record->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        Notification::make()->title(__('Cancelled'))->danger()->send();
    }),

            ])->actionsColumnLabel(__('Actions'))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
