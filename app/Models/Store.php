<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
