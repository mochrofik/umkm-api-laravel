<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $store_id
 * @property string $name
 * @property string|null $description
 * @property int $display_order
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MenuCategories whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MenuCategories extends Model
{
    protected $table = 'menu_categories';

    public function products()
    {
        return $this->hasMany(Product::class, 'menu_category_id', 'id');
    }
}
