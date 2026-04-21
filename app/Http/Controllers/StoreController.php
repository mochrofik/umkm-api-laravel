<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\StoreService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    protected StoreService $storeService;

    public function __construct(StoreService $storeService)
    {
        $this->storeService = $storeService;
    }

    public function fetch(Request $request)
    {
        try {
            $stores = $this->storeService->fetchStores($request->all());
            return $this->successResponse("Data toko berhasil diambil", $stores, 200);
        } catch (\Throwable $th) {
            Log::error("fetch store " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th->getMessage(), 500);
        }
    }

    public function addEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'email'        => $request->id ? 'required|email|unique:users,email,' . $request->id : 'required|email|unique:users,email',
            'role'         => 'required|in:admin,store,customer',
            'status'       => 'required|in:active,verify,banned',
            'password'     => $request->id ? 'nullable|string|min:8' : 'required|string|min:8',
            'store_name'   => 'required|string|max:255',
            'slug'         => $request->id ? 'required|string|unique:stores,slug,' . $request->id . ',user_id' : 'required|string|unique:stores,slug',
            'address'      => 'required|string',
            'description'  => 'required|string',
            'phone_number' => 'nullable|string',
            'latitude'     => 'nullable|numeric',
            'longitude'    => 'nullable|numeric',
            'open_at'      => 'nullable|date_format:H:i',
            'close_at'     => 'nullable|date_format:H:i',
        ], [
            'email.unique' => 'Validasi Gagal Email Sudah digunakan',
            'slug.unique'  => 'Validasi Gagal Nama Toko Sudah digunakan',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal", $validator->errors(), 422);
        }

        try {
            $store = $this->storeService->addOrUpdateStore($request->all(), $request->id);
            return $this->successResponse('Berhasil menyimpan data toko',  $store, 201);
        } catch (\Throwable $th) {
            Log::error("add edit toko error " . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $store = $this->storeService->deleteStore($id);
            return $this->successResponse("Data toko berhasil dihapus", $store, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data toko gagal dihapus", $th->getMessage(), 500);
        }
    }

    public function getCategory(Request $request)
    {
        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();

            if (!$store) {
                return $this->errorResponse("Data toko tidak ditemukan!", null, 400);
            }

            $categories = $this->storeService->fetchMenuCategories($request->all(), $store->id);
            return $this->successResponse("Data kategori berhasil diambil", $categories, 200);
        } catch (\Throwable $th) {
            Log::error("fetch menu categories " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th->getMessage(), 500);
        }
    }

    public function addEditMenuCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'            => 'nullable|exists:menu_categories,id',
            'name'          => 'required|string|max:255',
            'description'   => 'required|string',
            'display_order' => 'required|integer',
            'is_active'     => 'required|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal", $validator->errors(), 422);
        }

        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();

            if (!$store) {
                return $this->errorResponse("Toko tidak ditemukan", null, 500);
            }

            $menuCategory = $this->storeService->addOrUpdateMenuCategory($request->all(), $store->id, $request->id);
            $message = $request->id ? 'Kategori berhasil diubah' : 'Kategori berhasil ditambahkan';
            return $this->successResponse($message, $menuCategory, 201);
        } catch (\Throwable $th) {
            Log::error("addEdit category error: " . $th->getMessage());
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroyMenuCategories($id)
    {
        try {
            $category = $this->storeService->deleteMenuCategory($id);
            return $this->successResponse("Data kategori berhasil dihapus", $category, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data kategori gagal dihapus", $th->getMessage(), 500);
        }
    }
}
