<?php
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Submission;
use App\Models\DailyStat;
use App\Helpers\FcmHelper;

// --- IMPORT CONTROLLERS ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LinkedAccountController;
use App\Http\Controllers\BountyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\PayoutMethodController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AdminMonitorController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SecurityPinController;
use App\Http\Controllers\AdminBountyController;
use App\Http\Controllers\Auth\PasswordResetController;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Bisa Diakses Tanpa Login)
|--------------------------------------------------------------------------
| Bagian ini berisi halaman-halaman yang boleh dilihat oleh tamu (Guest).
| Seperti Landing Page, Login Google, dan Halaman Statis.
*/

// Halaman Depan (Landing Page)
Route::get('/', function () {
    // Logika mengambil data clipper terbaru, withdrawal terbaru, dan statistik
    $recentClippers = \App\Models\User::where('role', 'clipper')
        ->whereNotNull('avatar_url')
        ->where('avatar_url', '!=', '')
        ->latest()
        ->take(4)
        ->get();

    // Fallback jika clipper kurang dari 4
    if ($recentClippers->count() < 4) {
        $moreClippers = \App\Models\User::where('role', 'clipper')
            ->whereNotIn('id', $recentClippers->pluck('id'))
            ->latest()
            ->take(4 - $recentClippers->count())
            ->get();
        $recentClippers = $recentClippers->merge($moreClippers);
    }

    $recentWithdrawals = \App\Models\Transaction::where('type', 'withdrawal')
        ->where('status', 'success')
        ->whereHas('user', function ($q) {
            $q->where('role', 'clipper');
        })
        ->with('user')
        ->latest()
        ->take(10)
        ->get();

    $bounties = \App\Models\Bounty::where('status', 'active')->latest()->take(6)->get();
    $totalPayout = \App\Models\Transaction::where('type', 'payout')->sum('amount');
    $totalClipper = \App\Models\User::where('role', 'clipper')->count();
    $activeBounties = \App\Models\Bounty::where('status', 'active')->count();
    $topPrizePool = \App\Models\Bounty::where('status', 'active')->sum('reward_amount');
    

    return view('welcome', compact('totalPayout', 'totalClipper', 'recentClippers', 'recentWithdrawals', 'activeBounties', 'topPrizePool'));
});

Route::post('/password/otp-send', [PasswordResetController::class, 'sendOtp'])->name('password.otp.send');
Route::post('/password/otp-reset', [PasswordResetController::class, 'resetPassword'])->name('password.otp.reset');

// --- AUTHENTICATION (GOOGLE & LOGOUT) ---

// Redirect User ke Halaman Login Google
Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('auth.google');

// Callback saat User kembali dari Google
Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->getEmail())->first();
        $avatar = $googleUser->getAvatar();

        if (!$user) {
            // Register User Baru jika belum ada
            $generatedUsername = Str::slug($googleUser->getName()) . '-' . rand(100, 999);
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'username' => $generatedUsername,
                'google_id' => $googleUser->getId(),
                'avatar_url' => $avatar,
                'password' => bcrypt(Str::random(16)),
                'role' => 'clipper', // Role kosong agar user memilih role nanti
                'email_verified_at' => now()
            ]);
        } else {
            // Update data user lama
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar_url' => $avatar
            ]);
        }

        Auth::login($user);

        // Jika role belum dipilih, arahkan ke halaman pemilihan role
        if (empty($user->security_pin)) {
            return redirect()->route('pin.setup'); 
        }

        return redirect('/dashboard');

    } catch (\Exception $e) {
        return redirect('/login')->with('error', 'Gagal login: ' . $e->getMessage());
    }
});

// Logout Custom
Route::any('/logout', function () {
    Auth::guard('web')->logout();
    Session::invalidate();
    Session::regenerateToken();
    return redirect('/');
})->name('logout');

