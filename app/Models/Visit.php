<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    protected $fillable = [
        'customer_id',
        'branch_id',
        'reservation_id',
        'table_id',
        'seated_at',
        'left_at',
        'bill_amount',
        'status',
    ];

    protected $casts = [
        'seated_at'   => 'datetime',
        'left_at'     => 'datetime',
        'bill_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    /**
     * Offer redemptions linked to this visit (optional).
     */
    public function offerRedemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class, 'visit_id');
    }
}
