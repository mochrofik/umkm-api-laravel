<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CategoriesController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function addEdit(Request $request)
    {
        try {
            $category = $this->categoryService->addOrUpdateCategory(
                $request->all(),
                $request->file('icon')
            );

            $message = ($request->id) ? 'Kategori berhasil diubah' : 'Kategori berhasil ditambahkan';

            return $this->successResponse($message, $category, 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validasi Gagal', $e->errors(), 422);
        } catch (\Throwable $th) {
            Log::error('addEdit category error: '.$th->getMessage());

            return $this->errorResponse('Terjadi kesalahan sistem', $th->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $categories = $this->categoryService->fetchCategories($request->all());

            return $this->successResponse('Data kategori berhasil diambil', $categories, 200);
        } catch (\Throwable $th) {
            Log::error('fetch categories '.$th);

            return $this->errorResponse('Terjadi kesalahan', $th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = $this->categoryService->deleteCategory($id);

            return $this->successResponse('Data kategori berhasil dihapus', $category, 200);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->errorResponse('Data kategori gagal dihapus', $th->getMessage(), 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $category = $this->categoryService->deleteCategory($id, true);

            return $this->successResponse('Data kategori berhasil dihapus', $category, 200);
        } catch (\Throwable $th) {
            Log::error($th);

            return $this->errorResponse('Data kategori gagal dihapus', $th->getMessage(), 500);
        }
    }
}
