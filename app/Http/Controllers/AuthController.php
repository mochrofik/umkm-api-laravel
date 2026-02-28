<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal Email Atau Password Kosong", $validator->errors(), 422);
        }
        try {

            $user = User::where('email', $request->email)->first();

            if (! $user || !($request->password == $user->password)) {
                return $this->errorResponse("Email atau Password salah", null, 401);
            }

            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return
                $this->successResponse(
                    "Login Berhasil",
                    [
                        'access_token' => $token,
                        'role' => $user->getRoleNames(),
                        'user' => $user,
                    ],
                    200
                );
        } catch (\Throwable $th) {
            Log::error($th);
            return
                $this->successResponse(
                    "Login Gagal ",
                    null,
                    500
                );
        }
    }
}
