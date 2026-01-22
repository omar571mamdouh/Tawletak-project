<?php

namespace App\Filament\Resources\RestaurantStaff\Pages;

use App\Filament\Resources\RestaurantStaff\RestaurantStaffResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantStaff extends EditRecord
{
    protected static string $resource = RestaurantStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }


    protected function mutateFormDataBeforeFill(array $data): array
{
    $data['restaurant_role_id'] = $this->record->roleAssignment?->restaurant_role_id;
    return $data;
}

protected function afterSave(): void
{
    $roleId = $this->data['restaurant_role_id'] ?? null;

    $this->record->roleAssignment()->updateOrCreate(
        ['staff_id' => $this->record->id],
        ['restaurant_role_id' => $roleId]
    );
}

}
