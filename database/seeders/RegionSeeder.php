<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Hapus batasan waktu eksekusi PHP
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        // Matikan logging query untuk performa & hemat memori
        DB::connection()->disableQueryLog();

        $baseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';

        $this->command->info('Fetching Provinces...');
        // 2. Tambahkan timeout pada HTTP client
        $response = Http::timeout(60)->get("$baseUrl/provinces.json");

        if ($response->failed()) {
            $this->command->error('Failed to fetch provinces');

            return;
        }

        $provinces = $response->json();

        foreach ($provinces as $province) {
            Region::updateOrCreate(
                ['code' => $province['id']],
                [
                    'name' => $province['name'],
                    'level' => 1,
                    'parent_code' => null,
                ]
            );

            // $this->command->info("  Processing: {$province['name']}");

            // $regencies = Http::timeout(60)->get("$baseUrl/regencies/{$province['id']}.json")->json();

            // foreach ($regencies as $regency) {
            //     Region::updateOrCreate(
            //         ['code' => $regency['id']],
            //         [
            //             'name' => $regency['name'],
            //             'level' => 2,
            //             'parent_code' => $province['id'],
            //         ]
            //     );

            //     $districts = Http::timeout(60)->get("$baseUrl/districts/{$regency['id']}.json")->json();

            //     foreach ($districts as $district) {
            //         Region::updateOrCreate(
            //             ['code' => $district['id']],
            //             [
            //                 'name' => $district['name'],
            //                 'level' => 3,
            //                 'parent_code' => $regency['id'],
            //             ]
            //         );

            //         // 3. OPTIMASI KELURAHAN: Gunakan Upsert (Batch)
            //         $villages = Http::timeout(60)->get("$baseUrl/villages/{$district['id']}.json")->json();

            //         if (!empty($villages)) {
            //             $villageData = [];
            //             foreach ($villages as $village) {
            //                 $villageData[] = [
            //                     'code' => $village['id'],
            //                     'name' => $village['name'],
            //                     'level' => 4,
            //                     'parent_code' => $district['id'],
            //                     'created_at' => now(),
            //                     'updated_at' => now(),
            //                 ];
            //             }

            //             // Gunakan upsert untuk performa tinggi (menyimpan banyak data sekaligus)
            //             Region::upsert($villageData, ['code'], ['name', 'level', 'parent_code', 'updated_at']);
            //         }
            //     }
            // }
        }

        $this->command->info('Region Seeder completed successfully!');
    }
}
