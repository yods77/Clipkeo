<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Deposit;
use App\Models\Submission;
use App\Models\WithdrawalRequest;
use App\Models\LinkedAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX (HALAMAN UTAMA DASHBOARD)
    |--------------------------------------------------------------------------
    | Mengatur logika tampilan dashboard berdasarkan Role User (Admin, Clipper, Publisher).
    | Memastikan setiap role mendapatkan data yang sesuai untuk View-nya.
    */
    public function index()
    {
        $user = auth()->user();

        // 1. Ambil data misi (yang sudah ada sebelumnya)
        $activeBounties = \App\Models\Bounty::where('status', 'active')->get();

        // 2. TAMBAHKAN INI: Ambil akun sosial milik user ini
        // Pastikan model LinkedAccount sudah ada
        $mySocials = \App\Models\LinkedAccount::where('user_id', $user->id)->get();

        /*
        |--------------------------------------------------------------------------
        | 1. LOGIKA KHUSUS ADMIN
        |--------------------------------------------------------------------------
        | Admin tidak menggunakan dashboard umum, langsung redirect ke panel admin.
        */
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. DATA GLOBAL & PENCARIAN (SEARCH LOGIC)
        |--------------------------------------------------------------------------
        | Bagian ini menangani pencarian misi. Jika ada input 'search' dari URL,
        | query akan memfilter data. Jika tidak, tampilkan data terbaru.
        */

        // Mulai Query Builder untuk Misi Aktif
        $query = Bounty::with('publisher')->where('status', 'active');

        // Cek apakah ada pencarian menggunakan helper request()
        if (request()->has('search') && request('search') != null) {
            $search = request('search');

            // Filter: Judul OR Deskripsi OR Nama Publisher
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('publisher', function ($pub) use ($search) {
                        $pub->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Eksekusi Query (Ambil 4 data terbaru)
        $latestBounties = $query->latest()->take(4)->get();

        // Mengambil Leaderboard (Top 10 Clipper dengan Reputasi Tertinggi)
        $leaderboard = User::with('badges')
            ->orderBy('reputation_points', 'desc')
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | [UPDATE] HITUNG TOTAL SUBMISSION
        |--------------------------------------------------------------------------
        | Menghitung jumlah setoran misi yang pernah dilakukan user yang login.
        | Data ini dipakai untuk ditampilkan di Card Profil Dashboard.
        */
        $totalSubmissions = $user->submissions()->count();

        /*
        |--------------------------------------------------------------------------
        | 3. LOGIKA KHUSUS CLIPPER
        |--------------------------------------------------------------------------
        | Clipper membutuhkan data Leaderboard, Misi Aktif, Riwayat Penarikan,
        | dan Total Submission (untuk statistik profil).
        */
        if ($user->role === 'clipper') {
            $withdrawals = Transaction::where('user_id', $user->id)
                ->where('type', 'withdrawal') 
                ->latest()
                ->get();

            $withdrawals = WithdrawalRequest::where('user_id', $user->id)
                ->latest()
                ->get();

            $mySocials = \App\Models\LinkedAccount::where('user_id', $user->id)->get();

            // [UPDATE] Menambahkan 'totalSubmissions' ke view agar bisa dibaca di dashboard blade
            return view('dashboard', compact('withdrawals', 'latestBounties', 'leaderboard', 'totalSubmissions', 'mySocials'));
        }

        /*
        |--------------------------------------------------------------------------
        | 4. LOGIKA KHUSUS PUBLISHER
        |--------------------------------------------------------------------------
        | Publisher membutuhkan statistik campaign, saldo, dan data deposit.
        */
        if ($user->role === 'publisher') {

            // A. Statistik Misi Aktif (Jumlah)
            $activeCampaigns = Bounty::where('publisher_id', $user->id)
                ->where('status', 'active')
                ->count();

            // B. Total Pelamar (Semua misi milik publisher ini)
            $totalApplicants = Submission::whereHas('bounty', function ($q) use ($user) {
                $q->where('publisher_id', $user->id);
            })->count();

            // C. Saldo & Keuangan
            $currentBalance = $user->wallet_balance;

            // Hitung Dana Tertahan (Escrow - Budget misi yang sedang berjalan)
            $lockedFunds = Bounty::where('publisher_id', $user->id)
                ->whereIn('status', ['active', 'completed'])
                ->sum('remaining_budget');

            // D. Riwayat Data Deposit
            $myDeposits = Transaction::where('user_id', $user->id)
                ->where('type', 'deposit') 
                ->latest()
                ->get();    

            // E. Daftar Misi Saya
            $myBounties = Bounty::where('publisher_id', $user->id)
                ->withCount('submissions')
                ->latest()
                ->get();

            // Kirim data spesifik Publisher ke View
            // [UPDATE] Menambahkan 'totalSubmissions' juga jika Publisher juga bisa submit (opsional)
            return view('dashboard', compact(
                'activeCampaigns', 
                'totalApplicants',
                'currentBalance',
                'lockedFunds',
                'myDeposits',
                'myBounties',
                'leaderboard',
                'totalSubmissions' // Data tambahan untuk statistik profil
            ));
        }


        /*
        |--------------------------------------------------------------------------
        | 5. FALLBACK (USER TANPA ROLE / GUEST)
        |--------------------------------------------------------------------------
        | Tampilkan data dasar saja jika role belum diset.
        */
        return view('dashboard', compact('latestBounties', 'leaderboard', 'mySocials', 'totalSubmissions'));
    }
}