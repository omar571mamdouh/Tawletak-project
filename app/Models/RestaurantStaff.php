<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class RestaurantStaff extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'restaurant_staff';

    protected $fillable = [
        'restaurant_id',
        'branch_id',
        'name',
        'phone',
        'email',
        'password_hash',
        'role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'password_hash' => 'hashed',
    ];

    protected $hidden = [
    'password_hash',
];


    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }

    public function tableStatusChanges(): HasMany
    {
        return $this->hasMany(TableStatusHistory::class, 'changed_by_staff_id');
    }

    public function getAuthPassword(): string
{
    return (string) $this->password_hash;
}

}
