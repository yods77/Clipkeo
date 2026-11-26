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
    Schema::table('payout_methods', function (Blueprint $table) {
        // Menambahkan kolom yang hilang
        $table->string('bank_name')->after('user_id');      // BCA, Mandiri, dll
        $table->string('account_number')->after('bank_name'); // Nomor Rekening
        $table->string('account_name')->after('account_number'); // Atas Nama
        $table->boolean('is_primary')->default(false)->after('account_name'); // Rekening Utama?
    });
}

public function down()
{
    Schema::table('payout_methods', function (Blueprint $table) {
        $table->dropColumn(['bank_name', 'account_number', 'account_name', 'is_primary']);
    });
}
};
