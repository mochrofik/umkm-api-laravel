<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $name
 * @property string $phone_number
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $subdistrict
 * @property string $postal_code
 * @property string $address_details
 * @property string|null $label
 * @property int $is_primary
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer $customer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereAddressDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereSubdistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAddresses whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserAddresses extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'phone_number',
        'province',
        'city',
        'district',
        'subdistrict',
        'postal_code',
        'address_details',
        'label',
        'is_primary',
        'latitude',
        'longitude',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
