<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $store_id
 * @property int $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $categories
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StoreCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StoreCategory extends Model
{
    protected $table = "store_category";

     public function categories()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
