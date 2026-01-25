<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CustomerLoyalty;
use App\Models\LoyaltyTier;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Offer extends Model
{

use LogsActivity;

  public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('Offer') 
        ->logAll()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
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

    // App\Models\Offer.php

public function restaurantId(): ?int
{
    return $this->branch?->restaurant_id;
}

public function isEligibleForCustomer(int $customerId): bool
{
    // لو الأوفر مفتوح لأي حد
    if (blank($this->eligible_loyalty_tier)) {
        return true;
    }

    $restaurantId = $this->branch?->restaurant_id;
    if (! $restaurantId) {
        return false;
    }

    $loyalty = CustomerLoyalty::query()
        ->where('customer_id', $customerId)
        ->where('restaurant_id', $restaurantId)
        ->with('tier')
        ->first();

    if (! $loyalty || ! $loyalty->tier) {
        return false;
    }

    $requiredTier = LoyaltyTier::where('name', $this->eligible_loyalty_tier)->first();
    if (! $requiredTier) {
        return false;
    }

    return (int) $loyalty->tier->min_visits >= (int) $requiredTier->min_visits;
}

}
