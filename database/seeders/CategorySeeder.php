<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Jajanan & Camilan'],
            ['name' => 'Makanan Berat'],
            ['name' => 'Minuman Segar'],
            ['name' => 'Roti & Kue'],
            ['name' => 'Frozen Food'],
            ['name' => 'Oleh-oleh'],
            ['name' => 'Bumbu & Bahan Masakan'],
        ];

        $targetDir = storage_path('app/public/uploads/categories');
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        foreach ($categories as $category) {
            $slug = Str::slug($category['name']);
            $iconName = $slug . '.png';
            $sourcePath = resource_path("assets/icon/{$iconName}");

            if (File::exists($sourcePath)) {
                File::copy($sourcePath, $targetDir . '/' . $iconName);
            }

            Category::updateOrCreate(
                [
                    'name' => $category['name'],
                ],
                [
                    'icon' => $iconName,
                ]
            );
        }
    }
}
