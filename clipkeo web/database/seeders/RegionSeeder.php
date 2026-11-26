<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil Data Provinsi
        $this->command->info('Sedang mengambil data provinsi...');
        $provinces = Http::get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')->json();

        foreach ($provinces as $province) {
            DB::table('provinces')->updateOrInsert(
                ['id' => $province['id']],
                ['name' => $province['name']]
            );

            // 2. Ambil Data Kota/Kabupaten berdasarkan Provinsi ini
            $this->command->info("Mengambil Kota untuk Provinsi: {$province['name']}");
            $regencies = Http::get("https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$province['id']}.json")->json();

            foreach ($regencies as $regency) {
                DB::table('regencies')->updateOrInsert(
                    ['id' => $regency['id']],
                    [
                        'province_id' => $regency['province_id'],
                        'name' => $regency['name']
                    ]
                );
            }
        }

        $this->command->info('Berhasil! Data wilayah Indonesia sudah siap di database.');
    }
}
