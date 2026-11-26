<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Transaction; // Pastikan Model Transaction ada
use App\Notifications\AdminActivityNotification;
use App\Notifications\FinancialStatusNotification;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Xendit\Xendit; // [BARU] Import Library Xendit

class DepositController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. TAMPILKAN HALAMAN FORM DEPOSIT (FRONTEND)
    |--------------------------------------------------------------------------
    | Menampilkan view form untuk user melakukan request deposit.
    */
    public function create()
    {
        return view('deposits.create');
    }

    /*
    |--------------------------------------------------------------------------
    | 2. PROSES DEPOSIT VIA XENDIT (UPDATED 🚀)
    |--------------------------------------------------------------------------
    | Logic ini diperbarui untuk integrasi Payment Gateway Xendit.
    | Alur: Input Nominal -> Request Invoice Xendit -> Simpan Transaksi Pending
    | -> Redirect User ke Halaman Bayar Xendit.
    */
    public function store(Request $request)
    {
        // A. Validasi Input (Minimal Rp 10.000)
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        $user = Auth::user();

        // B. Setup Xendit API Key (Dari .env)
        Xendit::setApiKey(env('XENDIT_SECRET_KEY'));

        // C. Buat Reference ID Unik (Format: DEP-TIMESTAMP-ACAK)
        $externalId = 'DEP-' . time() . '-' . Str::random(4);

        $customerObject = [
            'given_names' => $user->name,
            'email' => $user->email,
        ];

        // Hanya tambahkan nomor HP jika user punya data no hp
        if (!empty($user->phone_number)) {
            $customerObject['mobile_number'] = $user->phone_number;
        }

        // D. Siapkan Parameter Request ke Xendit
        $params = [
            'external_id' => $externalId,
            'amount' => (int) $request->amount,
            'description' => 'Top Up Saldo Clipkeo: ' . $user->email,
            'invoice_duration' => 86400, // Invoice berlaku 24 jam
            'currency' => 'IDR',
            'customer' => $customerObject,
            'success_redirect_url' => route('dashboard'),
            'failure_redirect_url' => route('deposits.create'),
        ];

        try {
            // E. TEMBAK API XENDIT (Buat Invoice)
            $createInvoice = \Xendit\Invoice::create($params);

            // F. Simpan Transaksi ke Database (Status: Pending)
            // Kita simpan ke tabel 'transactions' agar tercatat di history dompet
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'currency' => 'IDR',
                'status' => 'pending', // Masih Pending sampai Callback Xendit masuk
                'reference_id' => $externalId, // Ref ID Internal Kita
                'external_ref' => $createInvoice['id'], // ID Invoice dari Xendit
                'payment_url' => $createInvoice['invoice_url'], // Link Bayar Xendit
                'description' => 'Menunggu Pembayaran via Xendit',
            ]);

            // G. Arahkan User ke Halaman Pembayaran Xendit
            return redirect($createInvoice['invoice_url']);

        } catch (\Exception $e) {
            // Error Handling jika koneksi ke Xendit bermasalah
            return back()->with('error', 'Gagal memproses Xendit: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 3. TAMPILKAN INSTRUKSI PEMBAYARAN (MANUAL ONLY)
    |--------------------------------------------------------------------------
    | Fungsi ini tetap dibiarkan jika Anda ingin melihat detail deposit manual.
    | (Tidak digunakan dalam alur otomatis Xendit).
    */
    public function show(Deposit $deposit)
    {
        if ($deposit->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('deposits.show', compact('deposit'));
    }

    /*
    |--------------------------------------------------------------------------
    | 4. PROSES UPLOAD BUKTI TRANSFER (MANUAL ONLY)
    |--------------------------------------------------------------------------
    | Fungsi ini tetap dibiarkan untuk backup fitur upload bukti manual.
    */
    public function update(Request $request, Deposit $deposit)
    {
        $request->validate([
            'proof_image' => 'required|image|max:2048',
        ]);

        if ($deposit->user_id !== Auth::id()) {
            abort(403);
        }

        if ($request->hasFile('proof_image')) {
            if ($deposit->proof_image) {
                Storage::disk('public')->delete($deposit->proof_image);
            }

            $path = $request->file('proof_image')->store('payment-proofs', 'public');

            $deposit->update([
                'proof_image' => $path,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Bukti pembayaran berhasil dikirim! Mohon tunggu verifikasi Admin.');
    }

    /*
    |--------------------------------------------------------------------------
    | 5. ADMIN APPROVE (MANUAL ONLY)
    |--------------------------------------------------------------------------
    | Fungsi ini untuk Admin menyetujui deposit manual.
    | Untuk Xendit, approval dilakukan otomatis via Webhook (bukan disini).
    */
    public function approve($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda bukan admin!');
        }

        $deposit = Deposit::findOrFail($id);

        if ($deposit->status == 'paid') {
            return back()->with('error', 'Deposit ini sudah diproses sebelumnya.');
        }

        $deposit->update(['status' => 'paid']);

        $deposit->user->notify(new FinancialStatusNotification($deposit, 'deposit', 'approved'));

        $user = $deposit->user;
        $user->wallet_balance += $deposit->amount;
        $user->save();

        if (class_exists(Transaction::class)) {
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $deposit->amount,
                'currency' => 'IDR',
                'description' => 'Deposit Approved (' . $deposit->external_ref . ')',
                'reference_id' => 'DEP-' . date('Ymd') . '-' . $deposit->id . strtoupper(Str::random(4)),
                'status' => 'success'
            ]);
        }

        return back()->with('success', 'Deposit berhasil diterima! Saldo user bertambah.');
    }

    /*
    |--------------------------------------------------------------------------
    | 6. ADMIN REJECT (MANUAL ONLY)
    |--------------------------------------------------------------------------
    | Fungsi untuk menolak deposit manual.
    */
    public function reject(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin')
            abort(403);

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $deposit = Deposit::findOrFail($id);

        if ($deposit->status !== 'pending') {
            return back()->with('error', 'Transaksi sudah diproses.');
        }

        $deposit->update([
            'status' => 'failed',
            'rejection_reason' => $request->reason
        ]);

        $deposit->user->notify(new FinancialStatusNotification($deposit, 'deposit', 'rejected', $request->reason));

        if (class_exists(Transaction::class)) {
            Transaction::create([
                'user_id' => $deposit->user_id,
                'type' => 'deposit',
                'amount' => $deposit->amount,
                'currency' => 'IDR',
                'description' => 'Deposit Rejected (' . $deposit->external_ref . ')',
                'reference_id' => 'DEP-' . date('Ymd') . '-' . $deposit->id . strtoupper(Str::random(4)),
                'status' => 'failed'
            ]);
        }

        return back()->with('success', 'Deposit ditolak dengan alasan: ' . $request->reason);
    }
}