<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;

class WithdrawalLedgerService
{
    /**
     * Ensure one withdrawal row and one pending fee row exist for a request.
     * The withdrawal row represents the net bank transfer; the fee row
     * represents the 10% fee. Together they equal amount_requested.
     */
    public function ensurePendingLedger(WithdrawalRequest $withdrawal, ?string $externalId = null): Transaction
    {
        $transaction = $this->findWithdrawalTransaction($withdrawal, $externalId);

        if (!$transaction) {
            $transaction = new Transaction();
            $transaction->reference_id = 'WD-' . $withdrawal->id;
            $transaction->status = 'pending';
        }

        $transaction->fill([
            'user_id' => $withdrawal->user_id,
            'withdrawal_request_id' => $withdrawal->id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'amount' => $withdrawal->amount_final,
            'currency' => 'IDR',
            'description' => 'Penarikan ke rekening tujuan',
            'status' => $transaction->status ?: 'pending',
        ]);
        $transaction->save();

        $fee = $this->findFeeTransaction($withdrawal, $externalId);
        if (!$fee) {
            $fee = new Transaction();
            $fee->reference_id = 'FEE-WD-' . $withdrawal->id;
            $fee->status = 'pending';
        }

        $fee->fill([
            'user_id' => $withdrawal->user_id,
            'type' => Transaction::TYPE_PLATFORM_FEE,
            'amount' => $withdrawal->admin_fee,
            'currency' => 'IDR',
            'description' => 'Biaya admin withdrawal (10%)',
            'status' => $fee->status ?: 'pending',
        ]);
        $fee->save();

        return $transaction;
    }

    /**
     * Settle a withdrawal only once after Xendit confirms completion.
     */
    public function markCompleted(int $withdrawalId, string $externalId, ?string $providerId = null): ?WithdrawalRequest
    {
        return DB::transaction(function () use ($withdrawalId, $externalId, $providerId) {
            $withdrawal = WithdrawalRequest::whereKey($withdrawalId)
                ->lockForUpdate()
                ->first();

            if (!$withdrawal
                || in_array($withdrawal->status, ['completed', 'rejected'], true)
                || (!$allowProcessing && $withdrawal->status !== 'pending')) {
                return null;
            }

            $transaction = $this->findWithdrawalTransaction($withdrawal, $externalId);
            if (!$transaction) {
                $transaction = $this->ensurePendingLedger($withdrawal, $externalId);
            }

            if ($transaction->status === 'failed') {
                return null;
            }

            $transaction->update([
                'withdrawal_request_id' => $withdrawal->id,
                'amount' => $withdrawal->amount_final,
                'status' => 'success',
                'external_ref' => $providerId ?: $transaction->external_ref,
                'paid_at' => now(),
                'description' => 'Withdrawal berhasil ditransfer',
            ]);

            $withdrawal->update(['status' => 'completed']);

            $fee = $this->findFeeTransaction($withdrawal, $externalId);
            if (!$fee) {
                $fee = new Transaction();
                $fee->reference_id = 'FEE-WD-' . $withdrawal->id;
            }

            $fee->fill([
                'user_id' => $withdrawal->user_id,
                'type' => Transaction::TYPE_PLATFORM_FEE,
                'amount' => $withdrawal->admin_fee,
                'currency' => 'IDR',
                'description' => 'Biaya admin withdrawal (10%)',
                'status' => 'success',
            ]);
            $fee->save();

            return $withdrawal->fresh(['user']);
        });
    }

    /**
     * Refund the full gross amount, including the 10% fee, exactly once.
     */
    public function markFailed(int $withdrawalId, string $reason, ?string $externalId = null, bool $allowProcessing = true): ?WithdrawalRequest
    {
        return DB::transaction(function () use ($withdrawalId, $reason, $externalId, $allowProcessing) {
            $withdrawal = WithdrawalRequest::whereKey($withdrawalId)
                ->lockForUpdate()
                ->first();

            if (!$withdrawal || in_array($withdrawal->status, ['completed', 'rejected'], true)) {
                return null;
            }

            $transaction = $this->findWithdrawalTransaction($withdrawal, $externalId);
            if (!$transaction) {
                $transaction = $this->ensurePendingLedger($withdrawal, $externalId);
            }

            if ($transaction->status === 'success') {
                return null;
            }

            $transaction->update([
                'withdrawal_request_id' => $withdrawal->id,
                'amount' => $withdrawal->amount_final,
                'status' => 'failed',
                'description' => 'Withdrawal gagal: ' . $reason,
            ]);

            $fee = $this->findFeeTransaction($withdrawal, $externalId);
            if (!$fee) {
                $fee = new Transaction();
                $fee->reference_id = 'FEE-WD-' . $withdrawal->id;
            }

            $fee->fill([
                'user_id' => $withdrawal->user_id,
                'type' => Transaction::TYPE_PLATFORM_FEE,
                'amount' => $withdrawal->admin_fee,
                'currency' => 'IDR',
                'description' => 'Biaya admin withdrawal dibatalkan karena transfer gagal',
                'status' => 'failed',
            ]);
            $fee->save();

            $user = User::withTrashed()
                ->whereKey($withdrawal->user_id)
                ->lockForUpdate()
                ->first();

            if ($user) {
                $user->increment('wallet_balance', $withdrawal->amount_requested);
            }

            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            Transaction::firstOrCreate(
                ['reference_id' => 'WRF-WD-' . $withdrawal->id],
                [
                    'user_id' => $withdrawal->user_id,
                    'type' => Transaction::TYPE_WITHDRAWAL_REFUND,
                    'amount' => $withdrawal->amount_requested,
                    'currency' => 'IDR',
                    'description' => 'Pengembalian penuh withdrawal yang gagal',
                    'status' => 'success',
                ]
            );

            return $withdrawal->fresh(['user']);
        });
    }

    private function findWithdrawalTransaction(WithdrawalRequest $withdrawal, ?string $externalId = null): ?Transaction
    {
        $transaction = $withdrawal->transaction()->lockForUpdate()->first();
        if ($transaction) {
            return $transaction;
        }

        return Transaction::query()
            ->where('user_id', $withdrawal->user_id)
            ->where('type', Transaction::TYPE_WITHDRAWAL)
            ->where(function ($query) use ($withdrawal, $externalId) {
                $id = (int) $withdrawal->id;
                $query->where('reference_id', 'WD-' . $id)
                    ->orWhere('reference_id', 'like', 'WD-' . $id . '-%')
                    ->orWhere('reference_id', 'like', 'WD-%-' . $id);

                if ($externalId) {
                    $query->orWhere('reference_id', $externalId);
                }
            })
            ->orderByRaw('CASE WHEN external_ref IS NOT NULL THEN 0 ELSE 1 END')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();
    }

    private function findFeeTransaction(WithdrawalRequest $withdrawal, ?string $externalId = null): ?Transaction
    {
        $fee = Transaction::where('reference_id', 'FEE-WD-' . $withdrawal->id)
            ->lockForUpdate()
            ->first();

        if (!$fee && $externalId) {
            $fee = Transaction::where('reference_id', 'FEE-' . $externalId)
                ->lockForUpdate()
                ->first();
        }

        return $fee;
    }
}
