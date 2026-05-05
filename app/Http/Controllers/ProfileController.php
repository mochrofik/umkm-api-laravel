<?php

namespace App\Http\Controllers;

use App\Helpers\DeleteImageHelper;
use App\Models\Category;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $auth = Auth::user();

            $user = User::where('id', $auth->id)
                ->with('getStore.store_categories.categories')
                ->with('getCustomer')
                ->first();

            $user->roles = $auth->getRoleNames();

            return $this->successResponse('Data profile', $user, 200);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->errorResponse('Data profile gagal', $th, 500);
        }
    }

    public function update(Request $request)
    {

        DB::beginTransaction();
        try {
            if (isset($request->id_user) && $request->id_user != null) {
                if ($request->role == 'store') {

                    $user = User::where('id', $request->id_user)->first();
                    if (! $user) {
                        return $this->errorResponse('Data profile tidak ditemukan', null, 422);
                    }

                    $store = Store::where('user_id', $request->id_user)->first();
                    if (! $store) {
                        return $this->errorResponse('Data toko tidak ditemukan', null, 422);
                    }

                    $checkEmail = User::where('email', $request->email)
                        ->whereHas('getStore')
                        ->first();
                    if ($checkEmail != null && $request->email != $user->email) {
                        return $this->errorResponse('Validasi Gagal Email Sudah digunakan', null, 422);
                    }

                    $user->name = $request->name;
                    $user->email = $request->email;
                    $user->save();

                    if (isset($request->categories)) {
                        StoreCategory::where('store_id', $store->id)->delete();
                        foreach ($request->categories as $key => $value) {
                            $checkExistCategory = Category::find($value);

                            if ($checkExistCategory) {
                                $store_category = new StoreCategory;
                                $store_category->store_id = $store->id;
                                $store_category->category_id = $checkExistCategory->id;
                                $store_category->save();
                            }
                        }
                    }

                    $store->name = $request->store_name;
                    $store->slug = Str::slug($request->slug);
                    $store->address = $request->address;
                    $store->description = $request->description;
                    $store->phone_number = $request->phone_number;
                    $store->latitude = $request->latitude;
                    $store->longitude = $request->longitude;
                    $store->open_at = $request->open_at;
                    $store->close_at = $request->close_at;
                    $store->is_open = ($request->is_open == '1' || $request->is_open == 'true') ? true : false;

                    $staticPath = 'uploads/store';
                    if ($request->hasFile('icon')) {
                        DeleteImageHelper::deleteOldImage($store->logo, $staticPath);

                        $file = $request->file('icon');
                        $filename = time().'_'.$file->getClientOriginalName();
                        $file->storeAs($staticPath, $filename, 'public');
                        $store->logo = $filename;
                    }

                    $store->save();
                    DB::commit();

                    return $this->successResponse('Berhasil mengubah data toko', $store, 201);
                } elseif ($request->role == 'admin') {
                }
            }

            return $request;
        } catch (\Throwable $th) {
            DeleteImageHelper::deleteOldImage($filename, $staticPath);
            DB::rollBack();
            Log::error('error update profile '.$th);

            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
