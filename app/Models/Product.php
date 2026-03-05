<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

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
}
