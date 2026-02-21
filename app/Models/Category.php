<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;


    protected $appends = ['icon_url'];
    public function getIconUrlAttribute()
    {
        if (!$this->icon) return null;
        return asset('storage/uploads/categories/' . $this->icon);
    }
}
