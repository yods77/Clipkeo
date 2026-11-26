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
    Schema::table('transactions', function (Blueprint $table) {
        // Menyimpan URL halaman bayar Xendit (untuk redirect user)
        $table->string('payment_url')->nullable()->after('amount');
        // Menyimpan ID Invoice asli dari Xendit (untuk pencocokan data nanti)
        $table->string('external_ref')->nullable()->after('reference_id');
        // Menyimpan metode bayar (misal: BCA, OVO) setelah user bayar
        $table->string('payment_channel')->nullable()->after('external_ref');
        // Waktu kapan user berhasil bayar
        $table->timestamp('paid_at')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropColumn(['payment_url', 'external_ref', 'payment_channel', 'paid_at']);
    });
}
};
