<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;
use App\Models\PayoutMethod;
use App\Notifications\AdminActivityNotification;

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
        // Otomatis ambil user dari Token (Sanctum)
        $user = $request->user(); 

        // 1. Validasi Input Dasar
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'payout_method_id' => 'required|exists:payout_methods,id',
        ]);

        // 2. Cek Kecukupan Saldo
        if ($user->wallet_balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Saldo tidak mencukupi untuk melakukan penarikan ini.'
            ], 400);
        }

        // 3. Validasi Kepemilikan Rekening Bank
        $bank = PayoutMethod::where('id', $request->payout_method_id)
                            ->where('user_id', $user->id)
                            ->first();

        if (!$bank) {
            return response()->json([
                'status' => 'error',
                'message' => 'Metode pembayaran tidak valid atau bukan milik Anda.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | HITUNG POTONGAN ADMIN FEE (10%)
        |--------------------------------------------------------------------------
        */
        $feeRate = 0.10; // 10 Persen
        $feeAmount = $request->amount * $feeRate;     // Misal: 50.000 * 0.1 = 5.000
        $finalAmount = $request->amount - $feeAmount; // Misal: 45.000 (Yang diterima user)

        // 4. EKSEKUSI TRANSAKSI DATABASE
        DB::beginTransaction();
        try {
            // A. Potong Saldo User (Full Amount)
            $user->decrement('wallet_balance', $request->amount);

            // B. Buat Tiket Withdrawal Request
            // Catatan: Saya menghapus kolom 'payment_provider' agar tidak error SQL
            $wd = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount_requested' => $request->amount, // Jumlah Kotor
                'amount_final' => $finalAmount,         // Jumlah Bersih (Setelah Fee)
                'payout_method_id' => $bank->id,
                'status' => 'pending',
                'bank_info' => $bank->bank_name . " (" . $bank->account_number . ") a/n " . $bank->account_name,
            ]);

            // 🔥 [TAMBAHAN BARU] KIRIM NOTIFIKASI KE ADMIN 🔥
            // Biar Lonceng Dashboard Admin Bunyi "Ting!"
            try {
                // 1. Cari user yang role-nya admin
                $admins = User::where('role', 'admin')->get(); 

                // 2. Kirim ke semua admin (kalau adminnya lebih dari 1)
                foreach ($admins as $admin) {
                    $admin->notify(new AdminActivityNotification($wd, 'withdraw_request'));
                }
            } catch (\Exception $e) {
                // Kalau notif gagal, biarkan saja (jangan bikin error transaksi uang)
                \Illuminate\Support\Facades\Log::error("Gagal kirim notif admin: " . $e->getMessage());
            }
            // 🔥 [END TAMBAHAN] 🔥

            // C. Catat Riwayat Transaksi (Transaction History)
            if (class_exists(Transaction::class)) {
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => $request->amount,
                    'currency' => 'IDR',
                    'description' => 'Penarikan (Fee 10%) ke ' . $bank->bank_name,
                    'reference_id' => 'WD-' . $wd->id . '-' . Str::random(4),
                    'status' => 'pending'
                ]);
            }

            // Simpan Perubahan
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Penarikan berhasil diajukan! Dana bersih Rp ' . number_format($finalAmount, 0) . ' akan segera diproses.',
                'data' => [
                    'wd_id' => $wd->id,
                    'amount_requested' => $request->amount,
                    'admin_fee' => $feeAmount,
                    'amount_received' => $finalAmount,
                    'sisa_saldo' => $user->wallet_balance
                ]
            ], 200);

        } catch (\Exception $e) {
            // Batalkan semua perubahan jika ada error
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}