// Memuat route bawaan Laravel Breeze/Auth
require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (Hanya User Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // --- A. ONBOARDING (PILIH ROLE) ---
    // (Bebas PIN, karena user baru belum tentu punya PIN/Role)
    Route::get('/select-role', function () {
        if (auth()->user()->role)
            return redirect('/dashboard');
        return view('auth.select-role');
    })->name('role.selection');

    Route::post('/select-role', function (Illuminate\Http\Request $request) {
        $request->validate(['role' => 'required|in:clipper,publisher']);
        auth()->user()->update(['role' => $request->role]);
        return redirect('/dashboard')->with('success', 'Selamat datang! Akun Anda telah aktif.');
    })->name('role.save');

    // --- B. SETUP PIN (WAJIB DILUAR GROUP has.pin) ---
    // Kalau ini dimasukkan ke dalam, akan terjadi "Looping Redirect" (Error)
    Route::get('/setup-pin', [SecurityPinController::class, 'show'])->name('pin.setup');
    Route::post('/setup-pin', [SecurityPinController::class, 'store'])->name('pin.store');
    


    // ==================================================================
    //  ZONA WAJIB PIN (SEMUA FITUR DI BAWAH INI TERPROTEKSI)
    // ==================================================================
    Route::middleware(['has.pin'])->group(function () {
        
        // 1. DASHBOARD UTAMA
        Route::get('/dashboard', function () {
            if (!auth()->user()->role) {
                return redirect()->route('role.selection');
            }
            return app(DashboardController::class)->index();
        })->name('dashboard');

        // 2. USER PROFILE
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::put('/profile/pin', [ProfileController::class, 'updatePin'])->name('profile.pin.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update.avatar');

        // 3. FITUR UTAMA (CLIPPER & PUBLISHER)
        
        // Linked Accounts
        Route::get('/linked-accounts', [LinkedAccountController::class, 'index'])->name('linked-accounts.index');
        Route::post('/linked-accounts', [LinkedAccountController::class, 'store'])->name('linked-accounts.store');
        Route::delete('/linked-accounts/{id}', [LinkedAccountController::class, 'destroy'])->name('linked-accounts.destroy');

        // Bounties
        Route::resource('bounties', BountyController::class);
        Route::get('/my-bounties/{id}/submissions', [BountyController::class, 'submissions'])->name('bounties.submissions');

        // Submissions
        Route::get('/my-submissions', [SubmissionController::class, 'mySubmissions'])->name('submissions.my');
        Route::post('/bounties/{bounty}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
        Route::post('/submissions/{submission}/approve', [SubmissionController::class, 'approve'])->name('submissions.approve');
        Route::post('/submissions/{submission}/reject', [SubmissionController::class, 'reject'])->name('submissions.reject');
        Route::post('/submissions/{id}/pick-winner', [SubmissionController::class, 'pickWinner'])->name('submissions.pick-winner');
        Route::post('/bounties/{bounty}/submission/{submission}/approve', [SubmissionController::class, 'approve'])->name('bounty.submission.approve');

        // Deposits
        Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
        Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
        Route::get('/deposits/{deposit}', [DepositController::class, 'show'])->name('deposits.show');
        Route::put('/deposits/{deposit}', [DepositController::class, 'update'])->name('deposits.update');
        Route::post('/deposits/{id}/approve', [DepositController::class, 'approve'])->name('deposits.approve')->middleware('admin');
        Route::post('/deposits/{id}/reject', [DepositController::class, 'reject'])->name('deposits.reject')->middleware('admin');

        // Withdrawals
        Route::get('/withdraw', [WithdrawalController::class, 'create'])->name('withdrawals.create');
        Route::post('/withdraw', [WithdrawalController::class, 'store'])->name('withdrawals.store');
        
        // Admin Approval Withdrawal (Bisa dipisah ke middleware admin kalau mau lebih spesifik)
        Route::post('/admin/withdrawals/{id}/approve', [WithdrawalController::class, 'approve'])->name('admin.withdrawals.approve');
        Route::post('/admin/withdrawals/{id}/reject', [WithdrawalController::class, 'reject'])->name('admin.withdrawals.reject');

        // Payout Methods
        Route::get('/my-banks', [PayoutMethodController::class, 'index'])->name('payout-methods.index');
        Route::post('/my-banks', [PayoutMethodController::class, 'store'])->name('payout-methods.store');
        Route::delete('/my-banks/{id}', [PayoutMethodController::class, 'destroy'])->name('payout-methods.destroy');

        // Wallet & Transactions
        Route::get('/wallet', [TransactionController::class, 'index'])->name('wallet.index');

        // Notifications
        Route::get('/notification/open/{id}', [NotificationController::class, 'open'])->name('notifications.open');
        Route::get('/notification/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

        // Admin Pages
        Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
            Route::post('/withdrawals/{id}/approve',[WithdrawalController::class, 'approve'])->name('withdrawals.approve');
            Route::post('/withdrawals/{id}/reject',[WithdrawalController::class, 'reject'])->name('withdrawals.reject');
            Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
            Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
            Route::delete('/users/{id}', [AdminController::class, 'userDestroy'])->name('users.destroy');
            Route::get('/monitor', [AdminMonitorController::class, 'index'])->name('monitor.index');
            Route::get('/moderation', [AdminBountyController::class, 'index'])->name('bounties.review');
            Route::post('/moderation/{id}/approve', [AdminBountyController::class, 'approve'])->name('bounties.approve');
            Route::post('/moderation/{id}/reject', [AdminBountyController::class, 'reject'])->name('bounties.reject');
            Route::get('/monitoring-submissions', [AdminBountyController::class, 'monitoring'])->name('monitoring');
            Route::patch('/submission/{id}/takeover-approve', [AdminBountyController::class, 'takeoverApprove'])->name('submission.takeover.approve');
            Route::patch('/submission/{id}/takeover-reject', [AdminBountyController::class, 'takeoverReject'])->name('submission.takeover.reject');
            Route::get('/submission/{id}/takeover-view', [AdminBountyController::class, 'takeoverView'])->name('submission.takeover.view');
        });

        // Publisher Analytics
        Route::get('/analytics/overview', [AnalyticsController::class, 'overview'])->name('analytics.overview');
        Route::get('/bounties/{id}/analytics', [AnalyticsController::class, 'campaign'])->name('bounties.analytics');

        // Developer Tools
        Route::post('/dev/add-xp', function () {
            $user = \Illuminate\Support\Facades\Auth::user();
            $user->addReputation(100);
            return back()->with('success', 'Mantap! 100 XP berhasil ditambahkan.');
        })->name('dev.add-xp');

        Route::get('/leaderboard', function () {
            $leaderboard = User::with('badges')->orderBy('reputation_points', 'desc')->take(50)->get();
            return view('leaderboard', ['leaderboard' => $leaderboard]);
        })->name('leaderboard');

        Route::get('/submission/report/{id}', [SubmissionController::class, 'showReport'])->name('submission.report');

    });
    
    // Route untuk Cek Link Real-time (AJAX)
    Route::post('/check-video-link', [SubmissionController::class, 'checkLinkAvailability'])
        ->name('api.check.video.link')
        ->middleware('auth'); // Hanya user login yang boleh cek// --- END GROUP HAS.PIN ---

});

