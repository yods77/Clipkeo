<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some deployed schemas already contain this column, while the original
        // create migration does not. Keep this migration safe for both cases.
        if (Schema::hasColumn('bounties', 'remaining_budget')) {
            return;
        }

        Schema::table('bounties', function (Blueprint $table) {
            $table->decimal('remaining_budget', 15, 2)
                ->default(0)
                ->after('min_views');
        });

        // Existing campaigns start with their full reward budget available.
        DB::table('bounties')->update([
            'remaining_budget' => DB::raw('reward_amount'),
        ]);
    }

    public function down(): void
    {
        // Intentionally keep this column: it may have existed before this
        // compatibility migration was applied to an already-deployed database.
    }
};
