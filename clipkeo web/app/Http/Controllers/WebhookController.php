<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
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
            DB::transaction(function () use ($externalId, $data) {
                $transaction = Transaction::where('reference_id', $externalId)->first();

                // Cek Idempotency: Pastikan saldo tidak ditambah dua kali
                if ($transaction && $transaction->status !== 'success') {
                    
                    // A. Update Status Transaksi jadi Success
                    $transaction->update([
                        'status' => 'success',
                        'payment_channel' => $data['payment_method'] ?? 'Xendit',
                        'paid_at' => now(),
                        'description' => 'Deposit Berhasil (Auto) via ' . ($data['payment_method'] ?? 'Xendit'),
                    ]);
                    
                    // B. Tambah Saldo ke Dompet Publisher
                    $user = User::find($transaction->user_id);
                    if ($user) {
                        $user->increment('wallet_balance', $transaction->amount);
                        
                        // C. Kirim Notifikasi Deposit Sukses
                        try {
                            $user->notify(new FinancialStatusNotification($transaction, 'deposit', 'success'));
                        } catch (\Exception $e) {
                            Log::error('Gagal kirim notif deposit: ' . $e->getMessage());
                        }
                    }
                }
            });
            Log::info('Deposit Sukses: ' . $externalId);
        }
        
        // --------------------------------------------------------------------
        // KONDISI 2: PEMBAYARAN KEDALUWARSA (EXPIRED)
        // --------------------------------------------------------------------
        if ($status === 'EXPIRED') {
            $transaction = Transaction::where('reference_id', $externalId)->first();
            
            if ($transaction && $transaction->status === 'pending') {
                // Tandai transaksi gagal karena user tidak membayar tepat waktu
                $transaction->update(['status' => 'failed', 'description' => 'Pembayaran Expired']);
            }
        }

        return response()->json(['message' => 'Deposit Processed'], 200);
    }
}