// --- DEVELOPER TOOLS (Opsional) ---
Route::post('/dev/add-xp', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    $user->addReputation(100);
    return back()->with('success', 'Mantap! 100 XP berhasil ditambahkan.');
})->name('dev.add-xp')->middleware(['auth']);

Route::get('/leaderboard', function () {
    $leaderboard = User::with('badges')->orderBy('reputation_points', 'desc')->take(50)->get();
    return view('leaderboard', ['leaderboard' => $leaderboard]);
})->name('leaderboard')->middleware(['auth']);

// Route Ganti Bahasa (ID/EN)
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id'])) {
        Session::put('locale', $locale);
    }
    return back();
})->name('lang.switch');

// Route Halaman Statis & Kontak
Route::post('/contact-us', [ContactController::class, 'store'])->name('contact.store');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/contact-us', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/about-us', [PageController::class, 'about'])->name('pages.about');


/*
|--------------------------------------------------------------------------
| 3. WEBHOOK ROUTES (EXTERNAL)
|--------------------------------------------------------------------------
| Route ini diakses oleh pihak luar (Xendit) untuk memberi notifikasi pembayaran.
| PENTING: Route ini harus dikecualikan dari CSRF Protection (di bootstrap/app.php).
*/
Route::post('/webhooks/xendit', [WebhookController::class, 'handle'])->name('webhooks.xendit');

// Route Khusus Nota Transaksi
Route::get('/invoice/{reference_id}', [InvoiceController::class, 'show'])->name('invoice.show')->middleware(['auth', 'verified', 'has.pin']);

Route::get('/kirim-notif-simpel', function () {
    // 1. Ambil User
    $user = User::whereNotNull('fcm_token')->first();

    // 2. TEMBAK NOTIFIKASI (CUMA 1 BARIS!) 🔥
    $hasil = FcmHelper::sendToUser(
        $user, 
        'Selamat Pagi Bos! ☕', 
        'Ini dikirim pakai FcmHelper. Kodingan jadi bersih banget!',
        ['type' => 'promo', 'id' => '99'] // Data tambahan (opsional)
    );

    return [
        'status' => 'Terkirim',
        'response' => $hasil
    ];
});


