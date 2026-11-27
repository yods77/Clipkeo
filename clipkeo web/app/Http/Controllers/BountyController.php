<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Submission;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\MissionActivityNotification;
use App\Notifications\AdminActivityNotification;

class BountyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. INDEX (DAFTAR CAMPAIGN)
    |--------------------------------------------------------------------------
    | Menampilkan daftar campaign yang AKTIF di halaman utama Clipper.
    | Filter: Status Active, Pencarian Judul, dan Kategori.
    */
    public function index(Request $request)
    {
        $query = Bounty::with('publisher');

        // LOGIKA 1: FILTER STATUS
        // Hanya tampilkan yang Active (Tayang) atau Completed (Baru selesai 30 menit lalu)
        $query->where(function ($q) {
            $q->where('status', 'active')
                ->orWhere(function ($subQuery) {
                    $subQuery->where('status', 'completed')
                        ->where('updated_at', '>=', Carbon::now()->subMinutes(30));
                });
        });

        // LOGIKA 2: PENCARIAN
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('publisher', function ($pub) use ($search) {
                        $pub->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // LOGIKA 3: FILTER KATEGORI
        if ($request->has('category') && $request->filled('category')) {
            $category = $request->category;
            if ($category !== 'Semua') {
                $query->where('category', $category);
            }
        }

        $bounties = $query->latest()->get();

        $mySocials = collect([]);
        if (auth()->check() && auth()->user()->role === 'clipper') {
            $mySocials = auth()->user()->linkedAccounts;
        }

        return view('bounties.index', compact('bounties', 'mySocials'));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. CREATE (FORMULIR PEMBUATAN)
    |--------------------------------------------------------------------------
    | Menampilkan halaman form untuk Publisher membuat campaign baru.
    */
    public function create()
    {
        $this->authorizePublisher();

        return view('bounties.create');
    }

    /*
    |--------------------------------------------------------------------------
    | 3. STORE (PROSES SIMPAN & PENGAJUAN)
    |--------------------------------------------------------------------------
    | [UPDATE KEBIJAKAN BARU] FEE ADMIN 0% (GRATIS).
    | Publisher hanya membayar sesuai Total Budget yang diinput.
    */
    public function store(Request $request)
    {
        $this->authorizePublisher();

        // A. Validasi Input Dasar
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'required|string',
            'source_link' => 'required|url',
            'deadline_at' => 'required|date|after:today',
            'platform' => 'required|array|min:1',
            'platform.*' => 'in:tiktok,youtube_shorts,instagram_reels',
            'distribution_strategy' => 'required|in:performance_tiered,cpm',
            'reward_amount' => 'required|numeric|min:50000',
            'rate_per_view' => 'required_if:distribution_strategy,cpm|nullable|numeric|min:0.1',
            'min_views' => 'required_if:distribution_strategy,cpm|nullable|integer|min:1000',
            'tiers' => 'required_if:distribution_strategy,performance_tiered|array',
            'tiers.*.target_views' => 'required_if:distribution_strategy,performance_tiered|integer|min:1000',
            'tiers.*.reward' => 'required_if:distribution_strategy,performance_tiered|numeric|min:1000',
        ]);

        $user = Auth::user();

        // =========================================================================
        // [SECURITY UPDATE] VALIDASI LINK MATERI (Publisher)
        // =========================================================================
        $allowedSourceDomains = [
            'drive.google.com', 'docs.google.com',
            'dropbox.com', 'www.dropbox.com',
            'onedrive.live.com', '1drv.ms',
            'we.tl', 'wetransfer.com',
            'tiktok.com', 'www.tiktok.com',
            'instagram.com', 'www.instagram.com',
            'youtube.com', 'www.youtube.com', 'youtu.be'
        ];

        $urlMateri = $request->source_link;
        $hostMateri = strtolower(parse_url($urlMateri, PHP_URL_HOST) ?? '');

        $isSafeSource = false;
        foreach ($allowedSourceDomains as $domain) {
            if (str_contains($hostMateri, $domain)) {
                $isSafeSource = true;
                break;
            }
        }

        if (!$isSafeSource) {
            return back()->with('error', 'Link Materi Ditolak! Demi keamanan Clipper, mohon gunakan link dari: Google Drive, Dropbox, WeTransfer, atau Sosmed resmi.');
        }

        // =========================================================================
        // [UPDATE PROMO] PENGHAPUSAN ADMIN FEE (0%)
        // =========================================================================
        $totalBudget = $request->reward_amount;
        $adminFee = 0; // Fee diset ke 0
        $totalCost = $totalBudget + $adminFee; // Total Bayar = Budget Murni

        // C. Cek Saldo Publisher
        if ($user->wallet_balance < $totalCost) {
            return back()->with('error', 'Saldo tidak cukup! Anda butuh Rp ' . number_format($totalCost) . '. Silakan Top Up dahulu.');
        }

        // D. Siapkan Data JSON Rules
        $rulesJson = null;
        if ($request->distribution_strategy === 'performance_tiered') {
            $rulesJson = ['tiers' => $request->tiers];
        }

        // E. Upload Gambar Banner
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('bounty-images', 'public');
        }

        // F. Transaksi Database (Atomic)
        $bounty = DB::transaction(function () use ($request, $user, $totalCost, $totalBudget, $adminFee, $imagePath, $rulesJson) {
            
            // 1. Potong Saldo Publisher (Sesuai Budget Murni)
            $user->decrement('wallet_balance', $totalCost);

            // 2. Buat Data Campaign
            $newBounty = Bounty::create([
                'publisher_id' => $user->id,
                'image_path' => $imagePath,
                'title' => $request->title,
                'category' => $request->category,
                'slug' => Str::slug($request->title) . '-' . time(),
                'description' => $request->description,
                'source_link' => $request->source_link,
                'reward_amount' => $totalBudget,
                'remaining_budget' => $totalBudget,
                'distribution_strategy' => $request->distribution_strategy,
                'rate_per_view' => ($request->distribution_strategy === 'cpm') ? $request->rate_per_view : null,
                'min_views' => ($request->distribution_strategy === 'cpm') ? $request->min_views : 0,
                'rules' => $rulesJson,
                'platform' => $request->platform,
                'currency' => 'IDR',
                'winner_count' => 0,
                'required_hashtags' => $request->required_hashtags,
                'required_mentions' => $request->required_mentions,
                'deadline_at' => $request->deadline_at,
                'status' => 'pending_review',
            ]);

            // 3. Catat Transaksi (Hold Budget)
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'bounty_hold',
                'amount' => $totalBudget,
                'currency' => 'IDR',
                'description' => 'Budget Campaign (Hold): ' . $request->title,
                'reference_id' => 'HLD-' . date('Ymd') . '-' . $newBounty->id . '-' . Str::random(4),
                'status' => 'success'
            ]);

            // 4. Catat Transaksi Fee (Hanya jika Fee > 0)
            // Karena sekarang Fee 0, blok ini tidak akan dieksekusi.
            if ($adminFee > 0) {
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'platform_fee',
                    'amount' => $adminFee,
                    'currency' => 'IDR',
                    'description' => 'Service Fee: ' . $request->title,
                    'reference_id' => 'FEE-' . date('Ymd') . '-' . $newBounty->id . '-' . Str::random(4),
                    'status' => 'success'
                ]);
            }

            return $newBounty;
        });

        // G. Kirim Notifikasi
        $user->notify(new MissionActivityNotification($bounty, 'bounty_pending_review'));

        $admins = User::where('role', 'admin')->get();
        foreach($admins as $admin) {
            /** @var \App\Models\User $admin */
            $admin->notify(new AdminActivityNotification($bounty, 'bounty_pending_review'));
        }

        return redirect()->route('dashboard')->with('success', 'Campaign berhasil dibuat & sedang ditinjau oleh Admin.');
    }

    /*
    |--------------------------------------------------------------------------
    | 4. EDIT (FORM EDIT CAMPAIGN)
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $this->authorizePublisher();

        $bounty = Bounty::findOrFail($id);
        if ($bounty->publisher_id !== Auth::id()) abort(403);
        return view('bounties.edit', compact('bounty'));
    }

    /*
    |--------------------------------------------------------------------------
    | 5. UPDATE (PROSES UPDATE DATA)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $this->authorizePublisher();

        $bounty = Bounty::findOrFail($id);
        if ($bounty->publisher_id !== Auth::id()) abort(403);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'required|string',
            'platform' => 'required|array',
            'source_link' => 'required|url',
            'image' => 'nullable|image|max:2048',
            'deadline_at' => 'required|date|after:today',
        ]);

        if ($request->hasFile('image')) {
            $bounty->image_path = $request->file('image')->store('bounty-images', 'public');
        }

        $bounty->update([
            'title' => $request->title,
            'category' => $request->category,
            'slug' => Str::slug($request->title) . '-' . time(),
            'description' => $request->description,
            'source_link' => $request->source_link,
            'platform' => $request->platform,
            'deadline_at' => $request->deadline_at,
            'required_hashtags' => $request->required_hashtags,
            'required_mentions' => $request->required_mentions,
        ]);

        return redirect()->route('dashboard')->with('success', 'Data Campaign diperbarui!');
    }

    /*
    |--------------------------------------------------------------------------
    | 6. DESTROY (BATALKAN CAMPAIGN & REFUND)
    |--------------------------------------------------------------------------
    | [UPDATE] Logic Refund disesuaikan: Mengembalikan 100% Sisa Budget
    | tanpa perhitungan fee, karena fee awal adalah 0.
    */
    public function destroy($id)
    {
        $this->authorizePublisher();

        $result = DB::transaction(function () use ($id) {
            // Lock the campaign so concurrent cancellation requests cannot refund it twice.
            $bounty = Bounty::whereKey($id)->lockForUpdate()->firstOrFail();

            $publisherId = (int) ($bounty->publisher_id ?? $bounty->user_id);
            if ($publisherId !== (int) auth()->id()) {
                abort(403);
            }

            if ($bounty->status === 'completed' || (float) $bounty->remaining_budget <= 100) {
                return 'not_cancellable';
            }

            if ($bounty->status === 'rejected') {
                $bounty->delete();
                return 'deleted';
            }

            $activeSubs = $bounty->submissions()
                ->whereIn('status', ['pending', 'approved'])
                ->where('payout_status', 'unpaid')
                ->lockForUpdate()
                ->exists();

            if ($activeSubs) {
                return 'active_submissions';
            }

            $remainingBudget = (float) $bounty->remaining_budget;

            if ($remainingBudget > 0) {
                $publisher = User::whereKey($publisherId)->lockForUpdate()->firstOrFail();
                $publisher->increment('wallet_balance', $remainingBudget);

                Transaction::create([
                    'user_id' => $publisherId,
                    'type' => Transaction::TYPE_BOUNTY_REFUND,
                    'amount' => $remainingBudget,
                    'currency' => 'IDR',
                    'description' => "Refund Budget Campaign: {$bounty->title}",
                    'reference_id' => 'REF-' . date('Ymd') . '-' . $bounty->id . '-' . Str::random(4),
                    'status' => 'success',
                ]);
            }

            // Clear the held amount so the refunded budget cannot be counted or refunded again.
            $bounty->update([
                'status' => 'completed',
                'remaining_budget' => 0,
            ]);

            return 'refunded';
        });

        return match ($result) {
            'deleted' => back()->with('success', 'Riwayat campaign yang ditolak berhasil dihapus.'),
            'active_submissions' => back()->with('error', 'Selesaikan dulu submission yang pending/belum dibayar.'),
            'not_cancellable' => back()->with('error', 'Tidak bisa dibatalkan (sudah selesai/budget habis).'),
            default => back()->with('success', 'Campaign dibatalkan & sisa budget dikembalikan ke saldo.'),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | 7. SUBMISSIONS (LIST PELAMAR PER USER)
    |--------------------------------------------------------------------------
    | Halaman Publisher untuk melihat siapa saja yang mengirim video.
    */
    public function submissions($id)
    {
        $this->authorizePublisher();

        $bounty = Bounty::findOrFail($id);

        // Validasi Pemilik
        if ($bounty->publisher_id !== auth()->id())
            abort(403);

        // Update waktu terakhir dilihat
        $bounty->update(['last_viewed_at' => now()]);

        // Grouping berdasarkan Clipper
        $groupedSubmissions = Submission::where('bounty_id', $id)
            ->select('clipper_id', DB::raw('MAX(created_at) as latest_activity')) 
            ->groupBy('clipper_id')
            ->orderByDesc('latest_activity')
            ->paginate(10); 

        // Isi detail data untuk setiap grup
        $groupedSubmissions->getCollection()->transform(function ($group) use ($id) {
            
            $allVideos = Submission::with('user')
                ->where('bounty_id', $id)
                ->where('clipper_id', $group->clipper_id)
                ->orderByDesc('created_at')
                ->get();

            $group->user = $allVideos->first()->user; 
            $group->submissions_list = $allVideos;    
            $group->total_views_all = $allVideos->sum('views_count'); 
            $group->total_earnings_all = $allVideos->sum('prize_amount'); 
            
            $hasPending = $allVideos->contains('status', 'pending');
            $group->general_status = $hasPending ? 'pending' : 'approved';

            return $group;
        });

        return view('bounties.submissions', compact('bounty', 'groupedSubmissions'));
    }

    /*
    |--------------------------------------------------------------------------
    | 8. SHOW (DETAIL CAMPAIGN PUBLIC)
    |--------------------------------------------------------------------------
    | Halaman detail campaign yang dilihat oleh Clipper.
    */
    private function authorizePublisher(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'publisher',
            403,
            'Fitur ini hanya tersedia untuk Publisher.'
        );
    }

    public function show(Bounty $bounty)
    {
        $bounty = Bounty::with('user')->findOrFail($bounty->id);
        $user = auth()->user();

        $hasSubmitted = false;
        $mySocials = collect([]); // Default kosong untuk guest/publisher

        if (auth()->check() && $user->role === 'clipper') {
            
            // Cek apakah user pernah submit
            $hasSubmitted = Submission::where('bounty_id', $bounty->id)
                ->where('clipper_id', $user->id)
                ->exists();

            // [UPDATE PENTING] Ambil daftar akun sosmed terhubung user
            // Ini akan dipakai di view 'bounties.show' untuk dropdown select
            $mySocials = $user->linkedAccounts; 
        }

        return view('bounties.show', compact('bounty', 'hasSubmitted', 'mySocials'));
    }
}