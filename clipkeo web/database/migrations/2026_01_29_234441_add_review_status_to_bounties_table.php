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
            $table->text('rejection_reason')->nullable()->after('status');

            // Allow both old and new statuses until existing rows are migrated.
            $table->enum('status', [
                'draft',
                'active',
                'judging',
                'pending_review',
                'rejected',
                'completed',
                'canceled',
            ])->default('draft')->change();
        });

        DB::table('bounties')
            ->where('status', 'judging')
            ->update(['status' => 'pending_review']);

        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending_review',
                'active',
                'rejected',
                'completed',
                'canceled',
            ])->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending_review',
                'active',
                'rejected',
                'judging',
                'completed',
                'canceled',
            ])->default('draft')->change();
        });

        DB::table('bounties')
            ->where('status', 'pending_review')
            ->update(['status' => 'judging']);

        DB::table('bounties')
            ->where('status', 'rejected')
            ->update(['status' => 'draft']);

        Schema::table('bounties', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'active',
                'judging',
                'completed',
                'canceled',
            ])->default('draft')->change();

            $table->dropColumn('rejection_reason');
        });
    }
};
