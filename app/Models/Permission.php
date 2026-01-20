<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'key',
        'group',
    ];

    public function roles()
    {
        return $this->belongsToMany(
            RestaurantRole::class,
            'restaurant_role_permissions',
            'permission_id',
            'restaurant_role_id'
        );
    }
}
