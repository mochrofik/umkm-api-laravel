<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'description'  => 'nullable|string',
            'image_url'    => 'nullable|string|max:255',
            'stock'        => 'nullable|integer|min:0',
            'is_available' => 'required|integer',
            'menu_category_id' => 'required|integer',
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
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();
            if ($store != null) {
                if (isset($request->id) && $request->id != null) {

                    $product = Product::find($request->id);
                } else {
                    $product = new Product();
                }

                $product->store_id = $store->id;
                $product->menu_category_id = $request->menu_category_id;
                $product->name = $request->name;
                $product->price = $request->price;
                $product->description = $request->description;
                if ($request->stock < 0) {
                    return $this->errorResponse("Gagal tidak boleh kurang dari 0", null, 500);
                }
                $product->stock = $request->stock;
                $product->is_available = $request->is_available;

                $staticPath = 'uploads/product';
                if ($request->hasFile('image')) {
                    if ($product->logo && Storage::disk('public')->exists($staticPath . '/' . $product->logo)) {
                        Storage::disk('public')->delete($staticPath . '/' . $product->logo);
                    }


                    $file = $request->file('image');
                    $cleanName = str_replace(' ', '_', $file->getClientOriginalName());
                    $filename = time() . '_' . $cleanName;
                    $file->storeAs($staticPath, $filename, 'public');
                    $product->image_url = $filename;
                }

                $product->save();

                DB::commit();
                return $this->successResponse('Berhasil menambahkan data product',  $product, 201);
            } else {
                return $this->errorResponse("Data toko tidak ditemukan", null, 422);
            }
        } catch (\Throwable $th) {
            Log::error("store product " . $th);
            DB::rollBack();
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $staticPath = 'uploads/product';
            if ($product->image_url && Storage::disk('public')->exists($staticPath . '/' . $product->image_url)) {
                Storage::disk('public')->delete($staticPath . '/' . $product->image_url);
            }
            $product->delete();
            return $this->successResponse("Data produk berhasil dihapus", $product, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data Produk gagal dihapus", $th, 500);
        }
    }

    public function getProduct(Request $request)
    {

        try {
            $auth = Auth::user();
            $store = Store::where('user_id', $auth->id)->first();
            if (!$store) {
                return $this->errorResponse("Data toko tidak ditemukan", null, 400);
            }

            $search = $request->query('search');
            $limit = $request->query('limit');

            $query   = Product::query()
                ->where('store_id', $store->id)
                ->with('category')
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->latest();

            if ($limit && is_numeric($limit)) {
                $product = $query->paginate($limit)->withQueryString();
            } else {
                $product = $query->get();
            }

            return $this->successResponse("Data produk berhasil diambil", $product, 200);
        } catch (\Throwable $th) {
            Log::error("fetch menu produk " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th, 500);
        }
    }

    public function detail($id)
    {
        try {
            $product = Product::with('category')->where('id', $id)->first();
            return $this->successResponse("Data produk berhasil diambil", $product, 200);
        } catch (\Throwable $th) {
            Log::error("detail menu produk " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th, 500);
        }
    }
}
