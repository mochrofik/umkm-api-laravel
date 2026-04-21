<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    protected CustomerService $customerService;
    protected StoreService $storeService;

    public function __construct(CustomerService $customerService, StoreService $storeService)
    {
        $this->customerService = $customerService;
        $this->storeService = $storeService;
    }

    public function getNearby(Request $request)
    {
        try {
            $userLat = $request->input('lat');
            $userLng = $request->input('lng');
            $radius = 20; // Radius dalam KM

            if (!$userLat || !$userLng) {
                return $this->errorResponse('Latitude dan Longitude wajib diisi', null, 400);
            }

            $lokasiTerdekat = $this->storeService->getNearbyStores($userLat, $userLng, $radius);

            return $this->successResponse("Data toko terdekat", $lokasiTerdekat, 200);
        } catch (\Throwable $th) {
            Log::error("nearby toko error " . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $customers = $this->customerService->fetchCustomers($request->all());
            return $this->successResponse("Data pelanggan berhasil diambil", $customers, 200);
        } catch (\Throwable $th) {
            Log::error("fetch custo " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th->getMessage(), 500);
        }
    }

    public function addEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'phone_number'  => 'required|string|max:15',
            'gender'        => 'required|in:male,female',
            'date_of_birth' => 'nullable|date_format:Y-m-d',
            'address'       => 'nullable|string',
            'postal_code'   => 'nullable|string|max:5',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'role'          => 'required|in:admin,store,customer',
            'status'        => 'required|in:active,verify,banned',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', $validator->errors(), 422);
        }

        try {
            $customer = $this->customerService->addOrUpdateCustomer($request->all(), $request->id);
            return $this->successResponse('Berhasil menyimpan data pelanggan', $customer, 201);
        } catch (\Throwable $th) {
            Log::error("add edit customer error " . $th->getLine() . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $customer = $this->customerService->deleteCustomer($id);
            return $this->successResponse("Data pelanggan berhasil dihapus", $customer, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data pelanggan gagal dihapus", $th->getMessage(), 500);
        }
    }

    public function storeByCategory(Request $request)
    {
        try {
            $stores = $this->storeService->getStoresByCategory(
                $request->category,
                $request->input('lat'),
                $request->input('lng')
            );

            return $this->successResponse("Data store", $stores, 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function showStore($slug)
    {
        try {
            $store = $this->storeService->getStoreBySlug($slug);

            if (!$store) {
                return $this->errorResponse("Toko tidak ditemukan", null, 404);
            }

            return $this->successResponse("Detail toko", $store, 200);
        } catch (\Throwable $th) {
            Log::error("show store error " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th->getMessage(), 500);
        }
    }

    public function getStoreBySearching(Request $request)
    {
        try {
            $search = $request->input('search');

            if (!$search) {
                return $this->errorResponse('Parameter search wajib diisi', null, 400);
            }

            $stores = $this->storeService->searchStores(
                $search,
                $request->input('lat'),
                $request->input('lng')
            );

            return $this->successResponse("Hasil pencarian toko", $stores, 200);
        } catch (\Throwable $th) {
            Log::error("search store error " . $th);
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
