<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('withdrawal_request_id')
                ->nullable()
                ->unique('transactions_withdrawal_request_id_unique')
                ->constrained('withdrawal_requests')
                ->nullOnDelete();
        });

        // MySQL stores this column as ENUM. SQLite (used by the test suite)
        // stores it as a string, so it needs no alteration.
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE transactions MODIFY type ENUM(
                    'deposit',
                    'withdrawal',
                    'bounty_hold',
                    'bounty_prize',
                    'bounty_refund',
                    'platform_fee',
                    'withdrawal_refund'
                ) NOT NULL"
            );
        }

        // Attach the best matching legacy withdrawal ledger row where possible.
        // Prefer a row that already has an Xendit disbursement ID.
        DB::table('withdrawal_requests')
            ->orderBy('id')
            ->chunkById(100, function ($withdrawals) {
                foreach ($withdrawals as $withdrawal) {
                    $id = (int) $withdrawal->id;

                    $legacyTransaction = DB::table('transactions')
                        ->where('user_id', $withdrawal->user_id)
                        ->where('type', 'withdrawal')
                        ->whereNull('withdrawal_request_id')
                        ->where(function ($query) use ($id) {
                            $query->where('reference_id', 'WD-' . $id)
                                ->orWhere('reference_id', 'like', 'WD-' . $id . '-%')
                                ->orWhere('reference_id', 'like', 'WD-%-' . $id)
                                ->orWhere('reference_id', 'like', 'WD-REJECT-' . $id . '%');
                        })
                        ->orderByRaw('CASE WHEN external_ref IS NOT NULL THEN 0 ELSE 1 END')
                        ->orderByDesc('id')
                        ->first();

                    if ($legacyTransaction) {
                        DB::table('transactions')
                            ->where('id', $legacyTransaction->id)
                            ->update(['withdrawal_request_id' => $id]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $hasRefundRows = DB::table('transactions')
                ->where('type', 'withdrawal_refund')
                ->exists();

            if ($hasRefundRows) {
                throw new RuntimeException(
                    'Cannot remove withdrawal_refund while ledger rows of that type still exist.'
                );
            }

            DB::statement(
                "ALTER TABLE transactions MODIFY type ENUM(
                    'deposit',
                    'withdrawal',
                    'bounty_hold',
                    'bounty_prize',
                    'bounty_refund',
                    'platform_fee'
                ) NOT NULL"
            );
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['withdrawal_request_id']);
            $table->dropUnique('transactions_withdrawal_request_id_unique');
            $table->dropColumn('withdrawal_request_id');
        });
    }
};
