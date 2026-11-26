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
        // Kita tambahkan kolom bank_info untuk menyimpan detail rekening saat request dibuat
        $table->string('bank_info')->nullable()->after('amount_final'); 
    });
}

public function down()
{
    Schema::table('withdrawal_requests', function (Blueprint $table) {
        $table->dropColumn('bank_info');
    });
}
};
