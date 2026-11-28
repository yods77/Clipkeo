<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WithdrawalRequest;

class TransactionController extends Controller
{
    public function index(Request $request) // <--- Tambahkan Request $request
    {
        $user = Auth::user();

        $withdrawals = WithdrawalRequest::where('user_id', $user->id)
            ->latest()
            ->get();

        // 1. Base Query
        $query = Transaction::query();

        // 2. Role Scope (Admin lihat semua, User lihat punya sendiri)
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        } else {
            $query->with('user'); // Admin butuh data user
        }

        // 3. Filter Pencarian (Keyword)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");

                // Opsional: Admin bisa cari nama user
                if (Auth::user()->role === 'admin') {
                    $q->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%$search%");
                    });
                }
            });
        }

        // 4. Filter Tipe Transaksi
        if ($request->filled('type')) {
            $legacyTypes = [
                'reward' => Transaction::TYPE_BOUNTY_PRIZE,
                'payout' => Transaction::TYPE_BOUNTY_PRIZE,
                'refund' => Transaction::TYPE_BOUNTY_REFUND,
            ];

            $requestedType = (string) $request->input('type');
            $type = $legacyTypes[$requestedType] ?? $requestedType;
            $allowedTypes = [
                Transaction::TYPE_DEPOSIT,
                Transaction::TYPE_WITHDRAWAL,
                Transaction::TYPE_BOUNTY_HOLD,
                Transaction::TYPE_BOUNTY_PRIZE,
                Transaction::TYPE_BOUNTY_REFUND,
                Transaction::TYPE_PLATFORM_FEE,
                Transaction::TYPE_WITHDRAWAL_REFUND,
            ];

            if (in_array($type, $allowedTypes, true)) {
                $query->where('type', $type);
            }
        }

        // 5. Filter Tanggal (Start & End)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 6. Get Data (Paginate)
        // withQueryString() penting agar saat pindah halaman, filternya tidak hilang
        $transactions = $query->latest()->paginate(15)->withQueryString();

        // 7. Hitung Saldo (Hanya visualisasi untuk user)
        $balance = ($user->role !== 'admin') ? $user->wallet_balance : 0;

        return view('wallet.index', compact('transactions', 'balance'));
    }

    public function show($id)
    {
        // 1. Cari transaksi berdasarkan ID
        // Gunakan findOrFail agar muncul 404 jika ID tidak ketemu
        $transaction = Transaction::findOrFail($id);

        // 2. (Opsional) Keamanan: Pastikan yang buka nota adalah pemiliknya
        if ($transaction->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses Ditolak');
        }

        // 3. Kirim data asli ke View
        return view('invoice.show', compact('transaction'));
    }

}