<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuSection extends Model
{
    use HasFactory;

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
