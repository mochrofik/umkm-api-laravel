<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class KabupatenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $region = Region::where('level', 1)->get();
        $baseUrl = 'https://www.emsifa.com/api-wilayah-indonesia/api';

        foreach ($region as $key => $value) {
            $this->command->info("  Processing: {$value->name}");

            $regencies = Http::timeout(3000)->get("$baseUrl/regencies/{$value->code}.json")->json();

            foreach ($regencies as $key => $regency) {
                Region::updateOrCreate(
                    ['code' => $regency['id']],
                    [
                        'name' => $regency['name'],
                        'level' => 2,
                        'parent_code' => $value->code,
                    ]
                );
            }
        }
    }
}
