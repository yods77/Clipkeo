<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Drop by column so the migration also works with SQLite.
            $table->dropForeign(['payout_method_id']);

            $table->foreign('payout_method_id')
                ->references('id')
                ->on('payout_methods')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['payout_method_id']);

            // Restore the original relationship from the initial schema.
            $table->foreign('payout_method_id')
                ->references('id')
                ->on('user_payout_methods')
                ->restrictOnDelete();
        });
    }
};
