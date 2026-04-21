<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategories extends Model
{
    protected $table = 'menu_categories';

    public function products()
    {
        return $this->hasMany(Product::class, 'menu_category_id', 'id');
    }
}
