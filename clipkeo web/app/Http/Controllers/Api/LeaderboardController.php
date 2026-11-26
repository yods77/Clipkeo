<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil User Login (untuk menandai "It's Me")
        $me = $request->user();

        // 2. Ambil Semua Badge (Untuk efisiensi, ambil sekali saja)
        // Kita urutkan DESC agar mudah dicek (XP tertinggi dulu)
        $badges = DB::table('badges')->orderBy('xp_req', 'desc')->get();

        // 3. Query Top Clipper (Misal Top 50)
        // Pastikan kolom 'reputation_points' dan 'reputation_score' ada di tabel users
        $clippers = DB::table('users')
                    ->select('id', 'name', 'username', 'avatar_url', 'reputation_points', 'reputation_score')
                    ->orderBy('reputation_points', 'desc')
                    ->limit(50)
                    ->get();

        // 4. Format Data untuk Flutter
        $leaderboardData = $clippers->map(function ($clipper, $index) use ($me, $badges) {
            
            // A. Logika Mencari Badge yang Sesuai XP User
            $userBadgeUrl = null; 
            // Loop badges, cari yang pertama kali memenuhi syarat (xp_req <= user_xp)
            // Karena sudah diurutkan desc, yang pertama ketemu adalah pangkat tertinggi user
            foreach ($badges as $badge) {
                if ($clipper->reputation_points >= $badge->xp_req) {
                    if ($badge->icon_url) {
                        $userBadgeUrl = url('badges/' . $badge->icon_url); // Sesuaikan path 'badges/'
                    }
                    break; // Stop loop jika sudah ketemu
                }
            }
            // Fallback jika tidak punya badge
            if (!$userBadgeUrl) {
                $userBadgeUrl = url('badges/bronze_medal.png'); // Gambar default
            }

            // B. Tentukan Tier Name & Color Code (Opsional, untuk logika warna di Flutter)
            // Logic diambil dari blade: score >= 4 (Cyan), >=3 (Yellow), dst.
            $tierName = "Newbie"; // Default
            if (isset($clipper->tier_name)) {
                $tierName = $clipper->tier_name;
            } else {
                // Manual mapping jika kolom tier_name tidak ada di tabel users
                // Sesuaikan logika ini dengan sistem Anda
                if ($clipper->reputation_score >= 4) $tierName = "Legend";
                elseif ($clipper->reputation_score >= 3) $tierName = "Master";
                elseif ($clipper->reputation_score >= 2) $tierName = "Star";
            }

            // C. Fix Avatar URL
            $avatar = $clipper->avatar_url;
            if ($avatar && !str_starts_with($avatar, 'http')) {
                // Asumsi storage link laravel
                $avatar = url('storage/' . $avatar); 
            }

            return [
                'rank' => $index + 1,
                'id' => $clipper->id,
                'name' => $clipper->name,
                'username' => $clipper->username,
                'avatar_url' => $avatar,
                'xp' => (int) $clipper->reputation_points,
                'reputation_score' => (int) $clipper->reputation_score,
                'tier_name' => $tierName,
                'badge_url' => $userBadgeUrl,
                'is_me' => ($me && $clipper->id === $me->id), // Penanda User Sendiri
            ];
        });

        // 5. Kirim JSON
        return response()->json([
            'status' => true,
            'message' => 'Leaderboard data fetched successfully',
            'data' => $leaderboardData
        ]);
    }
}