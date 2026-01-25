<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use App\Models\UserPermission; // ← عدّل لو اسم الموديل مختلف

class FilamentPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $actions = ['viewAny', 'view', 'create', 'update', 'delete'];

        foreach (Filament::getResources() as $resourceClass) {

            if (! method_exists($resourceClass, 'getSlug')) {
                continue;
            }

            // نفس منطق BaseResource
            $resourceKey = str($resourceClass::getSlug())
                ->replace('/', '.')
                ->toString();

            $moduleLabel = Str::headline($resourceKey);

            foreach ($actions as $action) {
                $name = "{$resourceKey}.{$action}";   // users.viewAny

                UserPermission::updateOrCreate(
                    ['name' => $name],
                    [
                        'label'  => "{$moduleLabel} - " . Str::headline($action),
                        'module' => $resourceKey,
                    ]
                );
            }
        }
    }
}
