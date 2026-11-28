<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bounties', function (Blueprint $table) {
            $table->decimal('rate_per_view', 18, 2)
                ->nullable()
                ->after('remaining_budget')
                ->comment('Harga per view untuk sistem CPM');
        });

        // Temporarily retain legacy values while the rows are normalized.
        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'performance_tiered',
                'top_3_tiered',
                'equal_split',
                'cpm',
            ])->default('performance_tiered')->change();
        });

        DB::table('bounties')
            ->whereIn('distribution_strategy', ['top_3_tiered', 'equal_split'])
            ->update(['distribution_strategy' => 'performance_tiered']);

        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'performance_tiered',
                'cpm',
            ])->default('performance_tiered')->change();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->enum('payout_status', ['unpaid', 'paid', 'partial'])
                ->default('unpaid')
                ->after('status')
                ->comment('unpaid=belum cair, paid=lunas, partial=sebagian (untuk update views)');

            $table->unsignedBigInteger('last_paid_views')
                ->default(0)
                ->after('views_count')
                ->comment('Menyimpan angka views terakhir yang sudah dicairkan');
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['payout_status', 'last_paid_views']);
        });

        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'performance_tiered',
                'cpm',
                'top_3_tiered',
                'equal_split',
            ])->default('performance_tiered')->change();
        });

        DB::table('bounties')
            ->where('distribution_strategy', 'cpm')
            ->update(['distribution_strategy' => 'performance_tiered']);

        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('distribution_strategy', [
                'performance_tiered',
                'top_3_tiered',
                'equal_split',
            ])->default('performance_tiered')->change();

            $table->dropColumn('rate_per_view');
        });
    }
};
