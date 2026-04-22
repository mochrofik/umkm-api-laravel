<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuCategories;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        // Titik awal di Kecamatan Kamal, Bangkalan, Madura
        $baseLat = -7.1685;
        $baseLng = 112.7153;
        // ~5 meter = ~0.000045 derajat latitude
        $offsetLat = 0.000045;
        $offsetLng = 0.000050;

        $categories = Category::all();

        $stores = [
            ['name' => 'Warung Sego Pecel Madura',   'cat' => 'Makanan Berat',          'desc' => 'Warung pecel khas Madura dengan sambal kacang pilihan, sajian hangat setiap hari.'],
            ['name' => 'Kedai Jajanan Bu Siti',       'cat' => 'Jajanan & Camilan',      'desc' => 'Aneka jajanan tradisional khas Madura, cocok untuk oleh-oleh dan camilan sehari-hari.'],
            // ['name' => 'Es Campur Pak Hasan',         'cat' => 'Minuman Segar',           'desc' => 'Es campur segar dengan aneka topping buah, cendol, dan santan gurih.'],
            // ['name' => 'Roti Bakar Kamal',            'cat' => 'Roti & Kue',              'desc' => 'Roti bakar dengan berbagai varian rasa, dari cokelat hingga keju spesial.'],
            // ['name' => 'Frozen Mantap Kamal',         'cat' => 'Frozen Food',             'desc' => 'Aneka frozen food siap goreng dan siap masak untuk keluarga.'],
            // ['name' => 'Toko Oleh-oleh Madura Jaya',  'cat' => 'Oleh-oleh',              'desc' => 'Pusat oleh-oleh khas Madura, dari kerupuk hingga petis terbaik.'],
            // ['name' => 'Bumbu Dapur Nusantara',       'cat' => 'Bumbu & Bahan Masakan',   'desc' => 'Menyediakan bumbu dapur lengkap dan rempah pilihan khas Nusantara.'],
            // ['name' => 'Nasi Bebek Cak Durasim',      'cat' => 'Makanan Berat',          'desc' => 'Nasi bebek goreng bumbu hitam khas Madura, pedas dan gurih.'],
            // ['name' => 'Cemilan Renyah Kamal',        'cat' => 'Jajanan & Camilan',      'desc' => 'Keripik, kerupuk, dan camilan renyah produksi rumahan berkualitas.'],
            // ['name' => 'Juice & Boba Segar',          'cat' => 'Minuman Segar',           'desc' => 'Aneka juice buah segar dan minuman boba kekinian.'],
            // ['name' => 'Kue Tradisional Mak Ijah',    'cat' => 'Roti & Kue',              'desc' => 'Kue tradisional seperti klepon, onde-onde, dan lapis legit buatan rumahan.'],
            // ['name' => 'Frozen Delight Madura',       'cat' => 'Frozen Food',             'desc' => 'Nugget, sosis, dan dimsum frozen berkualitas harga terjangkau.'],
            // ['name' => 'Sate Ayam Cak Bari',          'cat' => 'Makanan Berat',          'desc' => 'Sate ayam bumbu kacang khas Madura, dibakar arang kelapa.'],
            // ['name' => 'Oleh-oleh Suramadu',          'cat' => 'Oleh-oleh',              'desc' => 'Oleh-oleh khas jembatan Suramadu, snack dan kerajinan Madura.'],
            // ['name' => 'Warung Rawon Bu Ning',        'cat' => 'Makanan Berat',          'desc' => 'Rawon khas Jawa Timur dengan kuah hitam pekat dan daging empuk.'],
            // ['name' => 'Depot Bakso Pak Joko',        'cat' => 'Makanan Berat',          'desc' => 'Bakso urat jumbo dan bakso telur dengan kuah kaldu sapi asli.'],
            // ['name' => 'Rempah Madura Asli',          'cat' => 'Bumbu & Bahan Masakan',   'desc' => 'Rempah-rempah asli Madura, petis, dan bumbu racikan tradisional.'],
            // ['name' => 'Es Dawet Ayu Kamal',          'cat' => 'Minuman Segar',           'desc' => 'Es dawet ayu dengan cendol pandan, gula merah, dan santan segar.'],
            // ['name' => 'Martabak Manis Kamal',        'cat' => 'Roti & Kue',              'desc' => 'Martabak manis tebal dengan topping cokelat, keju, kacang, dan wijen.'],
            // ['name' => 'Keripik Singkong Bu Ani',     'cat' => 'Jajanan & Camilan',      'desc' => 'Keripik singkong aneka rasa, dari original hingga balado pedas.'],
        ];

        // Produk berdasarkan kategori
        $productsByCategory = [
            'Makanan Berat' => [
                ['Nasi Goreng Spesial', 18000], ['Mie Ayam Bakso', 15000], ['Nasi Campur', 20000],
                ['Soto Ayam', 15000], ['Gado-gado', 13000], ['Nasi Pecel', 12000],
                ['Lontong Sayur', 14000], ['Nasi Uduk Komplit', 16000], ['Rawon', 22000], ['Sate Ayam 10 Tusuk', 25000],
            ],
            'Jajanan & Camilan' => [
                ['Keripik Tempe', 10000], ['Kerupuk Ikan', 8000], ['Kacang Goreng Bawang', 12000],
                ['Rempeyek Kacang', 10000], ['Pisang Goreng', 5000], ['Tahu Crispy', 8000],
                ['Cireng Isi', 10000], ['Lumpia Goreng', 12000], ['Bakwan Jagung', 5000], ['Risol Mayo', 7000],
            ],
            // 'Minuman Segar' => [
            //     ['Es Teh Manis', 5000], ['Es Jeruk Peras', 8000], ['Es Campur', 12000],
            //     ['Jus Alpukat', 15000], ['Jus Mangga', 12000], ['Es Dawet', 8000],
            //     ['Es Cendol', 8000], ['Lemon Tea', 10000], ['Boba Brown Sugar', 15000], ['Es Kelapa Muda', 10000],
            // ],
            // 'Roti & Kue' => [
            //     ['Roti Bakar Coklat', 12000], ['Roti Bakar Keju', 14000], ['Martabak Manis', 25000],
            //     ['Klepon', 5000], ['Onde-onde', 5000], ['Kue Lapis', 15000],
            //     ['Donat Gula', 5000], ['Brownies', 18000], ['Bolu Kukus', 8000], ['Pukis', 8000],
            // ],
            // 'Frozen Food' => [
            //     ['Nugget Ayam 500gr', 28000], ['Sosis Sapi 500gr', 25000], ['Dimsum Ayam', 22000],
            //     ['Bakso Sapi Frozen', 30000], ['Kentang Goreng 1kg', 35000], ['Cireng Frozen', 15000],
            //     ['Risol Frozen', 18000], ['Lumpia Frozen', 20000], ['Pangsit Goreng Frozen', 15000], ['Tempura Udang', 32000],
            // ],
            // 'Oleh-oleh' => [
            //     ['Kerupuk Terasi Madura', 15000], ['Petis Madura', 12000], ['Gula Siwalan', 20000],
            //     ['Rengginang', 18000], ['Dodol Madura', 22000], ['Sambal Petis', 15000],
            //     ['Keripik Paru', 25000], ['Terasi Madura', 10000], ['Garam Madura', 8000], ['Abon Sapi', 30000],
            // ],
            // 'Bumbu & Bahan Masakan' => [
            //     ['Bumbu Rawon', 10000], ['Bumbu Soto', 8000], ['Bumbu Rendang', 12000],
            //     ['Petis Udang', 15000], ['Kemiri 250gr', 10000], ['Kunyit Bubuk', 8000],
            //     ['Lada Hitam 100gr', 12000], ['Lengkuas Bubuk', 8000], ['Jahe Merah Bubuk', 10000], ['Bumbu Pecel', 10000],
            // ],
        ];

        $menuCatTemplates = [
            'Makanan Berat'         => ['Menu Utama', 'Menu Sarapan', 'Menu Spesial'],
            'Jajanan & Camilan'     => ['Camilan Kering', 'Camilan Basah', 'Gorengan'],
            // 'Minuman Segar'         => ['Es Segar', 'Jus Buah', 'Minuman Kekinian'],
            // 'Roti & Kue'            => ['Roti', 'Kue Basah', 'Kue Kering'],
            // 'Frozen Food'           => ['Frozen Ayam', 'Frozen Sapi', 'Frozen Camilan'],
            // 'Oleh-oleh'             => ['Oleh-oleh Kering', 'Oleh-oleh Basah', 'Bumbu Khas'],
            // 'Bumbu & Bahan Masakan' => ['Bumbu Instan', 'Rempah Kering', 'Bahan Segar'],
        ];

        $password = Hash::make('12345678');

        foreach ($stores as $i => $storeData) {
            // 1. Create User
            $user = User::create([
                'name'     => $storeData['name'],
                'email'    => 'store' . ($i + 1) . '@umkm.test',
                'password' => $password,
                'role'     => 'store',
                'status'   => 'active',
            ]);
            $user->assignRole('store');

            // 2. Create Store
            $lat = $baseLat + ($offsetLat * $i);
            $lng = $baseLng + ($offsetLng * ($i % 5));

            $store = Store::create([
                'user_id'      => $user->id,
                'name'         => $storeData['name'],
                'slug'         => Str::slug($storeData['name']) . '-' . ($i + 1),
                'address'      => 'Jl. Raya Kamal No. ' . ($i + 1) . ', Kec. Kamal, Kab. Bangkalan, Madura',
                'phone_number' => '0812000000' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'description'  => $storeData['desc'],
                'latitude'     => $lat,
                'longitude'    => $lng,
                'logo'         => null,
                'open_at'      => '08:00',
                'close_at'     => '21:00',
                'is_open'      => true,
                'rating'       => 0,
            ]);

            // 3. Assign store_category
            $cat = $categories->where('name', $storeData['cat'])->first();
            if ($cat) {
                StoreCategory::create([
                    'store_id'    => $store->id,
                    'category_id' => $cat->id,
                ]);
            }

            // 4. Create menu_categories
            $catName = $storeData['cat'];
            $menuNames = $menuCatTemplates[$catName] ?? ['Menu 1', 'Menu 2', 'Menu 3'];
            $menuCats = [];
            foreach ($menuNames as $order => $menuName) {
                $mc = MenuCategories::create([
                    'store_id'      => $store->id,
                    'name'          => $menuName,
                    'description'   => 'Kategori ' . $menuName . ' untuk ' . $store->name,
                    'display_order' => $order + 1,
                    'is_active'     => true,
                ]);
                $menuCats[] = $mc;
            }

            // 5. Create 10 products spread across menu categories
            $products = $productsByCategory[$catName] ?? [];
            $now = now();
            foreach ($products as $j => $pData) {
                $mc = $menuCats[$j % count($menuCats)];
                $productId = DB::table('products')->insertGetId([
                    'store_id'         => $store->id,
                    'menu_category_id' => $mc->id,
                    'name'             => $pData[0],
                    'price'            => $pData[1],
                    'description'      => $pData[0] . ' dari ' . $store->name . '. Dibuat fresh setiap hari.',
                    'image_url'        => null,
                    'stock'            => rand(10, 100),
                    'is_available'     => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                DB::table('product_tags')->insert([
                    'product_id' => $productId,
                    'tag_name'   => strtolower(str_replace(' ', '-', $catName)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
