<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantRole extends Model
{
    protected $fillable = [
        'restaurant_id',
        'name',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'restaurant_role_permissions',
            'restaurant_role_id',
            'permission_id'
        );
    }

    public function staffAssignments()
    {
        return $this->hasMany(RestaurantStaffRoleAssignment::class);
    }
}
