<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'name', 
        'phone', 
        'email', 
        'password', 
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password' => 'hashed', // ✅ Laravel 11+ automatic hashing
    ];

    protected $hidden = [
        'password',
        'remember_token', // ✅ أضف ده كمان
    ];

    // Relations...
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function offerRedemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class);
    }

    public function loyalties(): HasMany
    {
        return $this->hasMany(CustomerLoyalty::class);
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }
}