<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    public function redirectLogin(){
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Jika frontend mengirim 'code' dalam body request (POST), Socialite biasanya butuh di $_GET
            if ($request->has('code') && !$request->has('state')) {
                $_GET['code'] = $request->code;
            }

            $role = $request->get('role');
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Cari user berdasarkan google_id ATAU email
            // Ini mencegah duplikasi jika user sebelumnya daftar manual pakai email yang sama
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if (! $user) {
                // User belum ada, kirim data minimal ke frontend untuk registrasi lanjut
                return $this->successResponse('Registrasi diperlukan', [
                    'google_id' => $googleUser->id,
                    'email' => $googleUser->email,
                    'name' => $googleUser->name,
                    'create_password' => true,
                    'role' => $role,
                ], 200);
            }

            // Jika user ditemukan lewat email tapi belum punya google_id, hubungkan akunnya
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->id]);
            }

            // Create Sanctum Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successResponse('Login Berhasil', [
                'access_token' => $token,
                'role' => $user->getRoleNames(),
                'user' => $user,
            ], 200);

        } catch (Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->successResponse('Autentikasi Gagal', null, 500);
        }
    }
}
