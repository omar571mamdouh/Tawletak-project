<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MenuSection extends Model
{
    use HasFactory,LogsActivity;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('menu_sections') 
        ->logAll()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}

    protected $fillable = [
        'restaurant_id',
        'name',
        'sort_order',
        'is_active',
    ];

    /**
     * Section belongs to a restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Section has many menu items
     */
    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }
}
