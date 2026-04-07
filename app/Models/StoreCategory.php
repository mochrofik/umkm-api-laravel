<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    protected $table = "store_category";

     public function categories()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
