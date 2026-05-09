<?php

namespace App\Repository;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class GoogleRepository
{
    public function getGoogleUser()
    {
        return Socialite::driver('google')->stateless()->user();
    }

    public function getUserGoogle($googleId, $email)
    {
        return User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();
    }

    public function updateGoogleId($id, $googleId)
    {
        return User::where('id', $id)->update([
            'google_id' => $googleId,
        ]);
    }
}
