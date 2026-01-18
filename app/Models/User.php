<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
    'name',
    'email',
    'phone',
    'password',
    'role',
    'restaurant_id',
    'branch_id',
    'is_active',
];
public function canAccessPanel(\Filament\Panel $panel): bool
{
    return $this->is_active && in_array($this->role, [
        'super_admin',
        'owner',
        'manager',
        'staff',
    ]);
}


    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'password'  => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Filament access control.
     * Allow only active super admins to access the admin panel.
     */
   

    public function restaurant(): BelongsTo
{
    return $this->belongsTo(\App\Models\Restaurant::class, 'restaurant_id');
}

public function branch(): BelongsTo
{
    return $this->belongsTo(\App\Models\RestaurantBranch::class, 'branch_id');
}
}
