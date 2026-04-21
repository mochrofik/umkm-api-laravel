<?php

namespace App\Services;

use App\Models\Store;

class StoreService
{
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
