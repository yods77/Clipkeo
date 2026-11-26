<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. UPDATE TABEL BOUNTIES (Campaign)
        Schema::table('bounties', function (Blueprint $table) {
            // Menambah kolom 'rate_per_view' untuk sistem CPM (Misal: Rp 2.5)
            // Menggunakan 18,2 agar aman untuk angka desimal kecil
            $table->decimal('rate_per_view', 18, 2)->nullable()->after('remaining_budget')
                ->comment('Harga per view untuk sistem CPM');
        });

        // Mengubah ENUM strategi reward: Hapus top_3 & equal_time, sisakan performance & cpm
        // Kita pakai Raw SQL agar lebih aman dan kompatibel
        DB::statement("ALTER TABLE bounties MODIFY COLUMN distribution_strategy ENUM('performance_tiered', 'cpm') NOT NULL DEFAULT 'performance_tiered'");


        // 2. UPDATE TABEL SUBMISSIONS (Setoran Video)
        Schema::table('submissions', function (Blueprint $table) {
            // Menambah status pembayaran agar tidak bingung antara 'Approved Konten' vs 'Sudah Cair'
            $table->enum('payout_status', ['unpaid', 'paid', 'partial'])->default('unpaid')->after('status')
                ->comment('unpaid=belum cair, paid=lunas, partial=sebagian (untuk update views)');

            // Menambah 'Memori' untuk mencatat views terakhir yang sudah dibayar (Anti-Double Pay)
            $table->bigInteger('last_paid_views')->default(0)->unsigned()->after('views_count')
                ->comment('Menyimpan angka views terakhir yang sudah dicairkan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback tabel submissions
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['payout_status', 'last_paid_views']);
        });

        // Rollback tabel bounties (Kembalikan ke enum lama)
        DB::statement("ALTER TABLE bounties MODIFY COLUMN distribution_strategy ENUM('performance_tiered', 'top_3_tiered', 'equal_time') NOT NULL DEFAULT 'performance_tiered'");
        
        Schema::table('bounties', function (Blueprint $table) {
            $table->dropColumn('rate_per_view');
        });
    }
};