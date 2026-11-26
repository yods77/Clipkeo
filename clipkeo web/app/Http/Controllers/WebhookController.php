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
        // --------------------------------------------------------------------
        // KONDISI 1: TRANSFER BERHASIL (COMPLETED)
        // --------------------------------------------------------------------
        if ($status === 'COMPLETED') {
            DB::transaction(function () use ($externalId) {
                
                // A. Update Status di Tabel Transaksi (Laporan Keuangan)
                Transaction::where('reference_id', $externalId)->update([
                    'status' => 'success', 
                    'paid_at' => now(),
                    'description' => 'Withdrawal Berhasil Ditransfer'
                ]);

                // B. Update Status di Tabel Permintaan (WithdrawalRequest)
                // Format ID kita: WD-{Timestamp}-{ID_Asli}
                $parts = explode('-', $externalId);
                
                if (count($parts) >= 3) {
                    $wdId = end($parts); // Mengambil angka terakhir sebagai ID
                    
                    $wd = WithdrawalRequest::find($wdId);
                    
                    // Pastikan hanya memproses yang statusnya masih pending/processing
                    if ($wd && in_array($wd->status, ['pending', 'processing'])) {
                        
                        // Tandai request selesai
                        $wd->update(['status' => 'completed']);

                        // C. Kirim Notifikasi Sukses ke User (Clipper)
                        try {
                            $wd->user->notify(new FinancialStatusNotification($wd, 'withdrawal', 'success'));
                        } catch (\Exception $e) {
                            Log::error('Gagal kirim notif withdrawal success: ' . $e->getMessage());
                        }
                    }
                }
            });
            Log::info('Withdrawal Sukses: ' . $externalId);
        }
        
        // --------------------------------------------------------------------
        // KONDISI 2: TRANSFER GAGAL (FAILED) -> SAFETY NET / REFUND
        // --------------------------------------------------------------------
        elseif ($status === 'FAILED') {
            DB::transaction(function () use ($externalId, $data) {
                // Cari data transaksi berdasarkan ID Xendit
                $trx = Transaction::where('reference_id', $externalId)->first();
                
                // Pastikan transaksi ada dan belum ditandai failed sebelumnya
                if ($trx && $trx->status !== 'failed') {
                    
                    // A. Update Status Transaksi jadi Failed
                    $trx->update([
                        'status' => 'failed', 
                        'description' => 'Gagal: ' . ($data['failure_code'] ?? 'Bank Error')
                    ]);
                    
                    // B. [CRITICAL] REFUND SALDO USER
                    // Uang dikembalikan ke dompet user karena transfer gagal
                    $user = User::find($trx->user_id);
                    if ($user) {
                        $user->increment('wallet_balance', $trx->amount);
                    }

                    // C. Update Status Request jadi Rejected
                    $parts = explode('-', $externalId);
                    if (count($parts) >= 3) {
                        $wdId = end($parts);
                        $wd = WithdrawalRequest::find($wdId);
                        if ($wd) {
                            $wd->update([
                                'status' => 'rejected',
                                'rejection_reason' => 'Bank Error: ' . ($data['failure_code'] ?? 'Unknown')
                            ]);

                            // D. Kirim Notifikasi Gagal ke User
                            try {
                                $wd->user->notify(new FinancialStatusNotification($wd, 'withdrawal', 'rejected', 'Gagal Transfer Bank'));
                            } catch (\Exception $e) {
                                Log::error('Gagal kirim notif withdrawal fail: ' . $e->getMessage());
                            }
                        }
                    }
                }
            });
            Log::warning('Withdrawal Gagal & Refunded: ' . $externalId);
        }

        return response()->json(['message' => 'Withdrawal Processed'], 200);
    }

    /**
     * ============================================================================
     * PRIVATE: HANDLE DEPOSIT (LOGIKA TOP UP SALDO)
     * ============================================================================
     * Menangani logika saat Publisher melakukan pembayaran (Top Up).
     * - Jika SUKSES: Tambah saldo wallet publisher otomatis.
     */
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