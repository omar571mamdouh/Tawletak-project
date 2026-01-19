<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'menu_section_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
        'is_featured',
        'sort_order',
    ];

    /**
     * Item belongs to a restaurant
     */
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Item belongs to a menu section
     */
    public function section()
    {
        return $this->belongsTo(MenuSection::class, 'menu_section_id');
    }

    protected static function booted(): void
{
    static::saving(function (MenuItem $item) {
        // لو restaurant_id مش متبعت، بس menu_section_id موجود
        if (empty($item->restaurant_id) && !empty($item->menu_section_id)) {
            $item->restaurant_id = \App\Models\MenuSection::whereKey($item->menu_section_id)
                ->value('restaurant_id');
        }
    });
}

}
