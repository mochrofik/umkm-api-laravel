<?php

namespace App\Http\Controllers;

use App\Models\MenuCategories;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                })->with("user")
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
            'role'     => 'required|in:admin,store,customer',
            'status'   => 'required|in:active,verify,banned',
            'password' => $request->id ? 'nullable|string|min:8' : 'required|string|min:8',
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

        if (!isset($request->id) || $request->id == null) {
            $cekSlugStore = Store::where('slug', $request->slug)->first();
            if ($cekSlugStore) {
                return $this->errorResponse("Validasi Gagal Nama Toko Sudah digunakan", $validator->errors(), 422);
            }

            $checkEmail = User::where('email', $request->email)->first();
            if ($checkEmail != null) {
                return $this->errorResponse("Validasi Gagal Email Sudah digunakan", $validator->errors(), 422);
            }
        }
        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal", $validator->errors(), 422);
        }


        try {

            if (isset($request->id) && $request->id != null) {
                $user = User::where("id", $request->id)->first();
                if ($request->password != null) {
                    if ($user->password != $request->password) {
                        $user->password = $request->password;
                    }
                }

                if ($request->email != null && $user->email != $request->email) {
                    $user->email = $request->email;
                }
            } else {
                $user =  new User();
                $user->password = $request->password;
                $user->email = $request->email;
            }

            $user->name =      $request->name;
            $user->role = $request->role;
            $user->status   = $request->status;
            $user->save();

            if (isset($request->id) && $request->id != null) {
                $store = Store::where('user_id', $request->id)->first();
            } else {

                $user->assignRole('store');
                $store = new Store();
            }

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
            Log::error("add edit toko error " . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Store::findOrFail($id);
            $category->delete();
            return $this->successResponse("Data toko berhasil dihapus", $category, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data toko gagal dihapus", $th, 500);
        }
    }

    public function getCategory(Request $request)
    {

        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();
            if (!$store) {
                return $this->errorResponse("Data toko tidak ditemukan", null, 400);
            }

            $search = $request->query('search');
            $limit = $request->query('limit');

            $query   = MenuCategories::query()
                ->where('store_id', $store->id)
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->latest();

            if ($limit && is_numeric($limit)) {
                $categories = $query->paginate($limit)->withQueryString();
            } else {
                $categories = $query->get();
            }

            return $this->successResponse("Data kategori berhasil diambil", $categories, 200);
        } catch (\Throwable $th) {
            Log::error("fetch menu categories " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th, 500);
        }
    }

    public function addEditMenuCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'     => 'nullable|exists:categories,id',
            'name'   => 'required|string|max:255',
            'description'   => 'required|string',
            'display_order'   => 'required|integer',
            'is_active'     => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal", $validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();
            if (!$store) {
                return $this->errorResponse("Toko tidak ditemukan", null, 500);
            }

            if ($request->is_active == 1) {
                $duplicateOrder = MenuCategories::where('store_id', $store->id)
                    ->where('display_order', $request->display_order)
                    ->where('is_active', 1)
                    ->when($request->id, function ($query) use ($request) {
                        return $query->where('id', '!=', $request->id);
                    })
                    ->first();

                if ($duplicateOrder) {
                    return $this->errorResponse("Urutan #{$request->display_order} sudah digunakan oleh kategori aktif: '{$duplicateOrder->name}'", null, 422);
                }
            }

            if (isset($request->id) && $request->id != null) {
                $menuCategory = MenuCategories::find($request->id);
            } else {

                $nameExist = MenuCategories::where('name', $request->name)
                    ->where('store_id', $store->id)->first();
                if ($nameExist) {
                    return $this->errorResponse("Data sudah ada di database", null, 500);
                }
                $menuCategory = new MenuCategories();
            }

            $menuCategory->store_id = $store->id;
            $menuCategory->name = $request->name;
            $menuCategory->description = $request->description;
            $menuCategory->display_order = $request->display_order;
            $menuCategory->is_active = $request->is_active;

            $menuCategory->save();

            DB::commit();
            $message = (isset($request->id)) ? 'Kategori berhasil diubah' : 'Kategori berhasil ditambahkan';
            return $this->successResponse($message, $menuCategory, 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("addEdit category error: " . $th->getMessage());
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroyMenuCategories($id)
    {
        try {
            $category = MenuCategories::findOrFail($id);


            $product = Product::where('menu_category_id', $id)->first();
            if ($product != null) {
                return $this->errorResponse("Gagal Hapus, data kategori dipakai pada produk aktif", null, 500);
            }
            $category->delete();
            return $this->successResponse("Data kategori berhasil dihapus", $category, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data kategori gagal dihapus", $th, 500);
        }
    }
}
