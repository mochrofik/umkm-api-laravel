<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function tags()
    {
        return $this->hasMany(ProductTag::class);
    }
}
