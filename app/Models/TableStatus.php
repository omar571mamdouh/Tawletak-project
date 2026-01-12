<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableStatus extends Model
{
    protected $table = 'table_status';

    /**
     * Primary key is table_id (not id).
     */
    protected $primaryKey = 'table_id';
    public $incrementing = false;
    protected $keyType = 'int';

    /**
     * We only have updated_at (no created_at) in this table.
     */
    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'table_id',
        'status',
        'current_reservation_id',
        'occupied_since',
        'estimated_free_at',
        'updated_at',
    ];

    protected $casts = [
        'occupied_since'    => 'datetime',
        'estimated_free_at' => 'datetime',
        'updated_at'        => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function currentReservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'current_reservation_id');
    }

    public function history(): HasMany
{
    return $this->hasMany(TableStatusHistory::class, 'table_id', 'table_id');
}
}
