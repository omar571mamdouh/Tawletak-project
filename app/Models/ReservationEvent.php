<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class ReservationEvent extends Model
{

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('reservations-events') 
        ->logOnly(['reservation_id', 'event_time','event_type'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}

    protected $fillable = [
        'reservation_id',
        'event_type',
        'event_time',
        'actor_type',
        'actor_id',
        'meta_json',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'meta_json'  => 'array',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Polymorphic actor (Customer / RestaurantStaff / User / System)
     *
     * Note: Because your DB stores actor_type as an ENUM (customer/staff/admin/system),
     * this morph needs a custom morph map to resolve to model classes.
     */
    public function actor(): MorphTo
    {
        return $this->morphTo(null, 'actor_type', 'actor_id');
    }
}
