<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RestaurantBranch extends Model
{

use LogsActivity;

 public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->useLogName('restaurant-branch') 
        ->logOnly([ 'restaurant_id','name','address','lat','lng', 'opening_time','closing_time',])
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}

    protected $table = 'restaurant_branches';

    protected $fillable = [
        'restaurant_id',
        'name',
        'address',
        'lat',
        'lng',
        'opening_time',
        'closing_time',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'lat'        => 'decimal:7',
        'lng'        => 'decimal:7',
        'is_active'  => 'boolean',
        // time columns عادة بترجع string "HH:MM:SS" وده مناسب
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class, 'branch_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(RestaurantStaff::class, 'branch_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'branch_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'branch_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'branch_id');
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class, 'branch_id');
    }
}
