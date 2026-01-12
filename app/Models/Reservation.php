<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    protected $fillable = [
        'customer_id',
        'branch_id',
        'table_id',
        'party_size',
        'reservation_time',
        'expected_duration_minutes',
        'status',
        'confirmed_at',
        'cancelled_at',
        'seated_at',
        'completed_at',
        'source',
    ];

    protected $casts = [
        'party_size'                => 'integer',
        'reservation_time'          => 'datetime',
        'expected_duration_minutes' => 'integer',
        'confirmed_at'              => 'datetime',
        'cancelled_at'              => 'datetime',
        'seated_at'                 => 'datetime',
        'completed_at'              => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(ReservationEvent::class, 'reservation_id');
    }

    public function offerRedemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class, 'reservation_id');
    }
}
