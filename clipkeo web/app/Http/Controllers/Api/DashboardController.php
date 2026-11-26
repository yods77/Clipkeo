<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Cek User (TETAP)
        $user = $request->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        // 2. Cek Tabel Submission (TETAP)
        $totalSubmission = 0;
        $submissionError = null;

        try {
            // Menggunakan tabel 'submissions' dan kolom 'clipper_id' sesuai request sebelumnya
            $totalSubmission = DB::table('submissions') 
                                ->where('clipper_id', $user->id)
                                ->count();
        } catch (\Exception $e) {
            $totalSubmission = 0; 
            $submissionError = "Error Query Submission: " . $e->getMessage();
        }

        // --- 3. LOGIKA GAMIFICATION (DIUPDATE: AUTO LEVEL NAME) ---
        $currentXp = $user->reputation_points ?? 0;
        
        $currentLevelName = "Newbie Clipper"; 
        $currentLevelImage = null; // Variable baru untuk link gambar
        
        $nextLevelName = "Max Level";
        $targetXp = $currentXp;
        $progressPercent = 100;

        try {
            // A. CARI LEVEL SAAT INI
            $currentBadge = DB::table('badges')
                            ->where('xp_req', '<=', $currentXp)
                            ->orderBy('xp_req', 'desc') 
                            ->first();

            if ($currentBadge) {
                $currentLevelName = $currentBadge->name;
                
                // 🔥 AMBIL GAMBAR DARI DATABASE
                // Asumsi: Gambar disimpan di folder 'public/assets/badges/'
                // Sesuaikan path ini dengan lokasi penyimpanan gambar asli di Laravel Bosku
                if ($currentBadge->icon_url) {
                    // Url: http://localhost:8000/badges/nama_gambar.png
                    $currentLevelImage = url('badges/' . $currentBadge->icon_url); 
                }
            }

            // B. CARI LEVEL SELANJUTNYA
            $nextBadge = DB::table('badges')
                            ->where('xp_req', '>', $currentXp)
                            ->orderBy('xp_req', 'asc')
                            ->first();

            if ($nextBadge) {
                $targetXp = $nextBadge->xp_req;
                $nextLevelName = $nextBadge->name;
                $progressPercent = ($targetXp > 0) ? round(($currentXp / $targetXp) * 100) : 0;
            }

        } catch (\Exception $e) {
            // Ignore error
        }

        // 4. Kirim Respon
        return response()->json([
            'status' => true, 
            'message' => 'Data dashboard berhasil diambil',
            'debug_note' => $submissionError,
            'data' => [
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->avatar_url,
                'wallet_balance' => $user->wallet_balance ?? 0,
                'total_submission' => $totalSubmission,
                'unread_notifications_count' => $user->unreadNotifications()->count(),
                
                // 🔥 DATA LEVEL SEKARANG OTOMATIS DARI TABEL BADGES
                'current_level' => $currentLevelName, 
                'current_level_image' => $currentLevelImage,
                'current_xp' => (int) $currentXp,
                'next_level_name' => $nextLevelName,
                'target_xp' => (int) $targetXp,
                'level_progress_percent' => $progressPercent, 
            ]
        ]);
    }
}