<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications\MissionActivityNotification;
use App\Notifications\FinancialStatusNotification;
use App\Models\User;
class AdminBountyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. DAFTAR PENDING (MODERASI)
    |--------------------------------------------------------------------------
    | Menampilkan semua campaign yang butuh persetujuan Admin.
    | Data difilter hanya yang statusnya 'pending_review'.
    */
    public function index()
    {
        $pendingBounties = Bounty::with('publisher')
            ->where('status', 'pending_review')
            ->latest()
            ->get();

        return view('admin.bounties.review', compact('pendingBounties'));
    }

    /*
    |--------------------------------------------------------------------------
    | 2. APPROVE (TERBITKAN CAMPAIGN)
    |--------------------------------------------------------------------------
    | Mengubah status campaign menjadi 'active' agar muncul di halaman Clipper.
    | Publisher akan mendapatkan notifikasi lonceng "Disetujui".
    */
    public function approve($id)
    {
        $bounty = Bounty::findOrFail($id);

        if ($bounty->status !== 'pending_review') {
            return back()->with('error', 'Status tidak valid atau sudah diproses.');
        }

        // 1. Update Status jadi Active
        $bounty->update(['status' => 'active',
            'approved_at' => now()]);

        // 2. Kirim Notifikasi ke Publisher
        if ($bounty->publisher) {
            $bounty->publisher->notify(new MissionActivityNotification($bounty, 'campaign_approved'));
        }
        
        // 3. Kirim Notifikasi ke Clipper
        $clippers = User::where('role', 'clipper')->get();

        foreach ($clippers as $clipper) {
            $clipper->notify(new MissionActivityNotification($bounty, 'new_bounty_available'));
        }

        return back()->with('success', 'Campaign BERHASIL diterbitkan! Notifikasi dikirim ke Publisher.');
    }

    /*
    |--------------------------------------------------------------------------
    | 3. REJECT (TOLAK & REFUND)
    |--------------------------------------------------------------------------
    | Menolak campaign, mengembalikan saldo (Refund) ke Publisher, 
    | dan mengirim notifikasi lonceng dengan alasan penolakan.
    */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|min:5']);

        $bounty = Bounty::findOrFail($id);

        if ($bounty->status !== 'pending_review') {
            return back()->with('error', 'Status tidak valid atau sudah diproses.');
        }

        DB::transaction(function () use ($bounty, $request) {
            
            // [UPDATE] 1. Hitung Total Refund (Murni Budget Saja)
            // Karena tidak ada fee admin di awal, maka pengembalian = 100% sisa budget.
            $totalRefund = $bounty->remaining_budget; 
            
            // HAPUS PERHITUNGAN INI:
            // $fee = $budget * 0.10;              
            // $totalRefund = $budget + $fee;

            // 2. Update Status & Simpan Alasan Penolakan
            $bounty->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason
            ]);

            // 3. Kembalikan Saldo ke Wallet Publisher
            // Increment saldo hanya sebesar budget murni
            $bounty->publisher->increment('wallet_balance', $totalRefund);

            // 4. Catat Transaksi Refund di Database
            Transaction::create([
                'user_id' => $bounty->publisher_id,
                'type' => 'refund',
                'amount' => $totalRefund,
                'currency' => 'IDR',
                'description' => "Refund Penolakan Admin: {$bounty->title}",
                'reference_id' => 'REF-ADM-' . time() . '-' . $bounty->id,
                'status' => 'success'
            ]);

            // 5. Kirim Notifikasi Lonceng ke Publisher
            $bounty->publisher->notify(new MissionActivityNotification($bounty, 'campaign_rejected'));
        });

        return back()->with('success', 'Campaign DITOLAK. Saldo dikembalikan utuh (Tanpa Potongan) & Notifikasi dikirim.');
    }

    /*
    |--------------------------------------------------------------------------
    | 4. MONITORING SUBMISSION (Pending Review)
    |--------------------------------------------------------------------------
    | Menampilkan daftar video yang sudah disetor Clipper tapi belum
    | direview oleh Publisher.
    */
    public function monitoring()
    {
        $pendingSubmissions = \App\Models\Submission::with(['bounty.publisher', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc') // Yang paling lama antri muncul duluan
            ->paginate(15);

        return view('admin.bounties.monitoring', compact('pendingSubmissions'));
    }

    /*
    |--------------------------------------------------------------------------
    | 5. TAKEOVER APPROVE (FORCE ACC)
    |--------------------------------------------------------------------------
    | Admin menyetujui submission karena Publisher lambat merespon.
    */
    public function takeoverApprove(Request $request, $id)
    {
        // Gunakan Transaction agar data konsisten (Saldo, Status, History)
        return \DB::transaction(function () use ($request, $id) {
            
            // 1. Load Data
            // Gunakan lockForUpdate untuk mencegah race condition saat Admin & Publisher klik bersamaan
            $submission = \App\Models\Submission::with(['bounty', 'user'])->lockForUpdate()->findOrFail($id);
            $bounty = $submission->bounty;
            $clipper = $submission->user;

            // Cek Status (Hanya Pending yang boleh di-takeover)
            if ($submission->status !== 'pending') {
                return back()->with('error', 'Submission sudah diproses (Bukan status Pending).');
            }

            // 2. Tentukan Jumlah Views (Input Admin atau Default Views saat submit)
            // Admin bisa input 'views' manual dari form takeover, jika tidak ada pakai data lama
            $currentViews = $request->input('views', $submission->views_count);
            
            // Update History Grafik Harian (Agar sinkron dengan keputusan Admin)
            \App\Models\DailyStat::updateOrCreate(
                ['submission_id' => $submission->id, 'date' => now()->format('Y-m-d')],
                ['views_count'   => $currentViews]
            );

            // ------------------------------------------------------------------
            // 3. HITUNG REWARD (ADAPTASI DARI SUBMISSIONCONTROLLER)
            // ------------------------------------------------------------------
            $payAmount = 0;
            $note = 'Admin Takeover Reward';
            $isEligibleToPay = false;

            // --- SKENARIO A: CPM (Pay Per View + Portfolio Check) ---
            if ($bounty->distribution_strategy === 'cpm') {
                // Hitung total views semua video milik clipper ini di campaign ini
                $otherVideosViews = \App\Models\Submission::where('bounty_id', $bounty->id)
                    ->where('clipper_id', $submission->clipper_id)
                    ->where('id', '!=', $submission->id)
                    ->sum('views_count');
                
                $totalPortfolioViews = $otherVideosViews + $currentViews;

                // Cek Minimal Views Portfolio
                if ($totalPortfolioViews < $bounty->min_views) {
                    // Update views tapi status approved tanpa bayar
                    $submission->update([
                        'status' => 'approved',
                        'views_count' => $currentViews,
                        'notes' => 'Force Approved (Admin): Belum mencapai target portfolio minimal.'
                    ]);
                    
                    // Kirim notif tetap approved (walau belum cair)
                    $clipper->notify(new \App\Notifications\MissionActivityNotification($submission, 'submission_approved_by_admin'));
                    
                    return back()->with('info', "Video di-approve Admin ($currentViews views). Total portfolio ($totalPortfolioViews) belum mencapai target minimal ($bounty->min_views). Belum ada pembayaran.");
                }

                // Hitung selisih bayar
                $lastPaid = $submission->last_paid_views;
                $deltaViews = $currentViews - $lastPaid;

                if ($deltaViews > 0) {
                    $payAmount = floor($deltaViews * $bounty->rate_per_view);
                    $note = "CPM Reward (Takeover): +".number_format($deltaViews)." views";
                    $isEligibleToPay = true;
                }
            }

            // --- SKENARIO B: PERFORMANCE TIERED ---
            elseif ($bounty->distribution_strategy === 'performance_tiered') {
                $tiers = $bounty->rules['tiers'] ?? [];
                // Sort tier dari target tertinggi
                usort($tiers, fn($a, $b) => $b['target_views'] <=> $a['target_views']);

                $targetReward = 0;
                $hitTier = null;

                // Cari tier tertinggi yang ditembus
                foreach ($tiers as $tier) {
                    if ($currentViews >= $tier['target_views']) {
                        $targetReward = $tier['reward'];
                        $hitTier = $tier;
                        break; 
                    }
                }

                // Hitung selisih dari yang sudah pernah dibayar (jika ada)
                $alreadyPaid = $submission->prize_amount; 
                $payAmount = $targetReward - $alreadyPaid;

                if ($payAmount > 0) {
                    $note = "Tier Reward (Takeover): Target " . number_format($hitTier['target_views']) . " views";
                    $isEligibleToPay = true;
                }
            }

            // Jika tidak eligible bayar (misal views turun atau tidak capai target), tetap approve status
            if (!$isEligibleToPay || $payAmount <= 0) {
                $submission->update([
                    'status' => 'approved',
                    'views_count' => $currentViews,
                    'notes' => 'Force Approved (Admin): Views valid tapi belum ada penambahan reward.'
                ]);
                 // Kirim notif
                $clipper->notify(new \App\Notifications\MissionActivityNotification($submission, 'submission_approved_by_admin'));
                if ($bounty->publisher) {
                    $bounty->publisher->notify(new \App\Notifications\MissionActivityNotification($submission, 'admin_takeover_approval'));
                }
                
                return back()->with('success', 'Video berhasil di-takeover (Approved). Tidak ada saldo yang perlu ditransfer saat ini.');
            }

            // ------------------------------------------------------------------
            // 4. EKSEKUSI PEMBAYARAN (POTONG BUDGET & TRANSFER)
            // ------------------------------------------------------------------
            
            // Cek sisa budget campaign
            if ($bounty->remaining_budget < $payAmount) {
                $payAmount = $bounty->remaining_budget; // Bayar sisa recehan terakhir
                $note .= " (Partial/Sisa Budget)";
            }

            // Validasi budget 0
            if ($payAmount <= 0) {
                return back()->with('error', 'Gagal bayar: Budget campaign ini sudah habis total (Rp 0).');
            }

            // Kurangi Budget Bounty
            $bounty->decrement('remaining_budget', $payAmount);
            
            // Jika budget habis, tandai campaign selesai
            if ($bounty->remaining_budget <= 100) {
                $bounty->status = 'completed';
                $bounty->save();
            }

            // Update Submission
            $submission->update([
                'status'          => 'approved',
                'views_count'     => $currentViews,
                'last_paid_views' => $currentViews,
                'payout_status'   => ($bounty->remaining_budget <= 100) ? 'paid' : 'partial',
                'prize_amount'    => ($submission->prize_amount + $payAmount),
                'approved_at'     => now(),
                'notes'           => $note
            ]);

            // 🔥 AUTO-SWEEP: Fitur Tolak Masal jika Budget Habis (Copy dari SubmissionController)
            if ($bounty->remaining_budget < 1000) {
                $pendingSubmissions = \App\Models\Submission::where('bounty_id', $bounty->id)
                    ->where('status', 'pending')
                    ->with('user')
                    ->get();

                foreach ($pendingSubmissions as $pending) {
                    $pending->update([
                        'status' => 'rejected',
                        'rejection_reason' => '⚠️ SYSTEM AUTO-REJECT: Budget campaign habis setelah pembayaran terakhir (Admin Takeover).'
                    ]);
                    // Notif reject ke user lain
                    if ($pending->user) {
                        $pending->user->notify(new \App\Notifications\MissionActivityNotification($pending, 'submission_rejected'));
                    }
                }
            }

            // Transfer Uang ke Clipper
            if ($clipper) {
                $clipper->increment('wallet_balance', $payAmount);
                $clipper->addReputation(50); // Tambah XP karena sukses cair

                // Log Mutasi Clipper (Uang Masuk)
                \App\Models\Transaction::create([
                    'user_id'      => $clipper->id,
                    'type'         => 'reward',
                    'amount'       => $payAmount,
                    'description'  => $note, // e.g., "Tier Reward (Takeover)..."
                    'status'       => 'success',
                    'reference_id' => 'TKO-RWD-' . date('Ymd') . '-' . $submission->id,
                ]);

                // Notif Clipper
                $clipper->notify(new \App\Notifications\MissionActivityNotification($submission, 'submission_approved_by_admin'));
            }

            // Log Mutasi Publisher (Pencatatan Payout)
            \App\Models\Transaction::create([
                'user_id'      => $bounty->publisher_id,
                'type'         => 'payout',
                'amount'       => $payAmount,
                'description'  => "Payout Clipper via Admin Takeover ($note)",
                'status'       => 'success',
                'reference_id' => 'TKO-PAY-' . date('Ymd') . '-' . $submission->id,
            ]);

            if ($bounty->publisher) {
                $bounty->publisher->notify(new \App\Notifications\MissionActivityNotification($submission, 'admin_takeover_approval'));
            }

            $msg = 'Berhasil Takeover! Saldo Rp ' . number_format($payAmount) . ' cair ke Clipper.';
            if($bounty->status == 'completed') $msg .= ' Campaign otomatis SELESAI (Budget Habis).';

            return redirect()->route('admin.monitoring')->with('success', $msg);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 6. TAKEOVER REJECT (FORCE TOLAK)
    |--------------------------------------------------------------------------
    */
    public function takeoverReject(Request $request, $id)
    {
        // Gunakan Transaction & Lock untuk keamanan data
        return \DB::transaction(function () use ($request, $id) {
            
            // 1. Lock Data
            // Mencegah Publisher mengubah status saat Admin sedang memproses
            $submission = \App\Models\Submission::with(['bounty', 'user'])->lockForUpdate()->findOrFail($id);
            
            // 2. Cek Status (Harus Pending)
            if ($submission->status !== 'pending') {
                return back()->with('error', 'Gagal: Submission sudah diproses oleh Publisher atau sistem lain.');
            }

            // 3. Tentukan Alasan Penolakan
            // Jika di form ada input alasan, pakai itu. Jika tidak, pakai default Admin.
            $reason = $request->input('rejection_reason', 'Ditolak oleh Admin (Takeover): Konten tidak memenuhi syarat campaign.');

            // 4. Update Database
            $submission->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'notes'            => 'Force Rejected by Admin'
            ]);

            // 5. Kirim Notifikasi
            
            // Ke Clipper (Pemberitahuan Ditolak)
            if ($submission->user) {
                $submission->user->notify(new \App\Notifications\MissionActivityNotification($submission, 'submission_rejected_by_admin'));
            }

            if ($submission->bounty->publisher) {
                $submission->bounty->publisher->notify(new \App\Notifications\MissionActivityNotification($submission, 'admin_takeover_rejection'));
            }

            return redirect()->route('admin.monitoring')->with('success', 'Berhasil Takeover: Video telah ditolak.');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 7. VIEW TAKEOVER (TAMPILAN REVIEW KHUSUS ADMIN)
    |--------------------------------------------------------------------------
    | Menampilkan halaman review rasa publisher, tapi form-nya mengarah ke Admin.
    */
    public function takeoverView($id)
    {
        // 1. Ambil Submission Target
        $targetSubmission = \App\Models\Submission::with(['bounty', 'user', 'linkedAccount'])->findOrFail($id);
        $bounty = $targetSubmission->bounty;
        $clipper = $targetSubmission->user;

        // 2. Ambil Semua Submission milik User ini (Portfolio)
        // [UPDATE] Tambahkan with('user') biar loading cepat
        $allUserSubmissions = \App\Models\Submission::where('bounty_id', $bounty->id)
            ->where('clipper_id', $clipper->id)
            ->with('user', 'linkedAccount') // Penting untuk performa
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Susun Data Portfolio (Sesuai format JSON AlpineJS)
        // [UPDATE] Sesuaikan nama kolom dengan SubmissionController.php
        $portfolioData = [
            'total_views' => $allUserSubmissions->sum('views_count'),
            'total_earned' => $allUserSubmissions->where('status', 'approved')->sum('prize_amount') ?? 0,
            'submissions' => $allUserSubmissions->map(function ($item) {
                return [
                    'id' => $item->id,
                    'url' => $item->video_url, // Pastikan ini 'video_url' sesuai DB
                    'views' => $item->views_count,
                    'paid_views' => $item->last_paid_views ?? 0,
                    'prize' => $item->prize_amount,
                    'status' => $item->status,
                    'rejection_reason' => $item->rejection_reason,
                    'analytics' => $item->analytics_path ? asset('storage/' . $item->analytics_path) : null,
                    'caption' => $item->caption_content, // Pastikan 'caption_content'
                    'created_at' => $item->created_at->format('d M H:i'),
                    'user' => $item->user, 
                    'linked_account' => $item->linkedAccount,
                ];
            })->values(),
        ];

        // 4. Cari index video yang mau di-takeover agar otomatis terpilih
        $targetIndex = 0;
        foreach($portfolioData['submissions'] as $index => $sub) {
            if($sub['id'] == $targetSubmission->id) {
                $targetIndex = $index;
                break;
            }
        }

        return view('admin.bounties.takeover', compact('bounty', 'portfolioData', 'targetIndex'));
    }
}