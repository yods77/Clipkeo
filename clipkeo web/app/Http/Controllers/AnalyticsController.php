<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bounty;
use App\Models\Submission;
use App\Models\DailyStat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. OVERVIEW (DASHBOARD UTAMA)
    |--------------------------------------------------------------------------
    | Route: /analytics/overview
    */
    public function overview()
    {
        $user = Auth::user();

        // Validasi Role
        if ($user->role !== 'publisher')
            abort(403);

        // Ambil SEMUA ID Campaign milik Publisher
        $bountyIds = Bounty::where('publisher_id', $user->id)->pluck('id');

        $pageTitle = "Overall Performance";
        $subTitle = "Laporan gabungan dari seluruh campaign aktif & selesai.";

        return $this->generateReport($bountyIds, $pageTitle, $subTitle);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. CAMPAIGN ANALYTICS (DETAIL PER MISI)
    |--------------------------------------------------------------------------
    | Route: /bounties/{id}/analytics
    */
    public function campaign($id)
    {
        $bounty = Bounty::findOrFail($id);

        // Validasi Pemilik Campaign
        if ($bounty->publisher_id !== Auth::id())
            abort(403);

        $pageTitle = "Campaign Analytics: " . $bounty->title;
        $subTitle = "Analisis mendalam untuk performa campaign ini.";

        // Kirim array berisi 1 ID saja
        return $this->generateReport([$id], $pageTitle, $subTitle, $bounty);
    }

    /*
    |--------------------------------------------------------------------------
    | CORE LOGIC (PRIVATE METHOD)
    |--------------------------------------------------------------------------
    | Fungsi pusat untuk menghitung statistik & mengirim data ke View.
    */
    private function generateReport($bountyIds, $title, $subTitle, $bounty = null)
    {
        // ------------------------------------------------------------------------
        // A. HERO STATS
        // ------------------------------------------------------------------------
        $submissions = Submission::whereIn('bounty_id', $bountyIds);

        // Total Views (Hanya yang Approved)
        $totalViews = (clone $submissions)->where('status', 'approved')->sum('views_count');

        // Total Uang Keluar (Real Spend)
        $totalSpend = (clone $submissions)->sum('prize_amount');

        // Total Konten Tercipta (Approved)
        $totalContent = (clone $submissions)->where('status', 'approved')->count();

        // ECPV (Efisiensi Biaya per View)
        $eCPV = $totalViews > 0 ? ($totalSpend / $totalViews) : 0;


        // ------------------------------------------------------------------------
        // B. GRAFIK TREN HARIAN (30 HARI TERAKHIR)
        // ------------------------------------------------------------------------
        $viewsTrend = DailyStat::query()
            ->join('submissions', 'daily_stats.submission_id', '=', 'submissions.id')
            ->whereIn('submissions.bounty_id', $bountyIds)
            ->where('daily_stats.date', '>=', Carbon::now()->subDays(30))
            ->selectRaw('daily_stats.date, SUM(daily_stats.views_count) as total_views')
            ->groupBy('daily_stats.date')
            ->orderBy('daily_stats.date', 'ASC')
            ->get();


        // ------------------------------------------------------------------------
        // C. DISTRIBUSI PLATFORM
        // ------------------------------------------------------------------------
        $platformStats = (clone $submissions)->select('video_url')->get()
            ->groupBy(function ($item) {
                if (str_contains($item->video_url, 'tiktok'))
                    return 'TikTok';
                if (str_contains($item->video_url, 'instagram'))
                    return 'Instagram';
                if (str_contains($item->video_url, 'youtube') || str_contains($item->video_url, 'youtu.be'))
                    return 'YouTube';
                return 'Other';
            })
            ->map->count();


        // ------------------------------------------------------------------------
        // D. TOP CLIPPERS
        // ------------------------------------------------------------------------
        $topClippers = (clone $submissions)
            ->where('status', 'approved')
            ->join('users', 'submissions.clipper_id', '=', 'users.id')
            ->select(
                'users.name',
                'users.email',
                DB::raw('SUM(submissions.views_count) as total_views'),
                DB::raw('SUM(submissions.prize_amount) as total_earnings'),
                DB::raw('COUNT(submissions.id) as total_videos')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get();

        // ------------------------------------------------------------------------
        // [PENTING] E. LIST CAMPAIGN UNTUK DROPDOWN
        // ------------------------------------------------------------------------
        // Ini adalah bagian yang menyebabkan error sebelumnya. 
        // Kita harus kirim daftar campaign agar dropdown di blade bisa dirender.
        $allBounties = Bounty::where('publisher_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title']);

        // Kirim Data ke View (Termasuk $allBounties)
        return view('publisher.analytics', compact(
            'title',
            'subTitle',
            'bounty',
            'totalViews',
            'totalSpend',
            'totalContent',
            'eCPV',
            'viewsTrend',
            'platformStats',
            'topClippers',
            'allBounties'
        ));
    }
}