<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublisherController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | HALAMAN DASHBOARD UTAMA
    |--------------------------------------------------------------------------
    | Menampilkan ringkasan data, misi terbaru, dan riwayat deposit terakhir.
    */
    public function dashboard()
    {
        $user = Auth::user();

        // 1. Ambil Sayembara milik Publisher ini saja (Beserta jumlah pelamar)
        $myBounties = Bounty::where('publisher_id', $user->id)
            ->with('submissions')
            ->withCount('submissions') 
            ->latest()
            ->get();

        // 2. Ambil Riwayat Deposit Terakhir (Real Data)
        // Mengambil 5 data deposit terakhir milik user ini
        $myDeposits = \App\Models\Deposit::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // 3. Hitung Statistik Sederhana untuk Dashboard
        $activeCampaigns = $myBounties->where('status', 'active')->count();

        // Hitung total pelamar dari kumpulan ID misi di atas
        $totalApplicants = Submission::whereIn('bounty_id', $myBounties->pluck('id'))->count();

        // Ambil saldo saat ini
        $currentBalance = $user->wallet_balance;

        return view('dashboard', compact('myBounties', 'activeCampaigns', 'totalApplicants', 'currentBalance', 'myDeposits'));
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN ANALYTICS (STATISTIK)
    |--------------------------------------------------------------------------
    | Menampilkan performa kampanye publisher dengan Data Real dari Database.
    */
    public function analytics()
    {
        $user = Auth::user();

        // 1. [REAL] Hitung Misi Aktif
        // Mencari jumlah bounty milik user yang statusnya sedang 'active'
        $activeCampaigns = Bounty::where('publisher_id', $user->id)
            ->where('status', 'active')
            ->count();

        // 2. [REAL] Hitung Total Pelamar
        // Mencari semua submission dimana bounty-nya milik user ini
        $totalApplicants = Submission::whereHas('bounty', function ($query) use ($user) {
            $query->where('publisher_id', $user->id);
        })->count();

        // 3. [REAL] Hitung Dana Tertahan (Escrow)
        // Menjumlahkan 'budget' dari semua misi yang sedang aktif.
        // Uang ini dianggap "terkunci" sampai misi selesai atau dibatalkan.
        // PENTING: Pastikan tabel 'bounties' punya kolom 'budget'. Jika namanya 'price'/'reward', ubah disini.
        $lockedFunds = Bounty::where('publisher_id', $user->id)
            ->where('status', 'active')
            ->sum('budget'); 

        // 4. [REAL] Saldo Utama
        // Mengambil saldo dompet yang tersedia untuk ditarik/digunakan
        $currentBalance = $user->wallet_balance ?? 0;

        return view('publisher.analytics', compact(
            'activeCampaigns', 
            'totalApplicants', 
            'currentBalance', 
            'lockedFunds'
        ));
    }
}