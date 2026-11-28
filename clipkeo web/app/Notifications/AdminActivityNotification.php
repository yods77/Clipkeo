<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AdminActivityNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $type;

    /**
     * Create a new notification instance.
     *
     * @param $data Objek data (Deposit, Withdrawal, atau Pesan Contact)
     * @param $type Jenis notifikasi: 'deposit_request', 'withdraw_request', 'contact_message'
     */
    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database']; // Simpan ke database agar muncul di lonceng dashboard
    }

    public function toArray($notifiable)
    {
        $title = '';
        $message = '';
        $icon = '';
        $color = ''; // green, red, blue, amber, purple

        // LOGIKA PESAN UNTUK ADMIN
        switch ($this->type) {
            case 'deposit_success':
                // Deposit Publisher yang telah dikonfirmasi berhasil oleh Xendit.
                $publisherName = $this->data->user?->name ?? 'Publisher';
                $depositAmount = number_format((float) ($this->data->amount ?? 0), 0, ',', '.');
                $referenceId = $this->data->reference_id ?? '-';
                $title = 'Deposit Publisher Berhasil';
                $message = $publisherName . ' berhasil deposit Rp ' . $depositAmount . ' (Ref: ' . $referenceId . ')';
                $icon = 'money';
                $color = 'green';
                break;

            case 'deposit_request':
                // Publisher mengajukan deposit
                $title = 'Deposit Masuk';
                $message = $this->data->user->name . ' ingin deposit Rp ' . number_format($this->data->amount);
                $icon = 'money'; // Kita pakai kode string untuk icon
                $color = 'green';
                break;

            case 'withdraw_request':
                // Clipper mengajukan penarikan
                $title = 'Permintaan Withdraw';
                $message = $this->data->user->name . ' minta tarik dana Rp ' . number_format($this->data->amount_requested);
                $icon = 'wallet';
                $color = 'amber';
                break;

            case 'contact_message':
                // Pesan dari Landing Page
                $title = 'Pesan Baru';
                $message = 'Dari: ' . $this->data->name . ' - ' . \Illuminate\Support\Str::limit($this->data->message, 30);
                $icon = 'mail';
                $color = 'blue';
                break;

            case 'bounty_pending_review':
                $title = 'Campaign Butuh Moderasi';
                $message = $this->data->publisher->name . ' mengajukan campaign baru: "' . Str::limit($this->data->title, 20) . '".';
                $icon = 'shield'; // Menggunakan ikon tameng
                $color = 'red';   // Warna merah (penting)
                break;
        }

        return [
            'type' => $this->type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'data_id' => $this->data->id ?? null, // ID Transaksi/Pesan
            'reference_id' => $this->data->reference_id ?? null,
            'amount' => $this->data->amount ?? $this->data->amount_requested ?? null,
            'url' => $this->type === 'deposit_success' && !empty($this->data->reference_id)
                ? route('wallet.index', ['search' => $this->data->reference_id, 'type' => 'deposit'])
                : null,
            'timestamp' => now()
        ];
    }
}