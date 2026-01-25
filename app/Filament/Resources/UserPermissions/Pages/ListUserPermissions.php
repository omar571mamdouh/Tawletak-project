<?php

namespace App\Filament\Resources\UserPermissions\Pages;

use App\Filament\Resources\UserPermissions\UserPermissionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListUserPermissions extends ListRecords
{
    protected static string $resource = UserPermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('generatePermissions')
                ->label('Generate Permissions')
                ->icon('heroicon-o-bolt')
                ->requiresConfirmation()
                ->action(function () {
                    Artisan::call('db:seed', [
                        '--class' => 'Database\\Seeders\\FilamentPermissionsSeeder',
                        '--force' => true,
                    ]);

                    Notification::make()
                        ->title('Done')
                        ->body('Permissions generated successfully.')
                        ->success()
                        ->send();

                    // تحديث الجدول
                    $this->dispatch('$refresh');
                }),
        ];
    }
}
