<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'province_id')) {
            $table->char('province_id', 2)->nullable()->after('phone_number');
        }
        
        if (!Schema::hasColumn('users', 'city_id')) {
            $table->char('city_id', 4)->nullable()->after('province_id');
        }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
