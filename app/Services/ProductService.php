<?php

namespace App\Services;

use App\Helpers\DeleteImageHelper;
use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductService
{
    protected string $staticPath = 'uploads/product';

    /**
     * Fetch products for a store with filters.
     */
    public function fetchProducts(array $filters, $storeId)
    {
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? null;
        $status = $filters['status'] ?? null;

        $query = Product::query()
            ->where('store_id', $storeId)
            ->with('category')
            ->where(function ($query) use ($search, $status) {
                $query->where('name', 'like', "%{$search}%");
                if ($status != 'all' && $status != null) {
                    $query->where('is_available', (int) $status);
                }
            })
            ->latest();

        if ($limit && is_numeric($limit)) {
            return $query->paginate($limit)->withQueryString();
        }

        return $query->get();
    }

    /**
     * Add or update a product.
     */
    public function addOrUpdateProduct(array $data, $storeId, $image = null)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'is_available' => 'required|integer',
            'menu_category_id' => 'required|integer',
        ], [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan di database.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa angka bulat.',
            'max' => ':attribute maksimal :max karakter.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return DB::transaction(function () use ($data, $storeId, $image) {
            if (isset($data['id']) && $data['id'] != null) {

                $product = Product::where('id', $data['id'])->where('store_id', $storeId)->firstOrFail();
            } else {
                $product = new Product;
            }

            $product->store_id = $storeId;
            $product->menu_category_id = $data['menu_category_id'];
            $product->name = $data['name'];
            $product->price = $data['price'];
            $product->description = $data['description'];

            if (isset($data['stock']) && $data['stock'] < 0) {
                throw new \Exception('Stok tidak boleh kurang dari 0');
            }
            $product->stock = $data['stock'] ?? 0;
            $product->is_available = $data['is_available'];

            if ($image) {
                DeleteImageHelper::deleteOldImage($product->image_url, $this->staticPath);

                $cleanName = str_replace(' ', '_', $image->getClientOriginalName());
                $filename = time().'_'.$cleanName;
                $image->storeAs($this->staticPath, $filename, 'public');
                $product->image_url = $filename;
            }

            $product->save();

            if (isset($data['tags']) && $data['tags']) {
                $tagsArray = explode(',', $data['tags']);
                ProductTag::where('product_id', $product->id)->delete();
                foreach ($tagsArray as $tag) {
                    $tagName = trim($tag);
                    if (! empty($tagName)) {
                        $prodTag = new ProductTag;
                        $prodTag->product_id = $product->id;
                        $prodTag->tag_name = $tagName;
                        $prodTag->save();
                    }
                }
            }

            return $product;
        });
    }

    /**
     * Delete a product.
     */
    public function deleteProduct($id)
    {
        return DB::transaction(function () use ($id) {
            $product = Product::findOrFail($id);
            DeleteImageHelper::deleteOldImage($product->image_url, $this->staticPath);
            $product->delete();

            return $product;
        });
    }

    /**
     * Get product detail.
     */
    public function getProductDetail($id)
    {
        return Product::with(['category', 'tags'])
            ->where('id', $id)
            ->firstOrFail();
    }
}
