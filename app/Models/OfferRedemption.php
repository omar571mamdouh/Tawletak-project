<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OfferRedemption extends Model
{

use LogsActivity;

  public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('Offer-redemption') 
        ->logAll()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
    protected $fillable = [
        'offer_id',
        'customer_id',
        'reservation_id',
        'visit_id',
        'redeemed_at',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'offer_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id');
    }
}
