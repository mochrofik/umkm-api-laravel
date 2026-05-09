<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $store_id
 * @property string $name
 * @property numeric $price
 * @property string|null $description
 * @property string|null $image_url
 * @property int|null $stock
 * @property int $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $menu_category_id
 * @property-read \App\Models\MenuCategories|null $category
 * @property-read mixed $logo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \App\Models\Store|null $store
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductTag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMenuCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model
{


    use HasUuids;

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        if (!$this->image_url) return null;
        return asset('storage/uploads/product/' . $this->image_url);
    }


    public function tags()
    {
        return $this->hasMany(ProductTag::class);
    }
    public function category()
    {
        return $this->hasOne(MenuCategories::class, 'id', 'menu_category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

