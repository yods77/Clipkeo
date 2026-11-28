<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\Bounty;
use App\Models\Deposit;
use App\Models\WithdrawalRequest;

class NotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | BUKA NOTIFIKASI & REDIRECT (PENGARAH JALAN)
    |--------------------------------------------------------------------------
    | Fungsi ini menangani logika ketika user mengklik salah satu notifikasi.
    | Alurnya:
    | 1. Cari notifikasi berdasarkan ID.
    | 2. Tandai sebagai 'Sudah Dibaca' (Mark as Read).
    | 3. Cek Tipe Notifikasi (Switch Case).
    | 4. Redirect ke halaman detail yang sesuai.
    */
    public function open($id)
    {
        // 1. Ambil Notifikasi milik User yang sedang login
        $notification = auth()->user()->notifications()->findOrFail($id);

        // 2. Tandai Sudah Dibaca
        $notification->markAsRead();

        // 3. Ambil Data Penting dari dalam JSON Notifikasi
        $type = $notification->data['type'] ?? null;       // Contoh: 'submission_in_review'
        $dataId = $notification->data['data_id'] ?? null;  // ID dari Submission/Bounty/Transaksi

        // 4. Logika Redirect Berdasarkan Tipe (Switch Case)
        switch ($type) {
            
            // --- KASUS A: PUBLISHER (Ada Pelamar Baru) ---
            // Arahkan ke halaman List Submission di dalam Bounty tersebut
            case 'submission_in_review':
                $submission = Submission::find($dataId);
                if ($submission) {
                    return redirect()->route('bounties.submissions', $submission->bounty_id)
                        ->with('highlight_submission', $submission->id); // (Opsional) Bisa dipakai buat highlight baris
                }
                break;

            // --- KASUS B: CLIPPER (Status Submission Update) ---
            // Arahkan ke halaman "Riwayat Submission Saya"
            case 'submission_approved':
            case 'submission_rejected':
            case 'submission_created':
                return redirect()->route('submissions.my');
                break;

            // --- KASUS C: PUBLISHER (Bounty Tayang) ---
            // Arahkan ke halaman detail Bounty publik
            case 'bounty_pending_review': // Sedang Ditinjau
            case 'campaign_approved':     // Disetujui
            case 'campaign_rejected':     // Ditolak
            case 'bounty_created':        // Dibuat (Legacy)
                return redirect()->route('dashboard');
                break;

            // --- KASUS D: KEUANGAN (Deposit) ---
            // Arahkan ke halaman detail Deposit
            case 'deposit_success':
                return redirect()->route('wallet.index', [
                    'search' => $notification->data['reference_id'] ?? '',
                    'type' => 'deposit',
                ]);

            case 'deposit':
                return redirect()->route('wallet.index');
                break;
            
            // --- KASUS E: KEUANGAN (Withdrawal) ---
            // Arahkan ke halaman Dompet utama
            case 'withdrawal':
                return redirect()->route('wallet.index');
                break;
        }

        // Default: Jika tipe tidak dikenali atau link spesifik gagal, kembali ke Dashboard
        return redirect()->route('dashboard');
    }

    /*
    |--------------------------------------------------------------------------
    | TANDAI SEMUA DIBACA (READ ALL)
    |--------------------------------------------------------------------------
    | Fungsi sederhana untuk menandai semua notifikasi user sebagai 'read'
    | tanpa membuka satu per satu.
    */
    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}