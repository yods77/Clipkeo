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
    Schema::table('withdrawal_requests', function (Blueprint $table) {
        // 1. Hapus Foreign Key yang salah arah (sesuai nama error kamu)
        $table->dropForeign('withdrawal_requests_payout_method_id_foreign');

        // 2. Pasang Foreign Key baru ke tabel 'payout_methods'
        $table->foreign('payout_method_id')
              ->references('id')
              ->on('payout_methods')
              ->onDelete('cascade'); // Jika user hapus rekening, history request ikut terhapus (atau bisa diset null)
    });
}

public function down()
{
    // Cuma buat jaga-jaga kalau mau rollback (biasanya jarang dipakai di tahap ini)
    Schema::table('withdrawal_requests', function (Blueprint $table) {
        $table->dropForeign(['payout_method_id']);
    });
}
};
