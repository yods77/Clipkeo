<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // 1. Tambahkan dulu 'performance_tiered' ke dalam list ENUM (tapi jangan hapus yang lama dulu)
    // Agar database mengizinkan nilai baru ini masuk
    DB::statement("ALTER TABLE bounties MODIFY COLUMN distribution_strategy ENUM('winner_takes_all', 'performance_tiered', 'top_3_tiered', 'equal_split') DEFAULT 'performance_tiered'");

    // 2. Sekarang baru aman untuk update data lama dari 'winner_takes_all' ke 'performance_tiered'
    DB::table('bounties')
        ->where('distribution_strategy', 'winner_takes_all')
        ->update(['distribution_strategy' => 'performance_tiered']);

    // 3. Terakhir, ubah list ENUM ke versi final (tanpa 'winner_takes_all')
    // Dan pastikan defaultnya adalah 'performance_tiered'
    DB::statement("ALTER TABLE bounties MODIFY COLUMN distribution_strategy ENUM('performance_tiered', 'top_3_tiered', 'equal_split') DEFAULT 'performance_tiered'");
}

public function down()
{
    // Jika ingin balik (Rollback)
    DB::statement("ALTER TABLE bounties MODIFY COLUMN distribution_strategy ENUM('winner_takes_all', 'top_3_tiered', 'equal_split') DEFAULT 'winner_takes_all'");
}
};
