<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Transaction;
use App\Models\Submission;
use App\Models\LinkedAccount;
use App\Models\DailyStat;
use App\Notifications\MissionActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SubmissionController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | AJAX CHECKER: CEK LINK REAL-TIME
    |--------------------------------------------------------------------------
    */
    public function checkLinkAvailability(Request $request)
    {
        try {
            $linkInput = $request->link;
            $bountyId = $request->bounty_id;

            // 1. Kembangkan link (vt.tiktok -> tiktok.com)
            $realUrl = $this->expandUrl($linkInput);

            // 2. Gunakan Mesin Ekstraksi ID yang baru (Lebih Pintar)
            $videoId = $this->extractVideoId($realUrl);

            // Jaga-jaga jika ID gagal diambil
            if (!$videoId || $videoId == 'watch' || strlen($videoId) < 3) {
                return response()->json([
                    'status' => 'danger',
                    'message' => '⛔ Link tidak valid atau ID tidak terbaca.',
                    'debug' => ['processed_url' => $realUrl, 'extracted_id' => $videoId]
                ]);
            }

            // 3. Cari di Database (Global Search)
            $found = \App\Models\Submission::where('video_id', $videoId)->first();

            if ($found) {
                if ((int) $found->clipper_id !== (int) auth()->id()) {
                    return response()->json([
                        'status' => 'danger',
                        'message' => '⛔ Link video ini sudah di submit oleh clipper lain. Gunakan link video milik anda sendiri.',
                        'debug' => ['match_id' => $videoId, 'owner' => $found->clipper_id]
                    ]);
                }
                return response()->json([
                    'status' => 'info',
                    'message' => 'ℹ️ Video ini milik Anda sendiri. Lanjut update views.'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => '✅ Link video ini aman.',
                'debug' => ['searched_id' => $videoId]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'danger', 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 1. STORE (CLIPPER UPLOAD VIDEO)
    |--------------------------------------------------------------------------
    | Fungsi ini menangani proses submission video dari Clipper.
    */
    public function store(Request $request, $bountyId)
    {
        if (!$request->hasFile('analytics_file')) {
            dd([
                'pesan' => 'File tidak terdeteksi oleh Laravel!',
                'all_input' => $request->all(), // Lihat apakah analytics_file muncul di sini
                'has_enctype' => 'Periksa apakah form kamu sudah ada enctype="multipart/form-data"'
            ]);
        }
        // 1. Ambil Data Bounty & User Publisher
        $bounty = Bounty::with('user')->findOrFail($bountyId);

        // [VALIDASI] Cek Ketersediaan Budget & Status Campaign
        if ($bounty->status !== 'active' || $bounty->remaining_budget <= 100) {
            return back()->with('error', 'Maaf, kuota budget campaign ini sudah habis atau campaign telah berakhir.');
        }

        // 2. Validasi Input
        $request->validate([
            'linked_account_id' => 'required|exists:linked_accounts,id',
            'video_url' => 'required|url',
            'caption_content' => 'required|string',
            'current_views' => 'required|integer|min:0',
            'analytics_file' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm,image/jpeg,image/png|max:51200',
        ]);

        // [SECURITY CHECK] Pastikan Akun Sosmed milik User
        $isMyAccount = LinkedAccount::where('id', $request->linked_account_id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMyAccount) {
            return back()->with('error', 'Validasi Gagal: Akun sosmed yang dipilih bukan milik Anda.');
        }

        // =========================================================================
        // [ANTI-CHEAT INJECTION] PROSES PENCUCIAN LINK 🧼 (DARI KODE SAYA)
        // =========================================================================

        // 1. Buka Shortlink (vt.tiktok -> tiktok.com/video/...)
        $realUrl = $this->expandUrl($request->video_url);

        $videoId = $this->extractVideoId($realUrl);

        // 2. Bersihkan Parameter Sampah (?)
        $cleanLink = strtok($realUrl, '?');

        if (str_contains($realUrl, 'youtube.com') || str_contains($realUrl, 'youtu.be')) {
            // Jika Youtube, kita paksa formatnya ada ID-nya
            if ($videoId && $videoId !== 'watch') {
                $cleanLink = "https://www.youtube.com/watch?v=" . $videoId;
            }
        }

        // Hapus garis miring di akhir jika ada
        $cleanLink = rtrim($cleanLink, '/');

        // [SECURITY UPDATE] WHITELIST DOMAIN CHECKER
        $allowedDomains = [
            'tiktok.com',
            'www.tiktok.com',
            'instagram.com',
            'www.instagram.com',
            'youtube.com',
            'www.youtube.com',
            'youtu.be'
        ];

        // Cek domain dari LINK BERSIH ($cleanLink)
        $parsedUrl = parse_url($cleanLink);
        $host = strtolower($parsedUrl['host'] ?? '');

        $isSafe = false;
        foreach ($allowedDomains as $domain) {
            if (str_contains($host, $domain)) {
                $isSafe = true;
                break;
            }
        }

        if (!$isSafe) {
            return back()->with('error', 'Link Ditolak! Domain tidak dikenali setelah verifikasi (Pastikan link TikTok/IG/YouTube resmi).');
        }

        // 3. Smart Check: Deteksi Link Lama (PAKAI CLEAN LINK)
        $existingSubmission = Submission::where('bounty_id', $bountyId)
            ->where('video_url', $cleanLink) // <--- PENTING: Cek pakai link bersih
            ->first();

        // [SKENARIO A] Jika Link Sudah Ada (User Update Views / Orang Lain Coba Copas)
        if ($existingSubmission) {

            // CEK KEPEMILIKAN: Kalau link sama tapi beda user -> MALING
            if ($existingSubmission->clipper_id !== auth()->id()) {
                return back()->with('error', 'Gagal! Video ini sudah terdaftar atas nama Clipper lain. Dilarang mengambil video orang lain! 👮');
            }

            // Kalau milik sendiri -> PROSES UPDATE
            $analyticsPath = $existingSubmission->analytics_path;
            if ($request->hasFile('analytics_file')) {
                $analyticsPath = $request->file('analytics_file')->storeOnCloudinary('analytics/submissions')->getSecurePath();
            }

            $existingSubmission->update([
                'views_count' => $request->current_views,
                'caption_content' => $request->caption_content,
                'analytics_path' => $analyticsPath,
                'status' => 'pending',
            ]);

            // Log Harian
            DailyStat::updateOrCreate(
                [
                    'submission_id' => $existingSubmission->id,
                    'date' => now()->format('Y-m-d')
                ],
                [
                    'views_count' => $request->current_views
                ]
            );

            return back()->with('success', 'Submission berhasil diupdate! Views terbaru menunggu verifikasi.');
        }

        // [SKENARIO B] Jika Link Baru -> Lakukan CREATE Baru

        // 4. Deteksi & Validasi Platform (PAKAI CLEAN LINK)
        $detectedPlatform = null;
        if (str_contains($cleanLink, 'tiktok.com')) {
            $detectedPlatform = 'tiktok';
        } elseif (str::contains($cleanLink, ['instagram.com', '/reels/'])) {
            $detectedPlatform = 'instagram_reels';
        } elseif (str::contains($cleanLink, ['youtube.com', 'youtu.be'])) {
            $detectedPlatform = 'youtube_shorts';
        }

        if (!in_array($detectedPlatform, (array) $bounty->platform)) {
            return back()->with('error', "Misi ini hanya menerima platform yang sesuai.");
        }

        $analyticsPath = $existingSubmission ? $existingSubmission->analytics_path : null;

        // 4. PROSES UPLOAD KE CLOUDINARY (Satu Pintu untuk Baru & Update)
        if ($request->hasFile('analytics_file')) {
            try {
                // Kita tidak memanggil cloudinary() helper yang bikin error!
                // Kita panggil langsung CORE MESIN Cloudinary-nya.
                $cloudinary = new \Cloudinary\Cloudinary([
                    'cloud' => [
                        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                        'api_key'    => env('CLOUDINARY_API_KEY'),
                        'api_secret' => env('CLOUDINARY_API_SECRET'),
                    ],
                    'url' => [
                        'secure' => true
                    ]
                ]);

                // Upload langsung menggunakan API Inti
                $uploadResult = $cloudinary->uploadApi()->upload(
                    $request->file('analytics_file')->getRealPath(),
                    [
                        'folder' => 'analytics',
                        'resource_type' => 'auto'
                    ]
                );
                
                $analyticsPath = $uploadResult['secure_url'];

            } catch (\Exception $e) {
                // Jika gagal, sistem tidak akan crash (500), tapi kembali dengan rapi
                return back()->with('error', 'Gagal upload ke Cloudinary: ' . $e->getMessage());
            }
        }

        // 6. Generate Kode Unik
        $verifCode = 'CS-' . date('Y') . '-' . strtoupper(substr(md5($bounty->id . auth()->id() . time()), 0, 6));

        // REMOVED REDUNDANT EXTRACTION: $videoId = $this->extractVideoId($request->video_url);
        // We already have $videoId from line 114 (based on $realUrl)

        if (!$videoId) {
            return back()->with('error', 'Link video tidak valid atau tidak didukung!');
        }
        // 7. Simpan Data Baru (PAKAI CLEAN LINK)
        $submission = Submission::create([
            'clipper_id' => auth()->id(),
            'bounty_id' => $bountyId,
            'linked_account_id' => $request->linked_account_id,
            'video_url' => $cleanLink, // <--- YANG DISIMPAN LINK BERSIH
            'video_id' => $videoId,
            'views_count' => $request->current_views,
            'last_paid_views' => 0,
            'payout_status' => 'unpaid',
            'caption_content' => $request->caption_content,
            'analytics_path' => $analyticsPath,
            'verification_code' => $verifCode,
            'status' => 'pending',
        ]);

        // Rekam Jejak Harian
        DailyStat::create([
            'submission_id' => $submission->id,
            'date' => now()->format('Y-m-d'),
            'views_count' => $request->current_views
        ]);

        // 8. Tambah XP ke Clipper
        $user = auth()->user(); // Perbaikan sintaks Auth
        if ($user) {
            $user->addReputation(50);
        }

        // 9. Kirim Notifikasi
        $submission->setRelation('user', auth()->user());
        $submission->setRelation('bounty', $bounty);

        if ($bounty->publisher) {
            $bounty->publisher->notify(new MissionActivityNotification($submission, 'submission_in_review'));
        } elseif ($bounty->user) {
            $bounty->user->notify(new MissionActivityNotification($submission, 'submission_in_review'));
        }

        if (auth()->user()) {
            auth()->user()->notify(new MissionActivityNotification($submission, 'submission_created'));
        }

        return back()->with('success', 'Video berhasil dikirim! Menunggu review publisher. +50 XP ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | FUNGSI: APPROVE & CALCULATE REWARD
    |--------------------------------------------------------------------------
    */
    public function approve(Request $request, Submission $submission)
    {
        return DB::transaction(function () use ($request, $submission) {

            // 1. Lock Data & Validasi Owner
            /** @var \App\Models\Bounty $bounty */
            $bounty = Bounty::with('publisher')->where('id', $submission->bounty_id)->lockForUpdate()->first();
            $submission->load('user');
            $clipper = $submission->user;

            $ownerId = $bounty->publisher_id ?? $bounty->user_id;
            if (auth()->id() !== $ownerId)
                abort(403);

            $request->validate(['views' => 'required|integer|min:0']);
            $currentViews = $request->views;

            $payAmount = 0;
            $note = 'Reward Campaign';
            $isEligibleToPay = false;

            // ================================================================
            // [UPDATE BARU] SINKRONISASI GRAFIK SAAT DIAKUI PUBLISHER
            // ================================================================
            // Jika publisher mengedit angka views saat approve, kita update history hari ini
            // agar grafik user sesuai dengan yang dibayarkan/diakui.
            DailyStat::updateOrCreate(
                [
                    'submission_id' => $submission->id,
                    'date' => now()->format('Y-m-d')
                ],
                [
                    'views_count' => $currentViews
                ]
            );
            // ================================================================

            /*
            |------------------------------------------------------------------
            | LOGIKA A: STRATEGI CPM (PAY PER VIEW)
            |------------------------------------------------------------------
            */
            if ($bounty->distribution_strategy === 'cpm') {

                $otherVideosViews = Submission::where('bounty_id', $bounty->id)
                    ->where('clipper_id', $submission->clipper_id)
                    ->where('id', '!=', $submission->id)
                    ->sum('views_count');

                $totalPortfolioViews = $otherVideosViews + $currentViews;

                if ($totalPortfolioViews < $bounty->min_views) {
                    $submission->update([
                        'status' => 'approved',
                        'views_count' => $currentViews,
                    ]);
                    return back()->with('info', "Data disimpan ($currentViews views). Total portfolio ($totalPortfolioViews) belum mencapai target minimal ($bounty->min_views).");
                }

                $lastPaid = $submission->last_paid_views;
                $deltaViews = $currentViews - $lastPaid;

                if ($deltaViews <= 0) {
                    $submission->update(['views_count' => $currentViews, 'status' => 'approved']);
                    return back()->with('warning', 'Views diupdate, tapi tidak ada penambahan views baru pada video ini untuk dibayar.');
                }

                $payAmount = $deltaViews * $bounty->rate_per_view;
                $note = "CPM Reward: +" . number_format($deltaViews) . " views (Portfolio Qualified)";
                $isEligibleToPay = true;
            }

            /*
            |------------------------------------------------------------------
            | LOGIKA B: STRATEGI PERFORMANCE TIERED
            |------------------------------------------------------------------
            */ elseif ($bounty->distribution_strategy === 'performance_tiered') {

                $tiers = $bounty->rules['tiers'] ?? [];
                usort($tiers, fn($a, $b) => $b['target_views'] <=> $a['target_views']);

                $targetReward = 0;
                $hitTier = null;

                foreach ($tiers as $tier) {
                    if ($currentViews >= $tier['target_views']) {
                        $targetReward = $tier['reward'];
                        $hitTier = $tier;
                        break;
                    }
                }

                $alreadyPaid = $submission->prize_amount;
                $payAmount = $targetReward - $alreadyPaid;

                if ($payAmount <= 0) {
                    $submission->update(['views_count' => $currentViews, 'status' => 'approved']);
                    return back()->with('info', 'Views diupdate. Belum mencapai Tier lebih tinggi.');
                }

                $note = "Tier Reward: Mencapai target " . number_format($hitTier['target_views']) . " views";
                $isEligibleToPay = true;
            }

            /*
            |------------------------------------------------------------------
            | EKSEKUSI PEMBAYARAN & UPDATE DATABASE
            |------------------------------------------------------------------
            */
            if ($isEligibleToPay && $payAmount > 0) {

                if ($bounty->remaining_budget < $payAmount) {
                    $payAmount = $bounty->remaining_budget;
                    $note .= " (Partial/Sisa Budget)";
                }

                if ($payAmount <= 0) {
                    return back()->with('error', 'Budget campaign ini sudah habis total (Rp 0).');
                }

                $bounty->decrement('remaining_budget', $payAmount);

                if ($bounty->remaining_budget <= 100) {
                    $bounty->status = 'completed';
                    $bounty->save();
                }

                $submission->update([
                    'status' => 'approved',
                    'views_count' => $currentViews,
                    'last_paid_views' => $currentViews,
                    'payout_status' => ($bounty->remaining_budget <= 100) ? 'paid' : 'partial',
                    'prize_amount' => ($submission->prize_amount + $payAmount),
                    'approved_at' => now(),
                ]);

                // ========================================================================
                // 🔥 [FITUR BARU] AUTO-SWEEP: TOLAK SEMUA PENDING JIKA BUDGET HABIS
                // ========================================================================
                // Batas aman 1000 rupiah, jika di bawah itu anggap habis dan tolak sisanya.
                if ($bounty->remaining_budget < 1000) {

                    // 1. Ambil data pending + user-nya
                    $pendingSubmissions = Submission::where('bounty_id', $bounty->id)
                        ->where('status', 'pending')
                        ->with('user') // Penting: load relasi user agar bisa notif
                        ->get();

                    foreach ($pendingSubmissions as $pending) {
                        /** @var \App\Models\Submission $pending */
                        // 2. Update Status jadi Rejected
                        $pending->update([
                            'status' => 'rejected',
                            'rejection_reason' => '⚠️ MAAF SYSTEM AUTO-REJECT: Budget campaign ini telah habis (Sold Out). Terima kasih atas partisipasi Anda.'
                        ]);

                        // 3. Kirim Notifikasi Lonceng
                        if ($pending->user) {
                            $pending->user->notify(new MissionActivityNotification($pending, 'submission_rejected'));
                        }
                    }
                }

                if ($clipper) {
                    $clipper->increment('wallet_balance', $payAmount);
                    $clipper->addReputation(50);

                    Transaction::create([
                        'user_id' => $clipper->id,
                        'type' => Transaction::TYPE_BOUNTY_PRIZE,
                        'amount' => $payAmount,
                        'description' => "Reward $bounty->title ($note)",
                        'status' => 'success',
                        'reference_id' => 'RWD-' . date('Ymd') . '-' . $submission->id . '-' . Str::random(4),
                    ]);

                    $clipper->notify(new MissionActivityNotification($submission, 'submission_approved'));
                }

                Transaction::create([
                    'user_id' => $bounty->publisher_id,
                    'type' => Transaction::TYPE_BOUNTY_PRIZE,
                    'amount' => $payAmount,
                    'description' => "Payout Clipper ($note)",
                    'status' => 'success',
                    'reference_id' => 'PAY-' . date('Ymd') . '-' . $submission->id . '-' . Str::random(4),
                ]);

                $msg = 'Pembayaran Berhasil IDR ' . number_format($payAmount);
                if ($bounty->status == 'completed')
                    $msg .= '. Campaign SELESAI (Budget Habis).';

                return back()->with('success', $msg);
            }

            return back()->with('error', 'Terjadi kesalahan logika pembayaran.');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 3. REJECT (TOLAK VIDEO)
    |--------------------------------------------------------------------------
    */
    public function reject(Request $request, $id)
    {
        $submission = Submission::with(['bounty', 'user'])->findOrFail($id);

        if ($submission->bounty->publisher_id !== auth()->id()) {
            abort(403);
        }

        $request->validate(['rejection_reason' => 'required|string|max:255']);

        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        if ($submission->user) {
            $submission->user->notify(new MissionActivityNotification($submission, 'submission_rejected'));
        }

        return back()->with('success', 'Video berhasil ditolak.');
    }

    public function mySubmissions()
    {
        $submissions = Submission::where('clipper_id', auth()->id())
            ->with('bounty')
            ->latest()
            ->get();

        return view('submissions.my', compact('submissions'));
    }

    // =================================================================
    // [HELPER] MESIN PELACAK LINK ASLI (RESOLVER)
    // Support: TikTok (vt/vm) & YouTube (youtu.be)
    // =================================================================
    private function expandUrl($url)
    {
        // 1. Handle TikTok Shortlink
        if (str_contains($url, 'vt.tiktok.com') || str_contains($url, 'vm.tiktok.com')) {
            return $this->resolveRedirect($url);
        }

        // 2. Handle YouTube Shortlink (youtu.be/ID -> youtube.com/watch?v=ID)
        if (str_contains($url, 'youtu.be/')) {
            $videoId = basename(parse_url($url, PHP_URL_PATH));
            return 'https://www.youtube.com/watch?v=' . $videoId;
        }

        // 3. Handle YouTube Shorts
        if (str_contains($url, '/shorts/')) {
            $videoId = basename(parse_url($url, PHP_URL_PATH));
            return 'https://www.youtube.com/watch?v=' . $videoId;
        }

        return $url;
    }

    private function resolveRedirect($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // User Agent penting agar tidak diblokir TikTok
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 10; SM-G975F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.181 Mobile Safari/537.36');
        curl_exec($ch);
        $realUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        // Jika gagal resolve (kosong), kembalikan URL asli
        return $realUrl ?: $url;
    }

    private function extractVideoId($url)
    {
        // 1. TIKTOK (Handle /video/ID, /v/ID, query param ?video_id=ID)
        if (preg_match('/(?:video|v|embed)\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        // 2. YOUTUBE (Standard, Shortlink, Embed, Shorts)
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/|youtube\.com/shorts/)([^"&?/ ]{11})%i', $url, $matches)) {
            return $matches[1];
        }

        // 3. INSTAGRAM (Reels, Posts, TV)
        if (preg_match('/(?:reels|reel|p|tv)\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function showReport($id)
    {
        // Ambil data submission
        $submission = \App\Models\Submission::with('bounty')->findOrFail($id);

        // [PERBAIKAN] Gunakan 'clipper_id' bukan 'user_id'
        if ($submission->clipper_id != auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Akses Ditolak: Laporan ini bukan milik Anda.');
        }

        return view('invoice.show', compact('submission'));
    }
}