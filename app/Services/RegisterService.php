<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
use App\Repository\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterService
{

    public function __construct(
        protected  UserRepository $userRepository
    ) {}

    public function registerFromGoogle(Request $request)
    {
        $validator = null;

        if (isset($request->role) && $request->role == 'store') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'role' => 'required|in:admin,store,customer',
                'status' => 'required_without:google_id|in:active,verify,banned',
                'password' => 'required|string|min:8',
                'store_name' => 'required|string|max:255',
                'slug' => 'required|string',
                'address' => 'required|string',
                'description' => 'required|string',
                'phone_number' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'open_at' => 'nullable|date_format:H:i',
                'close_at' => 'nullable|date_format:H:i',
            ]);

            $validator->after(function ($validator) use ($request) {
                if (Store::where('slug', $request->slug)->exists()) {
                    $validator->errors()->add('slug', 'Nama Toko Sudah digunakan');
                }
                if (User::where('email', $request->email)->whereHas('getStore')->exists()) {
                    $validator->errors()->add('email', 'Email Toko Sudah digunakan');
                }
            });
        } elseif (isset($request->role) && $request->role == 'customer') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'role' => 'required|in:admin,store,customer',
                'status' => 'required_without:google_id|in:active,verify,banned',
                'password' => 'required|string|min:8',
                'nik' => 'nullable|numeric|digits:16|unique:customers,nik',
                'phone_number' => 'required|string|min:10|max:15',
                'gender' => 'nullable|in:male,female',
                'date_of_birth' => 'nullable|date|before:today',
                'address' => 'nullable|string|min:10',
                'postal_code' => 'nullable|digits:5',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);
        }

        if (! $validator) {
            $validator = Validator::make($request->all(), [
                'role' => 'required|in:store,customer',
            ]);
        }

        $validator->validate();

        // Manual check for phone number
        if (Store::where('phone_number', $request->phone_number)->exists()) {
            throw ValidationException::withMessages([
                'phone_number' => ['Nomor Telepon Sudah Digunakan'],
            ]);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->google_id != null ? 'active' : $request->status,
                'password' => Hash::make($request->password),
                'google_id' => $request->google_id,
                'nik' => $request->nik,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
            ]);

            if ($request->role == 'store') {
                $user->assignRole('store');
                $store = new Store;
                $store->user_id = $user->id;
                $store->name = $request->store_name;
                $store->slug = Str::slug($request->slug);
                $store->address = $request->address;
                $store->description = $request->description;
                $store->latitude = $request->latitude;
                $store->longitude = $request->longitude;
                $store->open_at = $request->open_at;
                $store->close_at = $request->close_at;
                $store->save();

                DB::commit();

                return $store;
            } else {
                $user->assignRole('customer');
                DB::commit();

            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function registerCustomer(Request $request, $isGoogle = false){


        try {
            DB::beginTransaction();
            $isExists = $this->userRepository->checkEmailExists($request->email);
            if ($isExists) {
                throw ValidationException::withMessages([
                    'email' => ['Email Sudah Digunakan'],
                ]);
            }
    
            $isExists = $this->userRepository->checkPhoneNumberExists($request->phone_number);
            if ($isExists) {
                throw ValidationException::withMessages([
                    'phone_number' => ['Nomor Telepon Sudah Digunakan'],
                ]);
            }

            if($isGoogle){
                $request->email_verified_at = now();
                $request->status =  'active';
            }else{
                $request->status =  'verify';
            }
    
            $user =  $this->userRepository->registerCustomer($request);
            $user->assignRole('customer');
            DB::commit();

            return $user;
            
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    

    }
}
