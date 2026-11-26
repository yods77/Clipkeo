<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Temporarily allow both old and new values before migrating existing rows.
        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'winner_takes_all',
                'performance_tiered',
                'top_3_tiered',
                'equal_split',
            ])->default('performance_tiered')->change();
        });

        DB::table('bounties')
            ->where('distribution_strategy', 'winner_takes_all')
            ->update(['distribution_strategy' => 'performance_tiered']);

        // Keep the final enum definition in sync across MySQL and SQLite.
        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'performance_tiered',
                'top_3_tiered',
                'equal_split',
            ])->default('performance_tiered')->change();
        });
    }

    public function down(): void
    {
        // Re-allow the previous value before converting rows back.
        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'winner_takes_all',
                'performance_tiered',
                'top_3_tiered',
                'equal_split',
            ])->default('winner_takes_all')->change();
        });

        DB::table('bounties')
            ->where('distribution_strategy', 'performance_tiered')
            ->update(['distribution_strategy' => 'winner_takes_all']);

        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'winner_takes_all',
                'top_3_tiered',
                'equal_split',
            ])->default('winner_takes_all')->change();
        });
    }
};
