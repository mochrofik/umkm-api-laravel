<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $product_id
 * @property string $tag_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereTagName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductTag extends Model
{
    //
}
