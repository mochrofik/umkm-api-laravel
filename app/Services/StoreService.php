<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use App\Models\MenuCategories;
use App\Models\Product;
use App\Helpers\DeleteImageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class StoreService
{
    protected string $staticPath = 'uploads/store';

    /**
     * Fetch stores with filters and pagination.
     */
    public function fetchStores(array $filters)
    {
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? 10;
        $status = $filters['status'] ?? null;

        return Store::query()
            ->when($status, function ($query, $status) {
                $query->whereHas('user', function ($query) use ($status) {
                    if ($status != "all") {
                        $query->where('status', $status);
                    }
                });
            })
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('phone_number', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
            })
            ->with("user")
            ->latest()
            ->paginate($limit)
            ->withQueryString();
    }

    /**
     * Add or update a store and its user account.
     */
    public function addOrUpdateStore(array $data, $id = null)
    {
        return DB::transaction(function () use ($data, $id) {
            if ($id) {
                $user = User::findOrFail($id);
                $store = Store::where('user_id', $id)->firstOrFail();

                if (!empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                }

                if (!empty($data['email']) && $user->email != $data['email']) {
                    $user->email = $data['email'];
                }
            } else {
                $user = new User();
                $user->email = $data['email'];
                $user->password = Hash::make($data['password']);
            }

            $user->name = $data['name'];
            $user->role = $data['role'] ?? 'store';
            $user->status = $data['status'] ?? 'active';
            $user->save();

            if (!$id) {
                $user->assignRole('store');
                $store = new Store();
            }

            $store->user_id      = $user->id;
            $store->name         = $data['store_name'];
            $store->slug         = Str::slug($data['slug']);
            $store->address      = $data['address'];
            $store->description  = $data['description'];
            $store->phone_number = $data['phone_number'] ?? null;
            $store->latitude     = $data['latitude'] ?? null;
            $store->longitude    = $data['longitude'] ?? null;
            $store->open_at      = $data['open_at'] ?? null;
            $store->close_at     = $data['close_at'] ?? null;
            $store->is_open      = ($data['is_open'] == "1" || $data['is_open'] == "true") ? true : false;

            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                DeleteImageHelper::deleteOldImage($store->logo, $this->staticPath);
                $file = $data['logo'];
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs($this->staticPath, $filename, 'public');
                $store->logo = $filename;
            }

            $store->save();

            return $store;
        });
    }

    /**
     * Delete a store and its user account.
     */
    public function deleteStore($id)
    {
        return DB::transaction(function () use ($id) {
            $store = Store::findOrFail($id);
            DeleteImageHelper::deleteOldImage($store->logo, $this->staticPath);
            
            $user = User::find($store->user_id);
            if ($user) {
                $user->delete();
            }
            
            $store->delete();
            return $store;
        });
    }

    /**
     * Fetch menu categories for a store.
     */
    public function fetchMenuCategories(array $filters, $storeId)
    {
        $search = $filters['search'] ?? null;
        $limit = $filters['limit'] ?? null;
        $status = $filters['status'] ?? null;

        $query = MenuCategories::query()
            ->where('store_id', $storeId)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status !== null && $status !== 'all', function ($query) use ($status) {
                $query->where('is_active', (int)$status);
            })
            ->latest();

        if ($limit && is_numeric($limit)) {
            return $query->paginate($limit)->withQueryString();
        }

        return $query->get();
    }

    /**
     * Add or update a menu category.
     */
    public function addOrUpdateMenuCategory(array $data, $storeId, $id = null)
    {
        return DB::transaction(function () use ($data, $storeId, $id) {
            if ($data['is_active'] == 1) {
                $duplicateOrder = MenuCategories::where('store_id', $storeId)
                    ->where('display_order', $data['display_order'])
                    ->where('is_active', 1)
                    ->when($id, function ($query) use ($id) {
                        return $query->where('id', '!=', $id);
                    })
                    ->first();

                if ($duplicateOrder) {
                    throw new \Exception("Urutan #{$data['display_order']} sudah digunakan oleh kategori aktif: '{$duplicateOrder->name}'");
                }
            }

            if ($id) {
                $menuCategory = MenuCategories::findOrFail($id);
            } else {
                $nameExist = MenuCategories::where('name', $data['name'])
                    ->where('store_id', $storeId)->first();
                if ($nameExist) {
                    throw new \Exception("Data sudah ada di database");
                }
                $menuCategory = new MenuCategories();
            }

            $menuCategory->store_id = $storeId;
            $menuCategory->name = $data['name'];
            $menuCategory->description = $data['description'];
            $menuCategory->display_order = $data['display_order'];
            $menuCategory->is_active = $data['is_active'];

            $menuCategory->save();

            return $menuCategory;
        });
    }

    /**
     * Delete a menu category.
     */
    public function deleteMenuCategory($id)
    {
        $category = MenuCategories::findOrFail($id);

        $product = Product::where('menu_category_id', $id)->first();
        if ($product) {
            throw new \Exception("Gagal Hapus, data kategori dipakai pada produk aktif");
        }

        $category->delete();
        return $category;
    }

    /**
     * Get nearby stores based on latitude and longitude.

     *
     * @param float $lat
     * @param float $lng
     * @param int $radius
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNearbyStores($lat, $lng, $radius = 20)
    {
        return Store::selectRaw("id, name, slug, logo, rating, description, latitude, longitude, 
            ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) 
            * cos( radians( longitude ) - radians(?) ) 
            + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS jarak", [$lat, $lng, $lat])
            ->having('jarak', '<', $radius)
            ->orderBy('jarak', 'asc')
            ->get();
    }

    /**
     * Get stores by category and proximity.
     *
     * @param string|null $category
     * @param float|null $lat
     * @param float|null $lng
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStoresByCategory($categoryName, $lat = null, $lng = null)
    {
        $category = str_replace('-', '%', $categoryName);
        
        $query = Store::query();
        
        if ($lat && $lng) {
            $query->select('*')
                ->selectRaw("id, name, slug, logo, rating, description, latitude, longitude, 
                ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) 
                * cos( radians( longitude ) - radians(?) ) 
                + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS jarak", [$lat, $lng, $lat]);
        }

        return $query->where(function ($q) use ($category) {
            $q->whereHas('menuCategories', function ($sub) use ($category) {
                $sub->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($category) . "%"]);
            })
            ->orWhereHas('store_categories', function ($sub) use ($category) {
                $sub->whereHas('categories', function ($query) use ($category) {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($category) . "%"]);
                })
                ->orWhereRaw('LOWER(name) LIKE ?', ["%" . strtolower($category) . "%"]);
            })
            ->orWhereHas('getProducts', function ($sub) use ($category) {
                $sub->whereRaw('LOWER(name) LIKE ? ', ["%" . strtolower($category) . "%"])
                ->orWhereHas('tags', function ($child) use ($category) {
                    $child->whereRaw('LOWER(tag_name) LIKE ?', ["%" . strtolower($category) . "%"]);
                });
            });
        })
        ->with(['getProducts.tags', 'store_categories.categories'])
        ->get();
    }

    /**
     * Search stores by keyword across multiple fields:
     * store name, product name, category, menu category, and product tags.
     *
     * @param string $keyword
     * @param float|null $lat
     * @param float|null $lng
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchStores($keyword, $lat = null, $lng = null)
    {
        $search = strtolower(trim($keyword));

        $query = Store::query();

        // Add distance calculation if coordinates are provided
        if ($lat && $lng) {
            $query->selectRaw("*, 
                ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) 
                * cos( radians( longitude ) - radians(?) ) 
                + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS jarak", [$lat, $lng, $lat]);
        }

        $query->where(function ($q) use ($search) {
            // Search by store name
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])

            // Search by category (through store_categories -> categories)
            ->orWhereHas('store_categories', function ($sub) use ($search) {
                $sub->whereHas('categories', function ($catQuery) use ($search) {
                    $catQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            })

            // Search by menu category name
            ->orWhereHas('menuCategories', function ($sub) use ($search) {
                $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            })

            // Search by product name or product tags
            ->orWhereHas('getProducts', function ($sub) use ($search) {
                $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->whereRaw('LOWER(tag_name) LIKE ?', ["%{$search}%"]);
                    });
            });
        });

        $query->with(['getProducts.tags', 'store_categories.categories', 'menuCategories']);

        // Sort by distance if coordinates provided
        if ($lat && $lng) {
            $query->orderBy('jarak', 'asc');
        }

        return $query->get();
    }

    /**
     * Get store by slug with menu categories and products.
     *
     * @param string $slug
     * @return Store|null
     */
    public function getStoreBySlug($slug)
    {
        return Store::where('slug', $slug)
            ->with(['menuCategories' => function ($query) {
                $query->where('is_active', 1)->orderBy('display_order', 'asc');
            }, 'menuCategories.products' => function ($query) {
                $query->where('is_available', 1);
            }])
            ->first();
    }
}
