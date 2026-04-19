<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasRoles, HasFactory, Notifiable, SoftDeletes;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
        ];
    }

    public function getStore()
    {
        return $this->hasOne(Store::class, 'user_id', 'id');
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public static function checkUser($email)
    {

        $user = User::where(function ($query) use ($email) {
            $query->where('email', $email)
                ->orWhereHas('getStore', function ($q) use ($email) {
                    $q->where('phone_number', $email);
                });
        })->first();

        if ($user) {
            return $user;
        }

        $user = User::where(function ($query) use ($email) {
            $query->where('email', $email)
                ->orWhereHas('getCustomer', function ($q) use ($email) {
                    $q->where('phone_number', $email);
                });
        })->first();

        if ($user) {
            return $user;
        }

        $user = User::where('email', $email)->first();

        return $user;
    }
}
