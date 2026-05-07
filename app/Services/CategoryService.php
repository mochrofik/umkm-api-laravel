<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    protected string $staticPath = 'uploads/categories';

    /**
     * Fetch categories with search and optional pagination.
     */
    public function fetchCategories(array $filters)
    {
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? null;

        $query = Category::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->latest();

        if ($limit && is_numeric($limit)) {
            return $query->paginate($limit)->withQueryString();
        }

        return $query->get();
    }

    /**
     * Add or update a category.
     */
    public function addOrUpdateCategory(array $data, $icon = null)
    {
        $validator = Validator::make(array_merge($data, ['icon' => $icon]), [
            'id'     => 'nullable|exists:categories,id',
            'name'   => 'required|string|max:255',
            'icon'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data, $icon) {
            if (isset($data['id']) && $data['id'] != null) {
                $category = Category::findOrFail($data['id']);
            } else {
                $nameExist = Category::where('name', $data['name'])->first();
                if ($nameExist) {
                    throw new \Exception("Data sudah ada di database");
                }
                $category = new Category();
            }

            $category->name = $data['name'];

            if ($icon) {
                $oldImage = $category->icon;
                if ($oldImage && Storage::disk('public')->exists($this->staticPath . '/' . $oldImage)) {
                    Storage::disk('public')->delete($this->staticPath . '/' . $oldImage);
                }

                $filename = time() . '_' . $icon->getClientOriginalName();
                $icon->storeAs($this->staticPath, $filename, 'public');
                $category->icon = $filename;
            }

            $category->save();

            return $category;
        });
    }

    /**
     * Delete a category.
     */
    public function deleteCategory($id, bool $force = false)
    {
        return DB::transaction(function () use ($id, $force) {
            $category = Category::findOrFail($id);
            
            $oldImage = $category->icon;
            if ($oldImage && Storage::disk('public')->exists($this->staticPath . '/' . $oldImage)) {
                Storage::disk('public')->delete($this->staticPath . '/' . $oldImage);
            }

            if ($force) {
                $category->forceDelete();
            } else {
                $category->delete();
            }

            return $category;
        });
    }
}
