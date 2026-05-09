<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string $address
 * @property string|null $phone_number
 * @property string $description
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $logo
 * @property string|null $open_at
 * @property string|null $close_at
 * @property int $is_open
 * @property numeric $rating
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read mixed $logo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MenuCategories> $menuCategories
 * @property-read int|null $menu_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StoreCategory> $store_categories
 * @property-read int|null $store_categories_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereCloseAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereOpenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store withoutTrashed()
 * @mixin \Eloquent
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Store where($column, $operator = null, $value = null, $boolean = 'and')
 
 */
class Store extends Model
{

    use SoftDeletes;

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute()
    {
        if (!$this->logo) return null;
        return asset('storage/uploads/store/' . $this->logo);
    }

    public function getUser()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'store_category');
    }
    public function store_categories()
    {
        return $this->hasMany(StoreCategory::class, 'store_id', 'id');
    }
    public function menuCategories()
    {
        return $this->hasMany(MenuCategories::class, 'store_id', 'id');
    }
    public function getProducts()
    {
        return $this->hasMany(Product   ::class, 'store_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
