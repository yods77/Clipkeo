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
    Schema::table('deposits', function (Blueprint $table) {
        // Menambahkan kolom baru setelah kolom 'amount'
        $table->integer('unique_code')->default(0)->after('amount');
        $table->string('proof_image')->nullable()->after('external_ref');
    });
}

public function down()
{
    Schema::table('deposits', function (Blueprint $table) {
        $table->dropColumn(['unique_code', 'proof_image']);
    });
}
};
