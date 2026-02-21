<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{

    public function addEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'nullable|exists:categories,id',
            'name'   => 'required|string|max:255',
            'icon'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validasi file gambar
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Validasi Gagal", $validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            if (isset($request->id) && $request->id != null) {
                $category = Category::find($request->id);
            } else {
                // Cek duplikasi nama hanya untuk data baru
                $nameExist = Category::where('name', $request->name)->first();
                if ($nameExist) {
                    return $this->errorResponse("Data sudah ada di database", null, 409);
                }
                $category = new Category();
            }

            $category->name = $request->name;

            $staticPath = 'uploads/categories';
            if ($request->hasFile('icon')) {
                if ($category->icon && Storage::disk('public')->exists($staticPath . '/' . $category->icon)) {
                    Storage::disk('public')->delete($staticPath . '/' . $category->icon);
                }

                $file = $request->file('icon');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs($staticPath, $filename, 'public');
                $category->icon = $filename;
            }

            $category->save();

            DB::commit();

            $message = (isset($request->id)) ? 'Kategori berhasil diubah' : 'Kategori berhasil ditambahkan';
            return $this->successResponse($message, $category, 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("addEdit category error: " . $th->getMessage());
            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $search = $request->query('search');
            $limit = $request->query('limit', 10);

            $categories = Category::query()
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'like', "%{$search}%");
                })
                ->latest()
                   ->paginate($limit)
                ->withQueryString();

            return $this->successResponse("Data kategori berhasil diambil", $categories, 200);
        } catch (\Throwable $th) {
            Log::error("fetch categories " . $th);
            return $this->errorResponse("Terjadi kesalahan", $th, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return $this->successResponse("Data kategori berhasil dihapus", $category, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data kategori gagal dihapus", $th, 500);
        }
    }
    public function forceDelete($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->forceDelete();
            return $this->successResponse("Data kategori berhasil dihapus", $category, 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return $this->errorResponse("Data kategori gagal dihapus", $th, 500);
        }
    }
}
