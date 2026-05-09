<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{


    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {

        try {
            $data = $request->validated();
            $email = $data['email'];
            $password = $data['password'];

            $response = $this->authService->login($email, $password);

            return $this->successResponse('Login Berhasil', $response, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse(
                "Login Gagal",
                $th->getMessage(),
                500
            );
        }
    }
}
