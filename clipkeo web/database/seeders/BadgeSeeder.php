<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeSeeder extends Seeder
{
    public function run()
    {
        // Kosongkan tabel dulu biar gak duplikat
        \Illuminate\Support\Facades\DB::table('badges')->truncate();

        \App\Models\Badge::insert([
            [
                'name' => 'Bronze Clipper',
                'xp_req' => 0,
                'icon_url' => 'badges/bronze_medal.png', //
                'created_at' => now(),
            ],
            [
                'name' => 'Silver Star',
                'xp_req' => 500,
                'icon_url' => 'badges/silver_star.png', //
                'created_at' => now(),
            ],
            [
                'name' => 'Gold Trophy',
                'xp_req' => 2000,
                'icon_url' => 'badges/gold_trophy.png', //
                'created_at' => now(),
            ],
            [
                'name' => 'Diamond King',
                'xp_req' => 5000,
                'icon_url' => 'badges/diamond_crown.png', //
                'created_at' => now(),
            ],
        ]);
    }
}

