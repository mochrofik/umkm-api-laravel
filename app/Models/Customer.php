<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $nik
 * @property string $phone_number
 * @property string|null $gender
 * @property string|null $date_of_birth
 * @property string $address
 * @property string|null $postal_code
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $avatar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $avatar_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUserId($value)
 * @mixin \Eloquent
 */
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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
