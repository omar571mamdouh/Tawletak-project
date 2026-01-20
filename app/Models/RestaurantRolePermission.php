<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantRolePermission extends Model
{
    protected $table = 'restaurant_role_permissions';

    protected $fillable = [
        'restaurant_role_id',
        'permission_id',
    ];
}
