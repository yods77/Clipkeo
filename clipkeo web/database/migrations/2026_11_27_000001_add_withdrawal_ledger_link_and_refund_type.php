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
                ->constrained('withdrawal_requests')
                ->nullOnDelete();

            $table->unique(
                'withdrawal_request_id',
                'transactions_withdrawal_request_id_unique'
            );
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
                                ->orWhere('reference_id', 'like', 'WD-%-' . $id);

                        })
                        ->orderByRaw('CASE WHEN external_ref IS NOT NULL THEN 0 ELSE 1 END')
                        ->orderByDesc('id')
                        ->first();

                    if ($legacyTransaction) {
                        DB::table('transactions')
                            ->where('id', $legacyTransaction->id)
                            ->update(['withdrawal_request_id' => $id]);
                    }

                    // The legacy controller marked the admin fee as success as
                    // soon as Xendit accepted the request. Reconcile it to the
                    // withdrawal's actual final state so pending fees are not
                    // counted as platform revenue before the bank confirms.
                    $feeStatus = match ($withdrawal->status) {
                        'completed' => 'success',
                        'rejected' => 'failed',
                        default => 'pending',
                    };

                    $feeReferences = ['FEE-WD-' . $id];
                    if ($legacyTransaction) {
                        $feeReferences[] = 'FEE-' . $legacyTransaction->reference_id;
                    }

                    DB::table('transactions')
                        ->where('user_id', $withdrawal->user_id)
                        ->where('type', 'platform_fee')
                        ->whereIn('reference_id', $feeReferences)
                        ->update(['status' => $feeStatus]);
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
