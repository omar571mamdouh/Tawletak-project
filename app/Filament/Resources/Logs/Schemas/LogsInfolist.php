<?php

namespace App\Filament\Resources\Logs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LogsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id')
                ->label('ID'),

            TextEntry::make('log_name')
                ->label('Log')
                ->badge(),

            TextEntry::make('description')
                ->label('Action')
                ->columnSpanFull(),

            TextEntry::make('event')
                ->label('Event')
                ->badge()
                ->color(fn (?string $state) => match ($state) {
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    default => 'gray',
                }),

            TextEntry::make('causer.name')
                ->label('By')
                ->default('System'),

            TextEntry::make('subject_type')
                ->label('Model')
                ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '-'),

            TextEntry::make('subject_id')
                ->label('Model ID'),

            TextEntry::make('created_at')
                ->label('Date')
                ->dateTime(),

            // properties (Spatie Activitylog)
            TextEntry::make('properties')
                ->label('Properties')
                ->formatStateUsing(function ($state) {
                    if (blank($state)) return '-';

                    // state غالباً array/json من Spatie
                    $value = is_string($state) ? $state : json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                    return $value;
                })
                ->copyable()
                ->columnSpanFull(),
        ]);
    }
}
