<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;
use App\Models\PayoutMethod;
use App\Notifications\AdminActivityNotification;
use App\Notifications\FinancialStatusNotification;
use App\Services\WithdrawalLedgerService;
use Illuminate\Support\Facades\Log;
use Throwable;

class WalletController extends Controller
{
    /**
     * API WITHDRAW (KHUSUS FLUTTER/MOBILE)
     * --------------------------------------------------------------------------
     * Menangani permintaan penarikan dana dari aplikasi mobile.
     *
     * Logika Bisnis:
     * 1. Validasi saldo user mencukupi.
     * 2. Validasi metode pembayaran milik user.
     * 3. Potongan Admin Fee 10%.
     * 4. Update saldo dan simpan riwayat transaksi.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cekPin(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'has_pin' => !is_null($user->security_pin) // True jika sudah punya, False jika belum
        ]);
    }

    public function setupPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:6|confirmed', // Wajib kirim 'pin' dan 'pin_confirmation'
        ]);

        $user = $request->user();
        
        // Simpan PIN dengan Enkripsi
        $user->update([
            'security_pin' => Hash::make($request->pin)
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'PIN Keamanan berhasil dibuat! Silakan lanjut penarikan.'
        ]);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'payout_method_id' => 'required|exists:payout_methods,id',
            'pin' => 'required|digits:6',
        ]);

        $user = $request->user();

        if (!$user->security_pin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum mengatur PIN transaksi.',
            ], 422);
        }

        if (!Hash::check($request->pin, $user->security_pin)) {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN transaksi tidak valid.',
            ], 401);
        }

        try {
            $result = DB::transaction(function () use ($request, $user) {
                $lockedUser = User::whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $lockedUser->wallet_balance < (float) $request->amount) {
                    return [
                        'error' => response()->json([
                            'status' => 'error',
                            'message' => 'Saldo tidak mencukupi untuk melakukan penarikan ini.',
                        ], 400),
                    ];
                }

                $bank = PayoutMethod::whereKey($request->payout_method_id)
                    ->where('user_id', $lockedUser->id)
                    ->lockForUpdate()
                    ->first();

                if (!$bank) {
                    return [
                        'error' => response()->json([
                            'status' => 'error',
                            'message' => 'Metode pembayaran tidak valid atau bukan milik Anda.',
                        ], 400),
                    ];
                }

                $amount = round((float) $request->amount, 2);
                $feeAmount = round($amount * 0.10, 2);
                $finalAmount = round($amount - $feeAmount, 2);

                $withdrawal = WithdrawalRequest::create([
                    'user_id' => $lockedUser->id,
                    'amount_requested' => $amount,
                    'admin_fee' => $feeAmount,
                    'amount_final' => $finalAmount,
                    'payout_method_id' => $bank->id,
                    'status' => 'pending',
                    'bank_info' => $bank->bank_name . " (" . $bank->account_number . ") a/n " . $bank->account_name,
                ]);

                $lockedUser->decrement('wallet_balance', $amount);

                // Record the net transfer and fee separately. Both remain pending
                // until Xendit confirms the disbursement.
                app(WithdrawalLedgerService::class)->ensurePendingLedger($withdrawal);

                return [
                    'withdrawal' => $withdrawal,
                    'amount' => $amount,
                    'fee' => $feeAmount,
                    'final_amount' => $finalAmount,
                    'balance' => (float) $lockedUser->fresh()->wallet_balance,
                ];
            });

            if (isset($result['error'])) {
                return $result['error'];
            }

            $withdrawal = $result['withdrawal'];

            // Notify only after the debit and ledger rows have committed.
            try {
                foreach (User::where('role', 'admin')->get() as $admin) {
                    $admin->notify(new AdminActivityNotification($withdrawal, 'withdraw_request'));
                }

                $withdrawal->load('user');
                $withdrawal->user?->notify(
                    new FinancialStatusNotification($withdrawal, 'withdrawal', 'pending')
                );
            } catch (Throwable $e) {
                Log::warning('Gagal mengirim notifikasi API withdrawal.', [
                    'withdrawal_id' => $withdrawal->id,
                    'exception' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Penarikan berhasil diajukan. Dana bersih menunggu persetujuan Admin.',
                'data' => [
                    'wd_id' => $withdrawal->id,
                    'amount_requested' => $result['amount'],
                    'admin_fee' => $result['fee'],
                    'amount_received' => $result['final_amount'],
                    'sisa_saldo' => $result['balance'],
                ],
            ], 200);
        } catch (Throwable $e) {
            Log::error('Gagal membuat withdrawal melalui API.', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat membuat withdrawal.',
            ], 500);
        }
    }
}