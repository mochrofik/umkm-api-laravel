<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //

    protected $appends = ['avatar_url'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) return null;
        return asset('storage/uploads/customer/' . $this->avatar);
    }
}
