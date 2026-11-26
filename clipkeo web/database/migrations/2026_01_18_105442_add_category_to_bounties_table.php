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
    Schema::table('bounties', function (Blueprint $table) {
        // Menambahkan kolom category setelah judul
        // Kita pakai enum biar datanya konsisten, atau string biasa juga boleh.
        // Di sini saya pakai string agar fleksibel jika ada kategori baru nanti.
        $table->string('category')->after('title')->default('Miscellaneous'); 
    });
}

public function down()
{
    Schema::table('bounties', function (Blueprint $table) {
        $table->dropColumn('category');
    });
}
};
