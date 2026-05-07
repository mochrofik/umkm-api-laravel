<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function addEdit(Request $request)
    {
        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();
            if (! $store) {
                return $this->errorResponse('Data toko tidak ditemukan', null, 422);
            }

            $product = $this->productService->addOrUpdateProduct(
                $request->all(),
                $store->id,
                $request->file('image')
            );

            return $this->successResponse('Berhasil menyimpan data product', $product, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validasi Gagal', $e->errors(), 422);
        } catch (\Throwable $th) {
            Log::error('store product '.$th);

            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = $this->productService->deleteProduct($id);

            return $this->successResponse('Data produk berhasil dihapus', $product, 200);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->errorResponse('Data Produk gagal dihapus', $th->getMessage(), 500);
        }
    }

    public function getProduct(Request $request)
    {
        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();
            if (! $store) {
                return $this->errorResponse('Data toko tidak ditemukan', null, 400);
            }

            $product = $this->productService->fetchProducts($request->all(), $store->id);

            return $this->successResponse('Data produk berhasil diambil', $product, 200);
        } catch (\Throwable $th) {
            Log::error('fetch menu produk '.$th);

            return $this->errorResponse('Terjadi kesalahan', $th->getMessage(), 500);
        }
    }

    public function detail($id)
    {
        try {
            $product = $this->productService->getProductDetail($id);

            return $this->successResponse('Data produk berhasil diambil', $product, 200);
        } catch (\Throwable $th) {
            Log::error('detail menu produk '.$th);

            return $this->errorResponse('Terjadi kesalahan', $th->getMessage(), 500);
        }
    }
}
