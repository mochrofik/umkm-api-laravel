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
            ],
            [
                'name' => 'Makanan Berat',
            ],
            [
                'name' => 'Minuman Segar',
            ],
            [
                'name' => 'Roti & Kue',
            ],
            [
                'name' => 'Frozen Food',
            ],
            [
                'name' => 'Oleh-oleh',
            ],
            [
                'name' => 'Bumbu & Bahan Masakan',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                [
                    'name' => $category['name'],
                ]
            );
        }
    }
}
