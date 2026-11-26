<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom untuk menyimpan skor total (buat ranking 1, 2, 3)
            $table->unsignedBigInteger('reputation_points')->default(0)->after('avatar_url');

            // Kolom Tier (Bronze, Silver, Gold)
            $table->string('tier_level')->default('Bronze')->after('reputation_points');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reputation_points', 'tier_level']);
        });
    }
};
