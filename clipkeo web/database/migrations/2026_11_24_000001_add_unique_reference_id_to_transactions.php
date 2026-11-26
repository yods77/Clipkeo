
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AddUniqueReferenceIdToTransactions extends Migration
{
    public function up(): void
    {
        // Periksa apakah ada reference_id yang duplikat.
        $duplicates = DB::table('transactions')
            ->select('reference_id')
            ->whereNotNull('reference_id')
            ->groupBy('reference_id')
            ->havingRaw('COUNT(*) > 1')
            ->limit(10)
            ->pluck('reference_id')
            ->all();

        // Hentikan migration jika ditemukan duplikasi.
        if (!empty($duplicates)) {
            throw new \RuntimeException(
                'Migration dibatalkan. Ditemukan reference_id duplikat: '
                . implode(', ', $duplicates)
                . '. Periksa data sebelum menjalankan migration.'
            );
        }

        // Tambahkan unique index.
        Schema::table('transactions', function (Blueprint $table) {
            $table->unique(
                'reference_id',
                'transactions_reference_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(
                'transactions_reference_id_unique'
            );
        });
    }
}
