<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bounty;
use App\Models\Submission;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;
use App\Models\ContactMessage;

class AdminController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'AKSES DITOLAK: Halaman ini khusus Admin.');
        }

        // --- 1. DATA STATISTIK UTAMA ---
        // Karena Revenue muncul, berarti kolom 'amount' aman (numeric).
        $platformRevenue = Transaction::where('type', 'platform_fee')->where('status', 'success')->sum('amount');
        $pendingWithdrawalsCount = WithdrawalRequest::whereIn('status', ['pending', 'processing'])->count();
        $pendingDepositsCount = Deposit::where('status', 'pending')->count();
        $totalUsers = User::count();
        $activeBounties = Bounty::where('status', 'active')->count();

        // --- TAMBAHKAN LOGIKA DANA TERENDAP DI SINI ---
        // 1. Total Saldo User (Clipper & Publisher)
        $totalUserBalances = User::whereIn('role', ['clipper', 'publisher'])->sum('wallet_balance');

        // 2. Dana Hold (Misi Aktif yang anggarannya belum habis)
        $totalEscrowBounties = Bounty::where('status', 'active')->sum('remaining_budget');

        // 3. Dana Terendap (Kas Sistem yang harus ada di Rekening/Gateway)
        $totalEscrowFunds = $totalUserBalances + $totalEscrowBounties;

        // --- 2. DATA TABEL ---
        $latestWithdrawals = WithdrawalRequest::with('user')->whereIn('status', ['pending', 'processing'])->latest()->take(5)->get();
        $pendingWithdrawals = WithdrawalRequest::with('user')->whereIn('status', ['pending', 'processing'])->latest()->get();
        $recentTransactions = Transaction::with('user')->latest()->take(10)->get();
        $pendingDeposits = Deposit::with('user')->where('status', 'pending')->latest()->get();

        // --- 3. DATA CHART (VERSI PHP - ANTI GAGAL) ---
        // Kita ambil data mentahnya saja, lalu hitung pakai logika PHP
        $allTransactions = Transaction::with('user')
            ->get(['id', 'user_id', 'type', 'amount', 'status']);

        // Prize dicatat pada dua ledger: pemasukan Clipper dan pengeluaran Publisher.
        // Pisahkan keduanya agar chart Admin tidak menyatukan makna transaksi.
        $groupedData = $allTransactions
            ->filter(function ($item) {
                // Pending/failed fees are not platform revenue.
                return $item->type !== Transaction::TYPE_PLATFORM_FEE || $item->status === 'success';
            })
            ->groupBy(function ($item) {
                if ($item->type === Transaction::TYPE_BOUNTY_PRIZE) {
                    return $item->user?->role === 'publisher'
                        ? 'bounty_prize_publisher'
                        : 'bounty_prize_clipper';
                }

                return $item->type;
            })
            ->map(function ($row) {
                return $row->sum(function ($item) {
                    return abs($item->amount);
                });
            });

        // Konversi ke format Array untuk Chart
        $chartData = $groupedData->toArray();

        // Pisahkan Label dan Series
        $rawLabels = array_keys($chartData);
        $chartSeries = array_values($chartData);

        // Rapikan tulisan Label
        $chartLabels = array_map(function ($label) {
            return match ($label) {
                'bounty_prize_clipper' => 'Hadiah Clipper',
                'bounty_prize_publisher' => 'Pembayaran Hadiah Publisher',
                'withdrawal_refund' => 'Refund Withdrawal',
                default => ucwords(str_replace('_', ' ', $label)),
            };
        }, $rawLabels);

        // Ambil data pesan terbaru
        $messages = ContactMessage::latest()->take(5)->get();

        // --- 4. KIRIM KE VIEW ---
        return view('admin.dashboard', compact(
            'platformRevenue',
            'pendingWithdrawalsCount',
            'pendingDepositsCount',
            'totalUsers',
            'activeBounties',
            'totalUserBalances', 
            'totalEscrowBounties', 
            'totalEscrowFunds',
            'latestWithdrawals',
            'recentTransactions',
            'pendingDeposits',
            'pendingWithdrawals',
            'chartLabels',
            'chartSeries',
            'messages'
        ));
    }


    // --- MANAJEMEN USER (CLIPPER & PUBLISHER) ---

    public function usersIndex()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Ambil semua user KECUALI admin sendiri
        $users = User::where('role', '!=', 'admin')
            ->latest()
            ->paginate(10); // Pakai pagination biar gak berat kalau user ribuan

        return view('admin.users.index', compact('users'));
    }

    public function userDestroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);

        // Keamanan: Jangan biarkan Admin menghapus sesama Admin lewat sini
        if ($user->role === 'admin') {
            return back()->with('error', 'Tidak bisa menghapus Admin.');
        }

        // Soft Delete (User tidak bisa login lagi, tapi data transaksi aman)
        $user->delete();

        return back()->with('success', 'User berhasil dihapus (Banned)!');
    }
}