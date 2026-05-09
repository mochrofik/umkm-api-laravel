<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string|null $parent_code
 * @property string $name
 * @property int $level
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Region> $children
 * @property-read int|null $children_count
 * @property-read Region|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereParentCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
