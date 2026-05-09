<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use App\Services\RegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    protected RegisterService $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
    }

    
    public function registerCustomer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
                'phone_number' => 'required|string|min:10|max:15',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validasi Gagal', $validator->errors(), 422);
            }

            $response = $this->registerService->registerCustomer($request);

            return $this->successResponse('Registrasi Berhasil', $response, 200);

        } catch (ValidationException $e) {
            return $this->errorResponse('Validasi Gagal', $e->errors(), 422);
        } catch (\Throwable $th) {
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function registerGoogleCustomer(Request $request)
    {
        try {
            $response = $this->registerService->registerCustomer($request, true);

            return $this->successResponse('Registrasi Berhasil', $response, 200);

        } catch (ValidationException $e) {
            return $this->errorResponse('Validasi Gagal', $e->errors(), 422);
        } catch (\Throwable $th) {
            Log::error('register error '.$th->getMessage());

            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
