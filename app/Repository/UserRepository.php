<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function checkEmailExists($email)
    {
        return User::where('email', $email)->first();
    }

    public function checkPhoneNumberExists($phoneNumber)
    {
        return User::where('phone_number', $phoneNumber)->first();
    }

    public function registerCustomer($request)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'email_verified_at' => $request->email_verified_at,
            'password' => Hash::make($request->password),
            'nik' => $request->nik,
            'phone_number' => $request->phone_number,
            'role' => 'customer',
            'status' => $request->status,
            'google_id' => $request->google_id,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'avatar' => $request->avatar,
        ];

        $user = User::updateOrCreate(['email' => $request->email], $data);

        return $user;
    }
    public function updateProfile($request, $userId)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'email_verified_at' => $request->email_verified_at,
            'password' => Hash::make($request->password),
            'nik' => $request->nik,
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'status' => $request->status,
            'google_id' => $request->google_id,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'avatar' => $request->avatar,
        ];

        $user = User::updateOrCreate(['id' => $userId], $data);

        return $user;
    }
}
