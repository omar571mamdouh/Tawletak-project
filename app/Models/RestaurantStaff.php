<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class RestaurantStaff extends Authenticatable
{
    use HasApiTokens, Notifiable;

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



public function roleAssignment()
{
    return $this->hasOne(
        RestaurantStaffRoleAssignment::class,
        'staff_id'
    );
}

public function role()
{
    return $this->hasOneThrough(
        RestaurantRole::class,
        RestaurantStaffRoleAssignment::class,
        'staff_id',              // FK في assignments
        'id',                    // PK في roles
        'id',                    // PK في staff
        'restaurant_role_id'     // FK في assignments
    );
}


public function permissions()
{
    return $this->role
        ? $this->role->permissions
        : collect();
}

public function hasPermission(string $permissionKey): bool
{
    return $this->permissions()
        ->contains('key', $permissionKey);
}

}
