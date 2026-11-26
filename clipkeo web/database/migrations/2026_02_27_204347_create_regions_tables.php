<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->char('id', 2)->primary(); // ID dari API (2 digit)
            $table->string('name');
        });

        // Tabel Kota/Kabupaten
        Schema::create('regencies', function (Blueprint $table) {
            $table->char('id', 4)->primary(); // ID dari API (4 digit)
            $table->char('province_id', 2);
            $table->string('name');

            $table->foreign('province_id')->references('id')->on('provinces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regions_tables');
    }
};
