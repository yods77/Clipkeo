<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Models\Bounty;
use App\Models\Submission;
use App\Http\Controllers\Api\BountyController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PayoutMethodController;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\WalletController;
use App\Notifications\MissionActivityNotification;
use App\Notifications\FinancialStatusNotification;
use App\Notifications\AdminActivityNotification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PinResetController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Cloudinary\Cloudinary;

// === PINTU UMUM (Bisa diakses tanpa token) ===
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/login-google', [AuthController::class, 'googleLogin'])->middleware('throttle:5,1');
Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->middleware('throttle:5,1');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:10,1');
Route::post('/forgot-pin', [PinResetController::class, 'sendOtp'])->middleware('throttle:5,1');
Route::post('/reset-pin', [PinResetController::class, 'resetPin'])->middleware('throttle:10,1');
Route::post('/verify-pin-otp', [PinResetController::class, 'verifyOtp'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/wallet/check-pin', [WalletController::class, 'cekPin']);
    Route::post('/wallet/setup-pin', [WalletController::class, 'setupPin']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
    Route::post('/wallet/validate-pin', [ProfileController::class, 'validatePin']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/user', [ProfileController::class, 'index']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::get('/my-active-missions', [BountyController::class, 'myActiveMissions']);
    Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken']);
    Route::post('/profile/update-avatar', [ProfileController::class, 'updateAvatar']);
    Route::post('/update-phone', [AuthController::class, 'updatePhoneNumber']);
    Route::get('/my-social-accounts', function (Request $request) {
        $accounts = $request->user()->linkedAccounts; // Sesuai nama relasi di User.php
        return response()->json([
            'status' => 'success',
            'data' => $accounts
        ]);
    });

    Route::get('/notifications', function (Request $request) {
        // Ambil 30 notifikasi terakhir
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Info',
                    'message' => $n->data['message'] ?? '-',
                    'type' => $n->data['type'] ?? 'info', // deposit, withdrawal, submission
                    'is_read' => $n->read_at !== null,
                    'created_at' => $n->created_at->diffForHumans(), // "5 menit yang lalu"
                    'data_id' => $n->data['data_id'] ?? null,
                ];
            });

        return response()->json(['status' => 'success', 'data' => $notifications]);
    });

    // 2. TANDAI SUDAH DIBACA (MARK AS READ)
    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $notification = $request->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'success']);
    });

    // 3. TANDAI SEMUA DIBACA
    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['status' => 'success']);
    });

    Route::get('/wallet/dashboard', function (Request $request) {
        $user = $request->user()->load('payoutMethods');
        $transactions = $user->transactions()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => true,
            'data' => [
                'balance' => (int) $user->wallet_balance,
                'reputation_points' => (int) $user->reputation_points,
                'tier' => $user->getTierNameAttribute(),
                'transactions' => $transactions,
                'unread_notifications_count' => $user->unreadNotifications()->count(),
                'payout_methods' => $user->payoutMethods,
                'service_fee_pct' => 0.05,
                'min_withdraw' => 50000,
            ],
        ]);
    });

    Route::get('/payout-methods', [PayoutMethodController::class, 'index']);
});

