<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableStatusHistory extends Model
{
    protected $table = 'table_status_history';

    protected $fillable = [
        'table_id',
        'changed_by_staff_id',
        'old_status',
        'new_status',
        'timestamp',
        'note',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function changedByStaff(): BelongsTo
    {
        return $this->belongsTo(RestaurantStaff::class, 'changed_by_staff_id');
    }
}
