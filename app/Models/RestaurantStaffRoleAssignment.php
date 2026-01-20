<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantStaffRoleAssignment extends Model
{
    protected $fillable = [
        'staff_id',
        'restaurant_role_id',
    ];

    public function staff()
    {
        return $this->belongsTo(RestaurantStaff::class, 'staff_id');
    }

    public function role()
    {
        return $this->belongsTo(RestaurantRole::class, 'restaurant_role_id');
    }
}
