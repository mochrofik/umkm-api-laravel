<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function fetch(Request $request)
    {
        try {
            $search = $request->query('search');
            $limit = $request->query('limit', 10);

            $categories = Store::query()
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate($limit)
                ->withQueryString();

            return $this->successResponse("Data toko berhasil diambil", $categories, 200);
        } catch (\Throwable $th) {
            Log::error("fetch store " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th, 500);
        }
    }

    public function addEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'role'     => 'required|in:admin,store,customer',
            'status'   => 'required|in:active,verify,banned',
            'password' => 'required|string|min:8',

            'store_name'   => 'required|string|max:255',
            'slug'      => 'required|string',
            'address'      => 'required|string',
            'description'  => 'required|string',
            'phone_number' => 'nullable|string',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'open_at'      => 'nullable|date_format:H:i',
            'close_at'     => 'nullable|date_format:H:i',
        ]);
        $cekSlugStore = Store::where('slug', $request->slug)->first();
        if ($cekSlugStore) {
            return $this->errorResponse("Validasi Gagal Nama Toko Sudah digunakan", $validator->errors(), 422);
        }

        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal", $validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'role'     => $request->role,
                'status'   => $request->status,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('store');
            $store = new Store();
            $store->user_id      = $user->id;
            $store->name         = $request->store_name;
            $store->slug         = Str::slug($request->slug);
            $store->address      = $request->address;
            $store->description  = $request->description;
            $store->phone_number = $request->phone_number;
            $store->latitude     = $request->latitude;
            $store->longitude    = $request->longitude;
            $store->open_at      = $request->open_at;
            $store->close_at     = $request->close_at;

            $staticPath = 'uploads/store';
            if ($request->hasFile('logo')) {
                if ($store->logo && Storage::disk('public')->exists($staticPath . '/' . $store->logo)) {
                    Storage::disk('public')->delete($staticPath . '/' . $store->logo);
                }

                $file = $request->file('logo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs($staticPath, $filename, 'public');
                $store->logo = $filename;
            }

            $store->save();
            DB::commit();
            return $this->successResponse('Berhasil menambahkan data toko',  $store, 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("add edit toko error " . $th->getMessage());
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
