<?php

namespace App\Observers;

use App\Models\TableStatus;
use App\Models\TableStatusHistory;
use Illuminate\Support\Facades\Auth;


class TableStatusObserver
{
    public function updating(TableStatus $status): void
    {
        // سجل فقط لو قيمة status اتغيرت
        if (! $status->isDirty('status')) {
            return;
        }

        TableStatusHistory::create([
            'table_id'            => $status->table_id,
            'changed_by_user_id' => Auth::id(), // لحد ما نربط staff auth
            'old_status'          => $status->getOriginal('status'),
            'new_status'          => $status->status,
            'timestamp'           => now(),
            'note'                => null,
        ]);
    }
}
