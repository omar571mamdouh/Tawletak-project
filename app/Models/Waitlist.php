<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    protected $fillable = [
        'customer_id',
        'branch_id',
        'party_size',
        'status',
        'estimated_wait_minutes',
        'notified_at',
        'seated_at',
    ];

    protected $casts = [
        'party_size'             => 'integer',
        'estimated_wait_minutes' => 'integer',
        'notified_at'            => 'datetime',
        'seated_at'              => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }
}
