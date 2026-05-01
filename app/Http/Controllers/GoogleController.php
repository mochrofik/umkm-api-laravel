<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectCustomerToGoogle()
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
            'role' => 'customer',
        ]);
    }

    public function redirectStoreToGoogle()
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
            'role' => 'store',
        ]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // kalo frontend pakai post dan kirim code
            if ($request->has('code')) {
                $_GET['code'] = $request->code;
            }
            $role = null;

            if ($request->has('role')) {
                $role = $request->role;
            }

            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user berdasarkan email atau google_id
            $user = User::where('google_id', $googleUser->id)
                ->where('email', $googleUser->email)
                ->first();

            if (! $user) {
                return $this->successResponse('registrasi berhasil',
                    [
                        'google_user' => $googleUser,
                        'create_password' => true,
                        'role' => $role,
                    ],
                    200,
                );
            }
            // Create Sanctum Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return
                $this->successResponse(
                    'Registrasi Berhasil',
                    [
                        'access_token' => $token,
                        'role' => $user->getRoleNames(),
                        'user' => $user,
                    ],
                    200
                );

        } catch (Exception $e) {
            Log::error('error', [
                'error' => $e->getMessage(),
            ]);

            return
                $this->successResponse(
                    'Registrasi Gagal ',
                    null,
                    500
                );
        }
    }
}
