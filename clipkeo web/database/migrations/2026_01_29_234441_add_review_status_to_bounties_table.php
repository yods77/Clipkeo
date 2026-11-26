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
        Schema::table('bounties', function (Blueprint $table) {
            // 1. Tambah kolom alasan penolakan
            $table->text('rejection_reason')->nullable()->after('status');
        });

        // 2. Update ENUM:
        // - Menambah: 'pending_review', 'rejected'
        // - Menghapus: 'judging' (sesuai request)
        // - Tetap ada: 'draft', 'active', 'completed', 'canceled'
        
        DB::statement("ALTER TABLE bounties MODIFY COLUMN status ENUM('draft', 'pending_review', 'active', 'rejected', 'completed', 'canceled') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum awal (termasuk judging jika mau rollback)
        DB::statement("ALTER TABLE bounties MODIFY COLUMN status ENUM('draft', 'active', 'judging', 'completed', 'canceled') NOT NULL DEFAULT 'draft'");

        Schema::table('bounties', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
