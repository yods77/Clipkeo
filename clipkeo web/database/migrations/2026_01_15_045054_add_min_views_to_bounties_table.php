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
        // Menambahkan kolom min_views setelah reward_amount
        $table->unsignedBigInteger('min_views')->default(10000)->after('reward_amount');
    });
}

public function down()
{
    Schema::table('bounties', function (Blueprint $table) {
        $table->dropColumn('min_views');
    });
}
};
