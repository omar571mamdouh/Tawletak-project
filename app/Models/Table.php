<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'table_code',
        'capacity',
        'location_tag',
        'is_active',
    ];

    protected $casts = [
        'capacity'  => 'integer',
        'is_active' => 'boolean',
    ];

    protected $with = ['status'];


    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }

    /**
     * Current real-time status row (one row per table).
     */
    public function status(): HasOne
    {
        return $this->hasOne(TableStatus::class, 'table_id', 'id');
    }

    /**
     * Audit log history of status changes.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(TableStatusHistory::class, 'table_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'table_id');
    }

    public function restaurant(): BelongsTo
{
    return $this->belongsTo(Restaurant::class, 'restaurant_id');
}
}
