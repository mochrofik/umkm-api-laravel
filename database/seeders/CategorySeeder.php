<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Jajanan & Camilan',
                'icon' => 'snacks.png',
            ],
            [
                'name' => 'Makanan Berat',
                'icon' => 'heavy_meals.png',
            ],
            [
                'name' => 'Minuman Segar',
                'icon' => 'drinks.png',
            ],
            [
                'name' => 'Roti & Kue',
                'icon' => 'bakery.png',
            ],
            [
                'name' => 'Frozen Food',
                'icon' => 'frozen_food.png',
            ],
            [
                'name' => 'Oleh-oleh',
                'icon' => 'souvenirs.png',
            ],
            [
                'name' => 'Bumbu & Bahan Masakan',
                'icon' => 'ingredients.png',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                [
                    'name' => $category['name'],
                ],
                [
                    'icon' => $category['icon'],
                ]
            );
        }
    }
}
