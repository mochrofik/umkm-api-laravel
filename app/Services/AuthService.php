<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function login($email, $password)
    {

        try {
            $user = User::checkUser($email);
            if ($user) {
                if (! $user || !Hash::check($password, $user->password)) {
                    throw new AuthException("Email atau Password salah", null, 401);
                }
            } else {
                $user = User::checkUser($email);

                if (! $user || !Hash::check($password, $user->password)) {
                    throw new AuthException("Email atau Password salah", null, 401);
                }
            }
          
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;


            return [
                        'access_token' => $token,
                        'role' => $user->getRoleNames(),
                        'user' => $user,
                    ];
                
     
        } catch (\Throwable $th) {
            Log::error($th);
            throw new Exception($th->getMessage());
        }
    }
}
