<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawalInvoiceMail;
use App\Models\WithdrawalRequest;
use App\Models\Transaction;
use App\Models\PayoutMethod;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Import Hash tetap ada
use App\Notifications\AdminActivityNotification;
use App\Notifications\FinancialStatusNotification;
use App\Models\User;
use Xendit\Xendit;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. TAMPILKAN FORM WITHDRAW (CLIPPER)
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $myBanks = PayoutMethod::where('user_id', Auth::id())->get();
        return view('withdrawals.create', compact('myBanks'));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. PROSES PENGAJUAN WITHDRAW (CLIPPER)
    |--------------------------------------------------------------------------
    | FOKUS UPDATE DISINI: Validasi PIN diperbaiki agar pesan error tampil.
    */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'payout_method_id' => 'required|exists:payout_methods,id',
            'pin' => 'required|digits:6',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->pin, $user->security_pin)) {
            return back()->withErrors(['pin' => 'PIN Keamanan salah. Silakan coba lagi.']);
        }

        $feePercentage = 0.10;

        // Lock saldo dan rekening dalam satu transaksi agar request paralel tidak
        // dapat memakai saldo yang sama atau memilih rekening milik user lain.
        $result = DB::transaction(function () use ($request, $user, $feePercentage) {
            $lockedUser = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $method = PayoutMethod::whereKey($request->payout_method_id)
                ->where('user_id', $lockedUser->id)
                ->lockForUpdate()
                ->first();

            if (!$method) {
                throw ValidationException::withMessages([
                    'payout_method_id' => 'Metode pembayaran tidak valid atau bukan milik Anda.',
                ]);
            }

            $amount = (float) $request->amount;
            if ((float) $lockedUser->wallet_balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo tidak cukup untuk melakukan penarikan ini.',
                ]);
            }

            $feeAmount = $amount * $feePercentage;
            $finalAmount = $amount - $feeAmount;

            $withdrawal = WithdrawalRequest::create([
                'user_id' => $lockedUser->id,
                'payout_method_id' => $method->id,
                'amount_requested' => $amount,
                'admin_fee' => $feeAmount,
                'amount_final' => $finalAmount,
                'bank_info' => $method->bank_name . " (" . $method->account_number . ") a/n " . $method->account_name,
                'status' => 'pending',
            ]);

            $lockedUser->decrement('wallet_balance', $amount);

            return [
                'withdrawal' => $withdrawal,
                'final_amount' => $finalAmount,
            ];
        });

        $withdrawal = $result['withdrawal'];
        $finalAmount = $result['final_amount'];

        // Notifikasi dilakukan setelah commit; kegagalan notifikasi tidak boleh
        // membatalkan atau membuat user mengulang transaksi keuangan.
        try {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new AdminActivityNotification($withdrawal, 'withdraw_request'));
            }

            $withdrawal->load('user');
            $withdrawal->user?->notify(new FinancialStatusNotification($withdrawal, 'withdrawal', 'pending'));
        } catch (\\Throwable $e) {
            \\Illuminate\\Support\\Facades\\Log::warning(
                'Gagal mengirim notifikasi withdrawal request: ' . $e->getMessage()
            );
        }

        return redirect()->route('dashboard')->with(
            'success',
            'Penarikan diproses! Dana bersih Rp ' . number_format($finalAmount, 0, ',', '.') . ' akan segera dikirim.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 3. ADMIN APPROVE & TRANSFER VIA XENDIT
    |--------------------------------------------------------------------------
    | TIDAK DIUBAH (Sama persis seperti kode Anda)
    */
    public function approve($id)
    {
        $wd = WithdrawalRequest::findOrFail($id);
        if ($wd->status !== 'pending')
            return back();

        $method = \App\Models\PayoutMethod::find($wd->payout_method_id);

        if (!$method) {
            return back()->with('error', 'Gagal: Rekening tujuan telah dihapus oleh User.');
        }

        Xendit::setApiKey(env('XENDIT_SECRET_KEY'));

        $rawBank = strtoupper(trim($method->bank_name));

        $bankMap = [
            'GOPAY' => 'GOPAY',
            'GOPAY DANA' => 'DANA',
            'BANK MANDIRI' => 'MANDIRI',
            'BANK BCA' => 'BCA',
            'BANK BNI' => 'BNI',
            'BANK BRI' => 'BRI',
        ];

        $finalBankCode = isset($bankMap[$rawBank]) ? $bankMap[$rawBank] : $rawBank;
        $cleanAccountNumber = preg_replace('/[^0-9]/', '', $method->account_number);
        $amount = (int) $wd->amount_final;
        $externalRef = 'WD-' . time() . '-' . $wd->id;

        $params = [
            'external_id' => $externalRef,
            'amount' => $amount,
            'bank_code' => $finalBankCode,
            'account_holder_name' => $method->account_name,
            'account_number' => $cleanAccountNumber,
            'description' => 'Withdrawal Clipkeo',
        ];

        try {
            \Illuminate\Support\Facades\Log::info('Mencoba Transfer Xendit:', $params);

            $createDisbursement = \Xendit\Disbursements::create($params);

            $wd->update(['status' => 'processing']);

            Transaction::create([
                'user_id' => $wd->user_id,
                'type' => 'withdrawal',
                'amount' => $wd->amount_final,
                'currency' => 'IDR',
                'description' => 'Withdraw diproses ke ' . $finalBankCode,
                'reference_id' => $externalRef,
                'external_ref' => $createDisbursement['id'],
                'status' => 'pending',
                'bank_code' => $finalBankCode,
                'account_number' => $cleanAccountNumber,
                'account_holder' => $method->account_name,
            ]);

            // Catat Fee (Optional)
            $fee = $wd->amount_requested - $wd->amount_final;
            if ($fee > 0) {
                Transaction::create([
                    'user_id' => $wd->user_id,
                    'type' => 'platform_fee',
                    'amount' => $fee,
                    'currency' => 'IDR',
                    'description' => 'Fee Penarikan',
                    'reference_id' => 'FEE-' . $externalRef,
                    'status' => 'success'
                ]);
            }

            try {
                // Memuat relasi agar data transaksi tersedia di email
                $wd->load(['user', 'transaction']);

                Mail::to($wd->user->email)->send(new WithdrawalInvoiceMail($wd));

                \Illuminate\Support\Facades\Log::info('Email Invoice Berhasil Dikirim ke: ' . $wd->user->email);
            } catch (\Exception $mailEx) {
                // Jika email gagal, jangan batalkan transaksi Xendit, cukup catat log error
                \Illuminate\Support\Facades\Log::error('Gagal kirim email invoice: ' . $mailEx->getMessage());
            }

            return back()->with('success', 'Transfer BERHASIL dikirim ke Xendit! Saldo sedang diproses.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Xendit Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses Xendit: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 4. ADMIN REJECT (TOLAK PENARIKAN)
    |--------------------------------------------------------------------------
    | TIDAK DIUBAH (Sama persis seperti kode Anda)
    */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $wd = WithdrawalRequest::findOrFail($id);

        if ($wd->status !== 'pending')
            return back()->with('error', 'Status permintaan tidak valid.');

        $wd->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason
        ]);

        $wd->user->notify(new FinancialStatusNotification($wd, 'withdrawal', 'rejected', $request->reason));

        if (class_exists(Transaction::class)) {
            Transaction::create([
                'user_id' => $wd->user_id,
                'type' => 'withdrawal',
                'amount' => $wd->amount_final,
                'currency' => 'IDR',
                'description' => 'Penarikan dana ditolak',
                'reference_id' => 'WD-REJECT-' . $wd->id . Str::random(4),
                'status' => 'failed'
            ]);
        }

        $wd->user->increment('wallet_balance', $wd->amount_requested);

        return back()->with('success', 'Permintaan ditolak. Dana telah dikembalikan ke user.');
    }
}