Route::get('/campaigns', function () {
    try {
        // 1. Ambil data Bounty yang statusnya 'active'
        // 2. Gunakan 'with' untuk mengambil data penerbit (publisher)
        //    PENTING: Pastikan di model Bounty.php ada fungsi 'public function publisher()'

        $campaigns = Bounty::with('publisher')
            ->where('status', 'active')
            ->latest() // Urutkan dari yang terbaru
            ->get()
            ->map(function ($bounty) {
                // 3. Modifikasi Data Sedikit agar enak dipakai di Flutter
                return [
                    'id' => $bounty->id,
                    'title' => $bounty->title,
                    'category' => $bounty->category,

                    // Buat URL Gambar Lengkap (http://...)
                    // Ganti 'storage/' jika folder simpannya beda
                    'thumbnail_url' => $bounty->image_path ? asset('storage/' . $bounty->image_path) : null,

                    'brand_name' => $bounty->publisher ? $bounty->publisher->name : 'Unknown Brand',
                    'brand_avatar' => $bounty->publisher ? $bounty->publisher->avatar : null, // Sesuaikan nama kolom avatar di tabel user
    
                    'remaining_budget' => (double) $bounty->remaining_budget,
                    'total_budget' => (double) $bounty->reward_amount,
                    'min_views' => $bounty->min_views,
                    'status' => $bounty->status,
                    'deadline' => $bounty->deadline_at,
                    'published_at' => $bounty->created_at->diffForHumans(), // Contoh: "2 hours ago"
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $campaigns
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mengambil data: ' . $e->getMessage()
        ], 500);
    }
});

// --- 1. API GET DETAIL CAMPAIGN (LENGKAP) ---
Route::get('/campaigns/{id}', function ($id) {
    try {
        $bounty = Bounty::with('publisher')->find($id);

        if (!$bounty) {
            return response()->json(['status' => 'error', 'message' => 'Campaign tidak ditemukan'], 404);
        }

        // Format data agar mudah dibaca Flutter
        $data = [
            'id' => $bounty->id,
            'title' => $bounty->title,
            'description' => $bounty->description, // Deskripsi panjang
            'company' => $bounty->publisher ? $bounty->publisher->name : 'Unknown',
            'company_avatar' => $bounty->publisher ? $bounty->publisher->avatar : null,
            'category' => $bounty->category,
            'platform' => $bounty->platform, // Array: ['tiktok', 'instagram']
            'thumbnail' => $bounty->image_path ? asset('storage/' . $bounty->image_path) : null,
            'source_link' => $bounty->source_link,
            'deadline' => $bounty->deadline_at ? $bounty->deadline_at->format('d M Y') : '-',
            'published_at' => $bounty->created_at->diffForHumans(),
            'applicants' => $bounty->submissions->count(), // Jumlah pelamar
            'total_budget' => (double) $bounty->reward_amount, // Total Awal 
            'remaining_budget' => (double) $bounty->remaining_budget,
            'min_views' => $bounty->min_views,
            'rules' => $bounty->rules, // JSON Tiers (Target Views & Reward)
            'required_hashtags' => $bounty->required_hashtags ?? "#Clipsquad #Cuan", // <--- TAMBAHAN
            'verification_code' => "CS-" . rand(1000, 9999),
            'status' => $bounty->status,
        ];

        return response()->json(['status' => 'success', 'data' => $data]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

// --- 2. API POST SUBMISSION (UPLOAD BUKTI) ---
Route::post('/campaigns/{id}/submit', function (Request $request, $id) {
    // 1. FUNGSI PEMBANTU (Helper) untuk Ekstrak ID
    $extractId = function ($url) {
        $url = trim($url);
        Log::info("=== Memulai Deteksi Link ===");
        Log::info("URL Awal: " . $url);
        // 1. JINAKKAN LINK PENDEK (PAKAI cURL AGAR LEBIH SAKTI)
        if (str_contains($url, 'vt.tiktok.com') || str_contains($url, 'reels/') || str_contains($url, 'youtu.be/') || str_contains($url, 't.co')) {
            try {
                Log::info("Mencoba menjinakkan link pendek via cURL...");

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, true);    // Kita butuh header
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ikuti redirect
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_NOBODY, true);     // Gak usah download body-nya, berat
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // Naikkan dikit biar gak gampang timeout
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

                $response = curl_exec($ch);
                $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // Ambil URL tujuan akhir
                curl_close($ch);

                if ($effectiveUrl) {
                    $url = $effectiveUrl;
                    Log::info("Link Berhasil Dijinakkan menjadi: " . $url);
                }
            } catch (\Exception $e) {
                Log::error("Error cURL: " . $e->getMessage());
            }
        }

        // 2. EKSTRAK ID (Setelah link sudah dipastikan format panjang)
        // Regex youtube
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=|shorts/)|youtu\.be/)([^"&?/ ]{11})%i', $url, $matches)) {
            Log::info("Platform Terdeteksi: YouTube dengan ID " . $matches[1]);
            return ['id' => $matches[1], 'platform' => 'youtube'];
        }
        // Regex TikTok
        if (preg_match('/(?:video|photo)\/(\d+)/', $url, $matches)) {
            Log::info("Platform Terdeteksi: TikTok dengan ID " . $matches[1]);
            return ['id' => $matches[1], 'platform' => 'tiktok'];
        }

        // Regex Instagram Reels
        if (preg_match('/(?:reels?|p)\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            Log::info("Platform Terdeteksi: Instagram dengan ID " . $matches[1]);
            return ['id' => $matches[1], 'platform' => 'instagram'];
        }

        return null;
    };

    // 2. VALIDASI AWAL
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'video_link' => 'required|url',
        'current_views' => 'required|integer',
        'analytics_file' => 'required|file|max:102400',
        'linked_account_id' => 'required|exists:linked_accounts,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
    }

    try {
        $bounty = Bounty::find($id);

        // 3. PANGGIL POLISI (Gunakan variabel $extractId)
        $result = $extractId($request->video_link);

        if (!is_array($result)) {
            Log::error("Submit Gagal: Sistem tidak mengenali link " . $request->video_link);
            return response()->json([
                'status' => 'error',
                'message' => 'Sistem gagal mengenali link video. Pastikan link TikTok/Reels/YouTube benar!'
            ], 422);
        }

        $videoId = $result['id'];
        $detectedPlatform = $result['platform']; // Ini string: 'tiktok', 'youtube', dll

        // 🔥 FIX: Tangani jika $bounty->platform adalah array atau string
        // --- 1. DEFINISIKAN VARIABLE (Penting agar tidak error) ---
        // Kita ambil data platform dari database dan ubah menjadi array
        $rawRequired = $bounty->platform;
        $allowedPlatforms = [];

        if (is_array($rawRequired)) {
            $allowedPlatforms = array_map('strtolower', $rawRequired);
        } else {
            // Jika di database berupa string "tiktok, instagram", kita pecah jadi array
            $allowedPlatforms = explode(',', strtolower((string) $rawRequired));
        }

        // --- 2. JALANKAN POLISI (Pengecekan) ---
        $isAllowed = false;
        foreach ($allowedPlatforms as $required) {
            $required = trim($required); // Bersihkan spasi jika ada

            // Logika Fleksibel: Cocokkan 'instagram' dengan 'instagram_reels'
            if (str_contains($required, $detectedPlatform) || str_contains($detectedPlatform, $required)) {
                $isAllowed = true;
                break;
            }
        }

        // --- 3. EKSEKUSI PENOLAKAN ---
        if (!$isAllowed) {
            Log::warning("Platform Tidak Cocok. Dicari: " . json_encode($allowedPlatforms) . " | Ditemukan: " . $detectedPlatform);
            return response()->json([
                'status' => 'error',
                'message' => "Link tidak sesuai! Misi ini mewajibkan platform " . strtoupper(implode(' / ', $allowedPlatforms))
            ], 422);
        }

        // Cari apakah video_id ini sudah pernah disetor
        $existing = Submission::where('video_id', $videoId)->first();

        if ($existing) {
            // JIKA PEMILIK BEDA -> BLOKIR (ANTI-TIKUNG)
            if ($existing->clipper_id != $request->user_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Link video ini sudah diklaim clipper lain. Gunakan video original milikmu sendiri!'
                ], 403);
            }

            // JIKA PEMILIK SAMA -> Kita biarkan lanjut untuk meng-UPDATE data views
            // (Nanti di bawah kita buat logika Update jika record sudah ada)
        }

        if ($bounty->remaining_budget < 100) {
            return response()->json(['status' => 'error', 'message' => 'Maaf, budget campaign ini sudah habis!'], 400);
        }

        // 4. UPLOAD FILE (Versi Jalur Darurat - Bypass Service Provider)
$analyticsPath = null;
if ($request->hasFile('analytics_file')) {
    $file = $request->file('analytics_file');

    if (!$file->isValid()) {
        return response()->json(['success' => false, 'message' => 'Video rusak atau melebihi limit server.'], 400);
    }

    try {
        Log::info('🚀 [JALUR MANUAL] Mengirim ke Cloudinary... Size: ' . $file->getSize());

        // 🔥 KITA BYPASS SERVICE PROVIDER: Langsung buat instance SDK
        // Kita ambil URL langsung dari config yang tadi sudah teruji di Tinker
        $cloudinary = new Cloudinary(config('cloudinary.cloud_url'));

        // Di SDK Manual, kita pakai uploadApi()
        $upload = $cloudinary->uploadApi()->upload($file->getRealPath(), [
            'folder'        => 'analytics',
            'resource_type' => 'video', // Pakai 'auto' jika ingin otomatis deteksi gambar/video
        ]);

        // SDK Manual mengembalikan Array, bukan Object
        if (isset($upload['secure_url'])) {
            $analyticsPath = $upload['secure_url'];
            Log::info('✅ UPLOAD SUKSES! URL: ' . $analyticsPath);
        } else {
            throw new \Exception("Gagal mendapatkan Secure URL dari Cloudinary.");
        }

    } catch (\Exception $e) {
        Log::error('❌ [FATAL ERROR UPLOAD]: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal upload: ' . $e->getMessage()
        ], 500);
    }
}

        // 5. SIMPAN ATAU UPDATE DATABASE (Menggunakan updateOrCreate)
        $submission = Submission::updateOrCreate(
            [
                'video_id' => $videoId, // Cari berdasarkan ID ini
                'clipper_id' => $request->user_id
            ],
            [
                'bounty_id' => $id,
                'linked_account_id' => $request->linked_account_id,
                'video_url' => $request->video_link,
                'views_count' => $request->current_views,
                'caption_content' => $request->caption_content,
                'analytics_path' => $analyticsPath,
                'status' => 'pending',
                'payout_status' => 'unpaid',
                'verification_code' => 'APP-' . Str::random(6),
            ]
        );

        // 6. NOTIFIKASI KE PUBLISHER
        try {
            $publisher = $bounty->publisher;
            if ($publisher) {
                $publisher->notify(new MissionActivityNotification($submission, 'submission_in_review'));
            }
        } catch (\Exception $notifError) {
            Log::error("Gagal kirim notif: " . $notifError->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Submission berhasil dan sedang di proses!',
            'data' => $submission
        ]);

    } catch (\Throwable $e) {
        Log::error("GAGAL SUBMIT: " . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Server Crash: ' . $e->getMessage()], 500);
    }
});

Route::get('/cek-config', function () {
    return response()->json([
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
    ]);
});

Route::get('/my-submissions', function (Request $request) {
    // Paksa user_id jadi Integer agar sinkron dengan Database
    $userId = (int) $request->query('user_id');

    if (!$userId) {
        return response()->json(['status' => 'error', 'message' => 'ID Kosong'], 400);
    }

    try {
        $submissions = App\Models\Submission::where('clipper_id', $userId)
            ->with(['bounty']) // Mengambil data relasi
            ->latest()
            ->get();

        // LOG untuk debug di terminal Laravel (php artisan serve)
        \Log::info("User $userId minta data. Ketemu: " . $submissions->count());

        return response()->json([
            'status' => 'success',
            'data' => $submissions
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});






Route::get('/campaigns', [BountyController::class, 'index']);
Route::get('/categories', [BountyController::class, 'getCategories']);
Route::get('/leaderboard', [LeaderboardController::class, 'index']);

