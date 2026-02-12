<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppReservation extends Model
{
    protected $table = 'app_reservations'; // ⬅️ اسم الجدول
    
    protected $fillable = [
        'restaurant_id',
        'customer_name',
        'customer_phone',
        'date',
        'time',
        'guests_count',
        'table_id',
        'code',
        'status',
        'reason',
        'cancelled_at',
    ];

    protected $casts = [
        'date' => 'date',
        'cancelled_at' => 'datetime',
    ];
}