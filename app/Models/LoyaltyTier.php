<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyTier extends Model
{
    protected $table = 'loyalty_tiers';

    protected $fillable = [
        'name',
        'min_visits',
        'benefits_json',
    ];

    protected $casts = [
        'benefits_json' => 'array',
    ];

    /**
     * Customers assigned to this tier.
     */
    public function customerLoyalties(): HasMany
    {
        return $this->hasMany(CustomerLoyalty::class, 'tier_id');
    }
}
