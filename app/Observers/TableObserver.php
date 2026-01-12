<?php

namespace App\Observers;

use App\Models\Table;

class TableObserver
{
    public function created(Table $table): void
    {
        // لو status already موجود لأي سبب، متعمليش duplicate
        if ($table->status()->exists()) {
            return;
        }

        $table->status()->create([
            'status' => 'available',
            // باقي الحقول nullable فمش محتاجين نحطها
        ]);
    }
}
