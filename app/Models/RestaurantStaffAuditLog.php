<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantStaffAuditLog extends Model
{
    protected $table = 'restaurant_staff_audit_logs';

    protected $fillable = [
        'restaurant_id','branch_id','staff_id',
        'action','entity_type','entity_id',
        'method','path','status_code','ip','user_agent',
        'meta','before','after',
    ];

    protected $casts = [
        'meta' => 'array',
        'before' => 'array',
        'after' => 'array',
    ];

    public function staff()
    {
        return $this->belongsTo(RestaurantStaff::class, 'staff_id');
    }

    public function branch()
    {
        return $this->belongsTo(RestaurantBranch::class, 'branch_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }
}
