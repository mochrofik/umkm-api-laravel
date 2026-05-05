<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'parent_code',
        'name',
        'level',
    ];

    /**
     * Get parent region.
     */
    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_code', 'code');
    }

    /**
     * Get children regions.
     */
    public function children()
    {
        return $this->hasMany(Region::class, 'parent_code', 'code');
    }
}
