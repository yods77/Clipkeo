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
        // Menggunakan text() alih-alih json() agar data lama tidak error
        $table->text('platform')->nullable()->change();
    });
}

public function down()
{
    Schema::table('bounties', function (Blueprint $table) {
        $table->string('platform')->change();
    });
}
};
