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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereAddressDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereSubdistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerAddress whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomerAddress extends Model
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
