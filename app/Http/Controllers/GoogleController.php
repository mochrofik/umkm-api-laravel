<?php

namespace App\Http\Controllers;

use App\Exceptions\UserNotRegisteredException;
use App\Models\User;
use App\Services\GoogleLoginService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function __construct(
        protected GoogleLoginService $googleLoginService
    ) {}

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

    public function redirectLogin()
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {

            $response = $this->googleLoginService->GoogleCallback($request);

            return $this->successResponse('Login Berhasil', $response, 200);

        } catch (UserNotRegisteredException $e){
            return $this->successResponse($e->getMessage(), $e->getData(), 200);
        } catch (Exception $e) {
            return $this->errorResponse('Autentikasi Gagal', $e->getMessage(), 500);
        }
    }

    public function checkLoginGoogleApp(Request $request)
    {
        try {

            $response = $this->googleLoginService->GoogleCallback($request, 'customer');

            return $this->successResponse('Login Berhasil', $response, 200);

        }catch (UserNotRegisteredException $e){
            return $this->errorResponse($e->getMessage(), $e->getData(), 500);
        } catch (Exception $e) {
            Log::error('Google Auth Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->successResponse('Autentikasi Gagal', null, 500);
        }
    }
}
