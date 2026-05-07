<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        try {
            $customer = $this->customerService->addOrUpdateCustomer($request->all(), $request->id);
            return $this->successResponse('Berhasil menyimpan data pelanggan', $customer, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validasi gagal', $e->errors(), 422);
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
