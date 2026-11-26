<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMonitorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MISSION CONTROL DASHBOARD
    |--------------------------------------------------------------------------
    | Halaman utama monitoring untuk Admin.
    | Menampilkan:
    | 1. Statistik Global (Total Misi, Total Submission).
    | 2. Red Flags (Misi dengan Rejection Rate Tinggi).
    | 3. Live Feed (Aktivitas Submission Terbaru).
    */
    public function index()
    {
        // 1. STATISTIK RINGKAS
        $stats = [
            'active_bounties'   => Bounty::where('status', 'active')->count(),
            'pending_reviews'   => Submission::where('status', 'pending')->count(),
            'money_in_escrow'   => Bounty::where('status', 'active')->sum('remaining_budget'),
            'total_paid_reward' => Submission::where('status', 'approved')->sum('prize_amount'),
        ];

        // 2. DETEKSI PUBLISHER "PELIT" (Rejection Rate > 50%)
        // Rumus: (Jumlah Reject / Total Submission) * 100
        // Kita hanya ambil Bounty yang minimal sudah ada 5 submission biar datanya valid.
        $suspiciousBounties = Bounty::with('publisher')
            ->withCount(['submissions', 'submissions as rejected_count' => function ($query) {
                $query->where('status', 'rejected');
            }])
            ->having('submissions_count', '>=', 5) // Minimal 5 pelamar baru dihitung
            ->get()
            ->map(function ($bounty) {
                $bounty->rejection_rate = ($bounty->rejected_count / $bounty->submissions_count) * 100;
                return $bounty;
            })
            ->filter(function ($bounty) {
                return $bounty->rejection_rate > 50; // Filter di atas 50% reject
            })
            ->sortByDesc('rejection_rate')
            ->take(10);

        // 3. LIVE FEED (10 Aktivitas Terakhir)
        // Kita ambil submission terbaru beserta statusnya
        $recentActivities = Submission::with(['user', 'bounty.publisher'])
            ->latest('updated_at') // Urutkan berdasarkan update terakhir (bisa saat create, approve, atau reject)
            ->take(15)
            ->get();

        return view('admin.monitor.index', compact('stats', 'suspiciousBounties', 'recentActivities'));
    }
}