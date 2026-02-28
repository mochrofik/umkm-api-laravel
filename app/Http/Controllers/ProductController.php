<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function addEdit(Request $request)
    {
        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'store_id'     => 'required|exists:stores,id',
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'description'  => 'nullable|string',
            'image_url'    => 'nullable|string|max:255',
            'stock'        => 'nullable|integer|min:0',
            'is_available' => 'required|boolean',
        ], [
            'required' => ':attribute wajib diisi.',
            'exists'   => ':attribute tidak ditemukan di database.',
            'numeric'  => ':attribute harus berupa angka.',
            'integer'  => ':attribute harus berupa angka bulat.',
            'max'      => ':attribute maksimal :max karakter.',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal", $validator->errors(), 422);
        }

        try {
            if (isset($request->store_id) && $request->store_id != null) {
                $storeExist = Store::find($request->store->id);
                if (!$storeExist) {
                    return $this->errorResponse("Data toko tidak ditemukan", null, 422);
                }
                if (isset($request->id) && $request->id != null) {

                    $product = Product::find($request->id);
                } else {
                    $product = new Product();
                }

                $product->name = $request->name;
                $product->price = $request->price;
                $product->description = $request->description;
                if ($request->stock < 0) {
                    return $this->errorResponse("Gagal tidak boleh kurang dari 0", null, 500);
                }
                $product->stock = $request->stock;
                $product->is_available = $request->is_available;

                $staticPath = 'uploads/product';
                if ($request->hasFile('logo')) {
                    if ($product->logo && Storage::disk('public')->exists($staticPath . '/' . $product->logo)) {
                        Storage::disk('public')->delete($staticPath . '/' . $product->logo);
                    }

                    $file = $request->file('logo');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs($staticPath, $filename, 'public');
                    $product->logo = $filename;
                }

                $product->save();

                DB::commit();
                return $this->successResponse('Berhasil menambahkan data product',  $product, 201);
            } else {
                return $this->errorResponse("Gagal menambah data produk", null, 500);
            }
        } catch (\Throwable $th) {
            Log::error("store product " . $th);
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }
}
