<?php

namespace App\Services;

use App\Helpers\DeleteImageHelper;
use App\Models\User;
use App\Repository\UserRepository;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Validator;

class ProfileService
{


      protected string $staticPath = 'uploads/user';
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    public function updateProfile($request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('id', Auth::user()->id)->first();
            if($request->hasFile('avatar')){
                $validateImage = Validator::make($request->all(), [
                    'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ], [
                    'avatar.required' => 'Gambar wajib diisi.',
                    'avatar.image' => 'Gambar harus berupa file gambar.',
                    'avatar.mimes' => 'Gambar harus berupa file jpeg, png, jpg, atau gif.',
                    'avatar.max' => 'Gambar maksimal 2MB.',
                ]);
                if($validateImage->fails()){
                    throw new \Illuminate\Validation\ValidationException($validateImage);
                }


                $image = $request->file('avatar');
                $file = $image;
                
                
                $filename = time().'_'.$file->getClientOriginalName();
                $file->storeAs($this->staticPath, $filename, 'public');
                $request->avatar = $filename;
                DeleteImageHelper::deleteOldImage($user->avatar, $this->staticPath);
               
            }

            if(!isset($request->email) || $request->email == null){
                $request->email = $user->email;
            }

            if(!isset($request->phone_number) || $request->phone_number == null){
                $request->phone_number = $user->phone_number;
            }else{
                $validatePhone = Validator::make($request->all(), [
                    'phone_number' => 'required|string|min:10|max:15|unique:users,phone_number,' . $user->id,
                ], [
                    'phone_number.min' => 'Nomor telepon minimal 10 karakter.',
                    'phone_number.max' => 'Nomor telepon maksimal 15 karakter.',
                    'phone_number.unique' => 'Nomor telepon sudah digunakan oleh orang lain.',
                ]);
                
                if($validatePhone->fails()){
                    throw new \Illuminate\Validation\ValidationException($validatePhone);
                }
            }
            if(!isset($request->status) || $request->status == null){
                $request->status = $user->status;
            }
            if(!isset($request->nik) || $request->nik == null){
                $request->nik = $user->nik;
            }else{
                $validateNik = Validator::make($request->all(), [
                    'nik' => 'required|string|min:16|max:16|unique:users,nik,' . $user->id,
                ], [
                    'nik.required' => 'NIK wajib diisi.',
                    'nik.min' => 'NIK harus tepat 16 karakter.',
                    'nik.max' => 'NIK tidak boleh lebih dari 16 karakter.',
                    'nik.unique' => 'NIK sudah digunakan oleh orang lain.',
                ]);
                
                if($validateNik->fails()){
                    throw new \Illuminate\Validation\ValidationException($validateNik);
                }
            }


           $user = $this->userRepository->updateProfile($request, $user->id);
            
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    
}