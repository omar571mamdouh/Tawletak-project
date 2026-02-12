<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Restaurant extends Model
{

use LogsActivity;

    public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('restaurant') 
        ->logOnly(['name','description','phone','price-range'])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
    protected $fillable = [
        'name',
        'description',
        'phone',
        'category',
        'price_range',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(RestaurantBranch::class, 'restaurant_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(RestaurantStaff::class, 'restaurant_id');
    }

    public function loyalties(): HasMany
    {
        return $this->hasMany(CustomerLoyalty::class, 'restaurant_id');
    }

    public function menuSections()
{
    return $this->hasMany(MenuSection::class);
}

public function menuItems()
{
    return $this->hasMany(MenuItem::class);
}



}
