<?php

namespace App\Services;

use App\Exceptions\UserNotRegisteredException;
use App\Repository\GoogleRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleLoginService
{
    
    public function __construct(
    protected GoogleRepository $googleRepository
) {}


    public function GoogleCallback(Request $request, $role = null)
    {

        try {
            if ($request->has('code') && ! $request->has('state')) {
                $_GET['code'] = $request->code;
            }

            $googleUser = $this->googleRepository->getGoogleUser();
            $user = $this->googleRepository->getUserGoogle($googleUser->id, $googleUser->email);

            if (! $user) {
                 throw new UserNotRegisteredException('User not found',
                 [
                    'google_id' => $googleUser->id,
                    'email' => $googleUser->email,
                    'name' => $googleUser->name,
                    'create_password' => true,
                    'role' => ($role ?? $request->get('role')) ?? 'customer',
                ]);
            }
            if(!$user->google_id){
                $this->googleRepository->updateGoogleId($user->id, $googleUser->id);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'role' => $user->getRoleNames(),
                'user' => $user,
                'access_token' => $token,
            ]; 


        }catch (UserNotRegisteredException $e){
            throw $e;
        } catch (\Throwable $th) {
             Log::error('Google Callback Error: '.$th->getMessage(), [
                'trace' => $th->getTraceAsString(),
            ]);
            throw new Exception($th->getMessage());
        }
    }
}
