<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use App\Helpers\FcmHelper;

class MissionActivityNotification extends Notification
{
    use Queueable;

    protected $data; // Menampung object Submission atau Bounty
    protected $type; // Menampung jenis notifikasi (string)

    /**
     * Constructor: Menerima data dan tipe notifikasi.
     */
    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Menentukan channel pengiriman (Database only).
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Menyusun data array untuk disimpan ke database.
     */
    public function toArray($notifiable)
    {
        // 1. Inisialisasi Default Variable (Agar tidak error jika case tidak ketemu)
        $title = '';
        $message = '';
        $icon = 'info';
        $color = 'text-gray-500';
        $link = route('submissions.my'); // Default link ke list submission clipper

        // 2. Tentukan Isi Pesan Berdasarkan Tipe Notifikasi
        switch ($this->type) {

            // ---------------------------------------------------------------
            // [GROUP 1] NOTIFIKASI UNTUK PUBLISHER
            // ---------------------------------------------------------------

            // Case: Campaign baru dibuat dan masuk antrian review admin
            case 'bounty_pending_review':
                $title = 'Campaign Sedang Ditinjau!';
                $message = "Campaign \"" . Str::limit($this->data->title, 20) . "\" berhasil dibuat dan sedang menunggu persetujuan.";
                $icon = 'clock';
                $color = 'text-amber-500';
                $link = route('dashboard');
                break;

            // Case: Campaign disetujui Admin dan sudah Tayang (Active)
            case 'campaign_approved':
                $title = 'Campaign Disetujui!';
                $message = "Hore! Campaign \"" . Str::limit($this->data->title, 20) . "\" sudah tayang dan bisa dilihat Clipper.";
                $icon = 'check-circle';
                $color = 'text-green-500';
                $link = route('dashboard');
                break;

            // Case: Campaign ditolak Admin (Refund Saldo)
            case 'campaign_rejected':
                $title = 'Campaign Ditolak!';
                $message = "Maaf, campaign \"" . Str::limit($this->data->title, 20) . "\" ditolak. Saldo telah dikembalikan.";
                $icon = 'x-circle';
                $color = 'text-red-500';
                $link = route('dashboard');
                break;

            // Case: Campaign berhasil dibuat (Langsung aktif / history)
            case 'bounty_created':
                $title = '📢 Sayembara Tayang!';
                $message = "Misi \"" . Str::limit($this->data->title, 20) . "\" berhasil dibuat dan sudah aktif.";
                $icon = 'video';
                $color = 'text-purple-500';
                $link = route('dashboard');
                break;

            // Case: Ada Clipper yang menyetor link video baru
            case 'submission_in_review':
                $title = 'Submission Baru!';
                $message = $this->data->user->name . " baru saja menyetor link video untuk campaign \"" . Str::limit($this->data->bounty->title, 20) . "\".";
                $icon = 'user';
                $color = 'text-blue-500';
                // Link menuju halaman review submission spesifik
                $link = route('bounties.submissions', $this->data->bounty_id);
                break;

            // ---------------------------------------------------------------
            // [GROUP 2] NOTIFIKASI UNTUK CLIPPER
            // ---------------------------------------------------------------

            // Case: Notifikasi Global ada Misi Baru Rilis
            case 'new_bounty_available':
                $title = 'Misi Baru Tersedia!';
                $message = "Clipkeo baru saja merilis misi: \"" . Str::limit($this->data->title, 20) . "\". Yuk ramaikan!";
                $icon = 'fire';
                $color = 'text-orange-500';
                $link = route('bounties.show', $this->data->id);
                break;

            // Case: Clipper berhasil setor link (Menunggu Review)
            case 'submission_created':
                $title = 'Submission Terkirim';
                $message = "Link video untuk misi \"" . Str::limit($this->data->bounty->title, 20) . "\" sedang direview.";
                $icon = 'clock';
                $color = 'text-amber-500';
                break;

            // Case: Setoran disetujui oleh PUBLISHER (Reward Cair)
            case 'submission_approved':
                $title = 'Submission Disetujui';
                $message = "Selamat! Submission untuk misi \"" . Str::limit($this->data->bounty->title, 20) . "\" telah disetujui. Reward dikirim ke Saldo Utama Anda.";
                $icon = 'currency-dollar'; // Icon Dollar/Uang
                $color = 'text-emerald-500';
                break;

            // [BARU - FIX] Case: Setoran disetujui oleh ADMIN/TAKEOVER (Reward Cair)
            case 'submission_approved_by_admin':
                $title = 'Submission Disetujui';
                $message = "Selamat! Submission untuk misi \"" . Str::limit($this->data->bounty->title, 20) . "\" telah disetujui. Reward dikirim ke Saldo Utama Anda.";
                $icon = 'shield-check'; // Icon Tameng Admin
                $color = 'text-indigo-500';
                break;

            case 'submission_rejected_by_admin':
                $title = 'Submission Ditolak';
                // Ambil alasan dari database
                $reason = $this->data->rejection_reason ?? 'Tidak memenuhi syarat komunitas.';

                $message = "Selamat, Submissionmu untuk misi \"" . Str::limit($this->data->bounty->title, 20) . "\" ditolak. Alasan: \"$reason\"";
                $icon = 'shield-exclamation'; // Ikon Tameng Tanda Seru
                $color = 'text-rose-600'; // Warna Merah Gelap
                break;

            // Case: Setoran ditolak (Baik oleh Publisher atau Admin)
            case 'submission_rejected':
                $reason = strtolower($this->data->rejection_reason ?? '');

                // Cek Keyword Auto Reject (Budget Habis)
                $isBudgetIssue = Str::contains($reason, ['budget', 'habis', 'sold out', 'auto-reject']);

                if ($isBudgetIssue) {
                    // Sub-Case: Ditolak karena Budget Habis
                    $title = 'Campaign Berakhir';
                    $message = 'Maaf, kuota budget campaign "' . Str::limit($this->data->bounty->title, 20) . '" telah habis.';
                    $icon = 'lock-closed';
                    $color = 'text-red-500';
                } else {
                    // Sub-Case: Ditolak karena Alasan Lain (Kualitas, Link Salah, dll)
                    $title = 'Submission Ditolak';
                    $message = "Maaf, Submissionmu untuk misi \"" . Str::limit($this->data->bounty->title, 20) . "\" ditolak. Alasan: \"$reason\"";
                    $icon = 'x-circle';
                    $color = 'text-rose-500';
                }
                break;

            case 'admin_takeover_approval':
                $title = 'Submission Disetujui oleh Admin';
                $message = 'Admin telah menyetujui submission untuk misi "' . Str::limit($this->data->bounty->title, 20) . '". Reward telah dibayarkan.';
                $icon = 'shield-check'; // Icon tameng (menandakan admin)
                $color = 'text-indigo-600';
                // Arahkan Publisher ke halaman Submission list miliknya
                $link = route('bounties.submissions', $this->data->bounty_id);
                break;

            case 'admin_takeover_rejection':
                $title = 'Submission Ditolak oleh Admin';
                $reason = $this->data->rejection_reason ?? 'Tidak sesuai kriteria';
                $cleanReason = str_replace('Admin Takeover: ', '', $reason);
                $message = 'Admin telah menolak submission "' . Str::limit($this->data->bounty->title, 15) . '". Alasan: ' . Str::limit($cleanReason, 30);
                $icon = 'shield-exclamation';
                $color = 'text-rose-600';
                $link = route('bounties.submissions', $this->data->bounty_id);
                break;
        }
        if ($title && $message) {
            \App\Helpers\FcmHelper::sendToUser(
                $notifiable, // User tujuan
                $title,      // Judul yang sudah diracik di atas
                $message,    // Pesan yang sudah diracik di atas
                [
                    'type' => $this->type,
                    'data_id' => $this->data->id ?? null,
                    'refresh_ui' => true,
                ]
            );
        }
        // 3. Return Array Data
        return [
            'type' => $this->type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'link' => $link,
            'data_id' => $this->data->id ?? null,
            'timestamp' => now()
        ];
    }
}