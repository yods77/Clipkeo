<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\AdminActivityNotification;
use App\Notifications\FinancialStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WithdrawalLedgerService;

class WebhookController extends Controller
{
    /**
     * ============================================================================
     * HANDLE INCOMING WEBHOOK (PINTU GERBANG UTAMA)
     * ============================================================================
     * Fungsi ini bertugas menerima semua sinyal yang dikirim oleh Xendit.
     * Tugasnya:
     * 1. Memvalidasi keamanan (Token).
     * 2. Memilah apakah ini transaksi Withdraw (WD) atau Deposit (DEP).
     * * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request)
    {
        // [LANGKAH 1] Validasi Token Keamanan
        // Memastikan yang mengetuk pintu adalah Xendit asli, bukan hacker.
        
        $xenditXCallbackToken = $request->header('x-callback-token');

        $myCallbackToken = (string) config(
            'services.xendit.verification_token'
        );

    if (
    $myCallbackToken === '' ||
    !is_string($xenditXCallbackToken) ||
    $xenditXCallbackToken === '' ||
    !hash_equals($myCallbackToken, $xenditXCallbackToken)
    ) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }


        // [LANGKAH 2] Ambil Data Transaksi
        $data = $request->all();
        $externalId = $data['external_id'] ?? '';
        $status = $data['status'] ?? '';

        if (!is_string($externalId) || $externalId === '' || !is_string($status)) {
            return response()->json(['message' => 'Invalid webhook payload'], 200);
        }

        // Log untuk monitoring di storage/logs/laravel.log
        Log::info('Webhook Xendit Masuk:', ['id' => $externalId, 'status' => $status]);

        // [LANGKAH 3] Arahkan ke Fungsi yang Tepat
        
        // Skenario A: Penarikan Dana (Withdrawal) -> Kode 'WD-'
        if (str_starts_with($externalId, 'WD-')) {
            return $this->handleWithdrawal($externalId, $status, $data);
        }

        // Skenario B: Pemasukan Dana (Deposit) -> Kode 'DEP-'
        if (str_starts_with($externalId, 'DEP-')) {
            return $this->handleDeposit($externalId, $status, $data);
        }

        return response()->json(['message' => 'Ignored - Kode tidak dikenali'], 200);
    }

    /**
     * ============================================================================
     * PRIVATE: HANDLE WITHDRAWAL (LOGIKA PENCAIRAN DANA)
     * ============================================================================
     * Menangani logika saat uang keluar dari rekening perusahaan ke user.
     * - Jika SUKSES: Tandai selesai, kirim notifikasi.
     * - Jika GAGAL:  Lakukan REFUND (Pengembalian Saldo) otomatis.
     */
    private function handleWithdrawal($externalId, $status, $data)
    {
        $withdrawalId = null;

        // New references use WD-{withdrawal_id}; legacy references used
        // WD-{timestamp}-{withdrawal_id}. Both formats remain supported.
        if (preg_match('/^WD-(\\d+)$/', (string) $externalId, $matches)) {
            $withdrawalId = (int) $matches[1];
        } elseif (preg_match('/^WD-[A-Za-z0-9_-]+-(\\d+)$/', (string) $externalId, $matches)) {
            // Current environment-prefixed IDs and legacy WD-{timestamp}-{id}.
            $withdrawalId = (int) $matches[1];
        }

        if (!$withdrawalId) {
            Log::warning('Webhook withdrawal diabaikan karena external_id tidak valid.', [
                'external_id' => $externalId,
            ]);

            return response()->json(['message' => 'Invalid withdrawal reference'], 200);
        }

        $withdrawalRecord = WithdrawalRequest::with('transaction')->find($withdrawalId);
        if (!$withdrawalRecord) {
            Log::warning('Webhook withdrawal merujuk request yang tidak ditemukan.', [
                'withdrawal_id' => $withdrawalId,
                'external_id' => $externalId,
            ]);

            return response()->json(['message' => 'Withdrawal not found'], 200);
        }

        $storedExternalId = $withdrawalRecord->transaction?->reference_id;
        $appEnvironment = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) config('app.env', 'production'));
        $currentExternalId = 'WD-' . $appEnvironment . '-' . $withdrawalId;
        $legacySimpleExternalId = 'WD-' . $withdrawalId;

        // Only the saved reference or this environment's deterministic ID may
        // settle a withdrawal. This blocks cross-environment ID collisions.
        if ($externalId !== $storedExternalId
            && $externalId !== $currentExternalId
            && $externalId !== $legacySimpleExternalId) {
            Log::critical('Webhook withdrawal ditolak karena referensinya tidak cocok.', [
                'withdrawal_id' => $withdrawalId,
                'external_id' => $externalId,
                'stored_external_id' => $storedExternalId,
                'expected_external_id' => $currentExternalId,
            ]);

            return response()->json(['message' => 'Withdrawal reference mismatch'], 200);
        }

        $ledger = app(WithdrawalLedgerService::class);
        $providerId = $data['id'] ?? $data['disbursement_id'] ?? null;

        if ($status === 'COMPLETED') {
            $withdrawal = $ledger->markCompleted(
                $withdrawalId,
                (string) $externalId,
                $providerId ? (string) $providerId : null
            );

            if ($withdrawal) {
                try {
                    $withdrawal->user?->notify(
                        new FinancialStatusNotification($withdrawal, 'withdrawal', 'success')
                    );
                } catch (\Throwable $e) {
                    Log::warning('Gagal mengirim notifikasi withdrawal berhasil.', [
                        'withdrawal_id' => $withdrawalId,
                        'exception' => $e->getMessage(),
                    ]);
                }

                Log::info('Withdrawal selesai dikonfirmasi Xendit.', [
                    'withdrawal_id' => $withdrawalId,
                    'external_id' => $externalId,
                ]);
            } elseif ($withdrawalRecord->status !== 'completed') {
                // A late success after a refund may mean the recipient was paid
                // while the wallet was already credited back. Do not debit again
                // automatically; flag it for manual financial reconciliation.
                Log::critical('Xendit melaporkan withdrawal berhasil setelah status lokal mencapai kondisi terminal non-completed.', [
                    'withdrawal_id' => $withdrawalId,
                    'external_id' => $externalId,
                    'local_status' => $withdrawalRecord->status,
                    'provider_id' => $providerId,
                ]);
            }
        } elseif ($status === 'FAILED') {
            $failureCode = (string) ($data['failure_code'] ?? 'BANK_ERROR');

            $withdrawal = $ledger->markFailed(
                $withdrawalId,
                'Bank Error: ' . $failureCode,
                (string) $externalId
            );

            if ($withdrawal) {
                try {
                    $withdrawal->user?->notify(new FinancialStatusNotification(
                        $withdrawal,
                        'withdrawal',
                        'rejected',
                        'Transfer gagal (' . $failureCode . '). Seluruh nominal termasuk biaya admin telah dikembalikan.'
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Gagal mengirim notifikasi refund withdrawal.', [
                        'withdrawal_id' => $withdrawalId,
                        'exception' => $e->getMessage(),
                    ]);
                }

                Log::warning('Withdrawal gagal; pengembalian penuh telah dicatat.', [
                    'withdrawal_id' => $withdrawalId,
                    'external_id' => $externalId,
                    'failure_code' => $failureCode,
                ]);
            } elseif ($withdrawalRecord->status === 'completed') {
                Log::critical('Xendit melaporkan withdrawal FAILED setelah ledger lokal COMPLETED.', [
                    'withdrawal_id' => $withdrawalId,
                    'external_id' => $externalId,
                    'provider_id' => $providerId,
                ]);
            }
        }

        return response()->json(['message' => 'Withdrawal Processed'], 200);
    }

    private function handleDeposit($externalId, $status, $data)
    {
        // --------------------------------------------------------------------
        // KONDISI 1: PEMBAYARAN DITERIMA (PAID / SETTLED)
        // --------------------------------------------------------------------
        if ($status === 'PAID' || $status === 'SETTLED') {
            $processedTransaction = DB::transaction(function () use ($externalId, $data) {
                // Kunci baris transaksi agar webhook berulang yang datang
                // bersamaan tidak dapat menambah saldo lebih dari satu kali.
                $transaction = Transaction::where('reference_id', $externalId)
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    Log::warning('Webhook deposit tidak menemukan transaksi lokal.', [
                        'external_id' => $externalId,
                    ]);

                    return null;
                }

                // Hanya transisi pending -> success yang boleh memproses saldo
                // dan membuat notifikasi. Callback duplikat diabaikan.
                if ($transaction->status === 'success') {
                    Log::info('Webhook deposit duplikat diabaikan.', [
                        'external_id' => $externalId,
                    ]);

                    return null;
                }

                if ($transaction->status !== 'pending') {
                    Log::warning('Webhook deposit diabaikan karena status transaksi bukan pending.', [
                        'external_id' => $externalId,
                        'local_status' => $transaction->status,
                    ]);

                    return null;
                }

                $user = User::whereKey($transaction->user_id)
                    ->lockForUpdate()
                    ->first();

                if (!$user) {
                    Log::error('Webhook deposit tidak dapat diproses karena pemilik transaksi tidak ditemukan.', [
                        'external_id' => $externalId,
                        'user_id' => $transaction->user_id,
                    ]);

                    // Rollback status transaksi agar Xendit dapat mencoba
                    // mengirim webhook kembali setelah masalah diperbaiki.
                    throw new \RuntimeException('Deposit owner not found.');
                }

                $paymentMethod = $data['payment_method'] ?? 'Xendit';
                if (!is_string($paymentMethod) || $paymentMethod === '') {
                    $paymentMethod = 'Xendit';
                }

                // A. Tandai transaksi sebagai berhasil.
                $transaction->update([
                    'status' => 'success',
                    'payment_channel' => $paymentMethod,
                    'paid_at' => now(),
                    'description' => 'Deposit Berhasil (Auto) via ' . $paymentMethod,
                ]);

                // B. Tambahkan saldo publisher tepat satu kali.
                $user->increment('wallet_balance', $transaction->amount);

                // Muat relasi user agar notifikasi admin dapat menampilkan
                // identitas publisher tanpa query tambahan yang ambigu.
                $transaction->setRelation('user', $user);

                // C. Simpan notifikasi internal untuk seluruh akun admin.
                // Notifikasi database berada dalam transaksi DB yang sama,
                // sehingga status deposit, saldo, dan notifikasi konsisten.
                $admins = User::where('role', 'admin')->get();

                foreach ($admins as $admin) {
                    $admin->notify(new AdminActivityNotification($transaction, 'deposit_success'));
                }

                if ($admins->isEmpty()) {
                    Log::warning('Deposit berhasil, tetapi tidak ada akun admin aktif untuk menerima notifikasi.', [
                        'external_id' => $externalId,
                    ]);
                }

                // D. Pertahankan notifikasi sukses untuk publisher.
                try {
                    $user->notify(new FinancialStatusNotification($transaction, 'deposit', 'success'));
                } catch (\Throwable $e) {
                    Log::error('Gagal mengirim notifikasi deposit kepada publisher.', [
                        'external_id' => $externalId,
                        'exception' => $e->getMessage(),
                    ]);
                }

                return $transaction;
            });

            if ($processedTransaction) {
                Log::info('Deposit berhasil dikonfirmasi Xendit dan notifikasi admin dibuat.', [
                    'external_id' => $externalId,
                    'transaction_id' => $processedTransaction->id,
                    'user_id' => $processedTransaction->user_id,
                    'amount' => $processedTransaction->amount,
                ]);
            }
        }

        // --------------------------------------------------------------------
        // KONDISI 2: PEMBAYARAN KEDALUWARSA (EXPIRED)
        // --------------------------------------------------------------------
        if ($status === 'EXPIRED') {
            $transaction = Transaction::where('reference_id', $externalId)->first();

            if ($transaction && $transaction->status === 'pending') {
                $transaction->update([
                    'status' => 'failed',
                    'description' => 'Pembayaran Expired',
                ]);
            }
        }

        return response()->json(['message' => 'Deposit Processed'], 200);
    }
}