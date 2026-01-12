<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    protected $fillable = [
        'branch_id',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'start_at',
        'end_at',
        'min_party_size',
        'eligible_loyalty_tier',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'start_at'       => 'datetime',
        'end_at'         => 'datetime',
        'min_party_size' => 'integer',
        'is_active'      => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class, 'offer_id');
    }
}
