<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawalInvoiceMail;
use App\Models\WithdrawalRequest;
use App\Models\PayoutMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Import Hash tetap ada
use App\Notifications\AdminActivityNotification;
use App\Notifications\FinancialStatusNotification;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\WithdrawalLedgerService;
use Throwable;

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

        $result = DB::transaction(function () use ($request, $user) {
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

            $amount = round((float) $request->amount, 2);
            if ((float) $lockedUser->wallet_balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo tidak cukup untuk melakukan penarikan ini.',
                ]);
            }

            $feeAmount = round($amount * 0.10, 2);
            $finalAmount = round($amount - $feeAmount, 2);

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

            // Both the net payout and the fee are recorded as pending ledger rows.
            app(WithdrawalLedgerService::class)->ensurePendingLedger($withdrawal);

            return [
                'withdrawal' => $withdrawal,
                'final_amount' => $finalAmount,
            ];
        });

        $withdrawal = $result['withdrawal'];
        $finalAmount = $result['final_amount'];

        // Notifications are sent only after the wallet transaction has committed.
        try {
            foreach (User::where('role', 'admin')->get() as $admin) {
                $admin->notify(new AdminActivityNotification($withdrawal, 'withdraw_request'));
            }

            $withdrawal->load('user');
            $withdrawal->user?->notify(
                new FinancialStatusNotification($withdrawal, 'withdrawal', 'pending')
            );
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim notifikasi withdrawal request.', [
                'withdrawal_id' => $withdrawal->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return redirect()->route('dashboard')->with(
            'success',
            'Penarikan diajukan. Dana bersih Rp ' . number_format($finalAmount, 0, ',', '.') . ' menunggu persetujuan Admin.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 3. ADMIN APPROVE & TRANSFER VIA XENDIT
    |--------------------------------------------------------------------------
    | Approval diproteksi dengan lock database dan idempotency key Xendit.
    */
    public function approve($id)
    {
        $secretKey = (string) config('services.xendit.secret_key');
        if ($secretKey === '') {
            return back()->with('error', 'Xendit belum dikonfigurasi. Withdrawal tetap menunggu dan saldo tidak berubah.');
        }

        $prepared = DB::transaction(function () use ($id) {
            $withdrawal = WithdrawalRequest::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($withdrawal->status, ['pending', 'processing'], true)) {
                return ['state' => 'closed'];
            }

            $method = PayoutMethod::whereKey($withdrawal->payout_method_id)
                ->where('user_id', $withdrawal->user_id)
                ->lockForUpdate()
                ->first();

            if (!$method) {
                return ['state' => 'missing_method'];
            }

            $ledger = app(WithdrawalLedgerService::class)
                ->ensurePendingLedger($withdrawal);

            // A provider ID means Xendit already accepted this disbursement.
            // Never submit a second transfer; wait for its webhook.
            if (!empty($ledger->external_ref)) {
                return ['state' => 'already_sent'];
            }

            $withdrawal->update(['status' => 'processing']);

            return [
                'state' => 'ready',
                'withdrawal_id' => $withdrawal->id,
                'external_id' => 'WD-' . $withdrawal->id,
                'amount' => (int) round((float) $withdrawal->amount_final),
                'bank_name' => $method->bank_name,
                'account_number' => $method->account_number,
                'account_name' => $method->account_name,
            ];
        });

        if ($prepared['state'] === 'closed') {
            return back()->with('error', 'Withdrawal ini sudah selesai atau ditolak.');
        }

        if ($prepared['state'] === 'missing_method') {
            return back()->with('error', 'Rekening tujuan tidak ditemukan atau bukan milik pengguna.');
        }

        if ($prepared['state'] === 'already_sent') {
            return back()->with('success', 'Withdrawal sudah diterima Xendit. Menunggu konfirmasi webhook; transfer tidak dikirim ulang.');
        }

        $rawBank = strtoupper(trim($prepared['bank_name']));
        $bankMap = [
            'GOPAY' => 'GOPAY',
            'GOPAY DANA' => 'DANA',
            'BANK MANDIRI' => 'MANDIRI',
            'BANK BCA' => 'BCA',
            'BANK BNI' => 'BNI',
            'BANK BRI' => 'BRI',
        ];

        $bankCode = $bankMap[$rawBank] ?? $rawBank;
        $accountNumber = preg_replace('/[^0-9]/', '', $prepared['account_number']);
        $externalId = $prepared['external_id'];
        $appEnvironment = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) config('app.env', 'production'));
        $idempotencyKey = 'clipkeo-' . $appEnvironment . '-wd-' . $prepared['withdrawal_id'];

        $params = [
            'external_id' => $externalId,
            'amount' => $prepared['amount'],
            'bank_code' => $bankCode,
            'account_holder_name' => $prepared['account_name'],
            'account_number' => $accountNumber,
            'description' => 'Withdrawal Clipkeo',
        ];

        try {
            // X-IDEMPOTENCY-KEY and external_id remain stable across retries.
            $response = Http::asForm()
                ->withBasicAuth($secretKey, '')
                ->withHeaders(['X-IDEMPOTENCY-KEY' => $idempotencyKey])
                ->timeout(30)
                ->post('https://api.xendit.co/disbursements', $params);
        } catch (Throwable $e) {
            // A timeout/connection error does not prove Xendit rejected the transfer.
            // Keep it processing; retrying with the same idempotency key is safe.
            Log::warning('Hasil request Xendit belum dapat dipastikan.', [
                'withdrawal_id' => $prepared['withdrawal_id'],
                'external_id' => $externalId,
                'exception' => $e->getMessage(),
            ]);

            return back()->with(
                'error',
                'Respons Xendit belum dapat dipastikan. Status tetap diproses; ulangi proses Admin dengan referensi yang sama agar tidak membuat transfer ganda.'
            );
        }

        $payload = $response->json() ?? [];

        if ($response->successful()) {
            $providerId = $payload['id'] ?? null;

            DB::transaction(function () use ($prepared, $externalId, $providerId, $bankCode, $accountNumber) {
                $withdrawal = WithdrawalRequest::whereKey($prepared['withdrawal_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$withdrawal) {
                    return;
                }

                $ledger = app(WithdrawalLedgerService::class)
                    ->ensurePendingLedger($withdrawal, $externalId);

                $ledger->update([
                    'external_ref' => $providerId ?: $ledger->external_ref,
                    'bank_code' => $bankCode,
                    'account_number' => $accountNumber,
                    'account_holder' => $prepared['account_name'],
                ]);
            });

            try {
                $withdrawal = WithdrawalRequest::with(['user', 'transaction'])
                    ->find($prepared['withdrawal_id']);

                if ($withdrawal?->user?->email) {
                    Mail::to($withdrawal->user->email)->send(new WithdrawalInvoiceMail($withdrawal));
                }
            } catch (Throwable $mailError) {
                Log::warning('Gagal mengirim invoice withdrawal.', [
                    'withdrawal_id' => $prepared['withdrawal_id'],
                    'exception' => $mailError->getMessage(),
                ]);
            }

            return back()->with(
                'success',
                'Permintaan transfer diterima Xendit. Dana sedang menunggu konfirmasi bank melalui webhook.'
            );
        }

        $errorCode = (string) (
            $payload['error_code']
            ?? $payload['errorCode']
            ?? $payload['message']
            ?? 'XENDIT_REQUEST_ERROR'
        );

        // A definite client-side rejection means no transfer was accepted.
        // Duplicate/idempotency responses and server errors remain unresolved.
        $ambiguousClientStatus = in_array($response->status(), [408, 409, 425, 429], true);

        if ($response->clientError()
            && !$ambiguousClientStatus
            && $errorCode !== 'DUPLICATE_TRANSACTION_ERROR') {
            $failed = app(WithdrawalLedgerService::class)->markFailed(
                (int) $prepared['withdrawal_id'],
                'Xendit: ' . $errorCode,
                $externalId
            );

            if (!$failed) {
                $current = WithdrawalRequest::find($prepared['withdrawal_id']);
                if ($current?->status === 'completed') {
                    return back()->with('success', 'Withdrawal sudah dikonfirmasi berhasil oleh Xendit.');
                }

                return back()->with('error', 'Status withdrawal berubah saat diproses. Periksa riwayat transaksi sebelum mencoba lagi.');
            }

            try {
                $failed->user?->notify(new FinancialStatusNotification(
                    $failed,
                    'withdrawal',
                    'rejected',
                    'Transfer ditolak Xendit. Seluruh dana termasuk biaya admin telah dikembalikan.'
                ));
            } catch (Throwable $notificationError) {
                Log::warning('Gagal mengirim notifikasi refund withdrawal.', [
                    'withdrawal_id' => $prepared['withdrawal_id'],
                    'exception' => $notificationError->getMessage(),
                ]);
            }

            return back()->with(
                'error',
                'Xendit menolak transfer. Seluruh nominal penarikan termasuk biaya admin telah dikembalikan ke saldo pengguna.'
            );
        }

        Log::warning('Status disbursement Xendit memerlukan rekonsiliasi.', [
            'withdrawal_id' => $prepared['withdrawal_id'],
            'external_id' => $externalId,
            'http_status' => $response->status(),
            'error_code' => $errorCode,
        ]);

        return back()->with(
            'error',
            'Xendit belum memberikan hasil final. Withdrawal tetap diproses dan tidak direfund sampai status transfer dipastikan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 4. ADMIN REJECT (TOLAK PENARIKAN)
    |--------------------------------------------------------------------------
    | Penolakan hanya diizinkan sebelum transfer dikirim ke Xendit.
    */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $withdrawal = app(WithdrawalLedgerService::class)->markFailed(
            (int) $id,
            'Ditolak Admin: ' . $request->reason,
            null,
            false
        );

        if (!$withdrawal) {
            return back()->with('error', 'Withdrawal tidak dapat ditolak karena statusnya sudah diproses atau selesai.');
        }

        try {
            $withdrawal->user?->notify(new FinancialStatusNotification(
                $withdrawal,
                'withdrawal',
                'rejected',
                $request->reason . ' Seluruh nominal termasuk biaya admin telah dikembalikan.'
            ));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim notifikasi penolakan withdrawal.', [
                'withdrawal_id' => $withdrawal->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Withdrawal ditolak. Seluruh dana termasuk biaya admin telah dikembalikan ke saldo pengguna.');
    }
}