<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\FinancialStatusNotification; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        if ($status === 'COMPLETED') {
            $processed = DB::transaction(function () use ($externalId) {
                $trx = Transaction::where('reference_id', $externalId)
                    ->lockForUpdate()
                    ->first();

                if (!$trx || !in_array($trx->status, ['pending', 'processing'], true)) {
                    return false;
                }

                $parts = explode('-', $externalId);
                if (count($parts) < 3) {
                    return false;
                }

                $wdId = end($parts);
                $wd = WithdrawalRequest::whereKey($wdId)
                    ->lockForUpdate()
                    ->first();

                if (!$wd || !in_array($wd->status, ['pending', 'processing'], true)) {
                    return false;
                }

                $trx->update([
                    'status' => 'success',
                    'paid_at' => now(),
                    'description' => 'Withdrawal Berhasil Ditransfer',
                ]);

                $wd->update(['status' => 'completed']);

                try {
                    $wd->user?->notify(new FinancialStatusNotification($wd, 'withdrawal', 'success'));
                } catch (\\Exception $e) {
                    Log::error('Gagal kirim notif withdrawal success: ' . $e->getMessage());
                }

                return true;
            });

            if ($processed) {
                Log::info('Withdrawal Sukses: ' . $externalId);
            }
        } elseif ($status === 'FAILED') {
            $failureCode = $data['failure_code'] ?? 'Bank Error';

            $processed = DB::transaction(function () use ($externalId, $failureCode) {
                $trx = Transaction::where('reference_id', $externalId)
                    ->lockForUpdate()
                    ->first();

                // Hanya transisi pending/processing yang boleh mengembalikan saldo.
                // Callback gagal yang duplikat atau datang setelah sukses diabaikan.
                if (!$trx || !in_array($trx->status, ['pending', 'processing'], true)) {
                    return false;
                }

                $parts = explode('-', $externalId);
                if (count($parts) < 3) {
                    return false;
                }

                $wdId = end($parts);
                $wd = WithdrawalRequest::whereKey($wdId)
                    ->lockForUpdate()
                    ->first();

                if (!$wd || !in_array($wd->status, ['pending', 'processing'], true)) {
                    return false;
                }

                $trx->update([
                    'status' => 'failed',
                    'description' => 'Gagal: ' . $failureCode,
                ]);

                $user = User::withTrashed()
                    ->whereKey($trx->user_id)
                    ->lockForUpdate()
                    ->first();

                if ($user) {
                    $user->increment('wallet_balance', $trx->amount);
                }

                $wd->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Bank Error: ' . $failureCode,
                ]);

                try {
                    $wd->user?->notify(new FinancialStatusNotification(
                        $wd,
                        'withdrawal',
                        'rejected',
                        'Gagal Transfer Bank'
                    ));
                } catch (\\Exception $e) {
                    Log::error('Gagal kirim notif withdrawal fail: ' . $e->getMessage());
                }

                return true;
            });

            if ($processed) {
                Log::warning('Withdrawal Gagal & Refunded: ' . $externalId);
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