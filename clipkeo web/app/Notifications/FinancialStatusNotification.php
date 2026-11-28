<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use App\Helpers\FcmHelper;

// [PENTING] Hapus 'implements ShouldQueue' agar notifikasi langsung tampil tanpa delay
class FinancialStatusNotification extends Notification
{
    use Queueable;

    public $transaction;
    public $type;
    public $status;
    public $reason;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */
    public function __construct($transaction, $type, $status, $reason = null)
    {
        $this->transaction = $transaction;
        $this->type = $type;
        $this->status = $status;
        $this->reason = $reason;
    }

    /*
    |--------------------------------------------------------------------------
    | CHANNELS
    |--------------------------------------------------------------------------
    | Mengirim ke Database (Lonceng) DAN Mail (Email).
    */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT EMAIL (MENGGUNAKAN VIEW NOTA)
    |--------------------------------------------------------------------------
    | Bagian ini yang kita update agar menampilkan desain invoice HTML
    */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)->subject($this->getSubject());

        // Deposit menggunakan model Transaction, bukan WithdrawalRequest.
        // Pisahkan view agar data dan format tanggal sesuai dengan modelnya.
        if ($this->type === 'deposit') {
            return $mail->view('emails.deposit', [
                'transaction' => $this->transaction,
                'status' => $this->status,
                'reason' => $this->reason,
            ]);
        }

        // Withdrawal tetap menggunakan template invoice yang sudah ada.
        return $mail->view('emails.invoice', [
            'withdrawal' => $this->transaction,
            'type' => $this->type,
            'status' => $this->status,
            'reason' => $this->reason,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT DATABASE (LONCENG DASHBOARD)
    |--------------------------------------------------------------------------
    | Bagian ini TIDAK DIUBAH (sesuai request).
    */
    public function toArray($notifiable)
    {
        $title = '';
        $message = '';
        $icon = '';
        $color = '';

        // Ambil nominal (Safe Access)
        $amountRaw = $this->transaction->amount_final ?? $this->transaction->amount ?? 0;
        $amount = 'Rp ' . number_format($amountRaw, 0, ',', '.');

        // LOGIKA DEPOSIT
        if ($this->type === 'deposit') {
            if ($this->status === 'pending') {
                $title = 'Deposit Sedang Diproses';
                $message = "Menunggu pembayaran deposit $amount.";
                $icon = 'clock';
                $color = 'blue';
            } elseif ($this->status === 'success') {
                $title = 'Deposit Berhasil';
                $message = "Saldo sebesar $amount masuk ke dompet.";
                $icon = 'check-circle';
                $color = 'green';
            } elseif ($this->status === 'rejected') {
                $title = 'Deposit Ditolak';
                $message = "Deposit $amount ditolak. Alasan: " . ($this->reason ?? '-');
                $icon = 'x-circle';
                $color = 'red';
            }
        }
        // LOGIKA WITHDRAWAL
        elseif ($this->type === 'withdrawal') {
            if ($this->status === 'pending') {
                $title = 'Penarikan Dana Sedang Diproses';
                $message = "Withdrawal sebesar $amount sedang diproses.";
                $icon = 'clock';
                $color = 'amber';
            } elseif ($this->status === 'success') {
                $title = 'Penarikan Dana Berhasil';
                $message = "Dana sebesar $amount berhasil dikirim.";
                $icon = 'check-circle';
                $color = 'green';
            } elseif ($this->status === 'rejected') {
                $title = 'Penarikan Dana Ditolak';
                $message = "Withdrawal ditolak. Alasan: " . ($this->reason ?? '-');
                $icon = 'x-circle';
                $color = 'red';
            }
        }
        if ($title && $message) {
            \App\Helpers\FcmHelper::sendToUser(
                $notifiable,
                $title,
                $message,
                [
                    'type' => $this->type, // 'deposit' atau 'withdrawal'
                    'status' => $this->status,
                    'refresh_ui' => true,
                ]
            );
        }
        return [
            'type' => $this->type,
            'status' => $this->status,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'amount' => $amountRaw,
            'url' => route('wallet.index'),
            'timestamp' => now()
        ];
    }

    // Helper Subject Email
    private function getSubject()
    {
        if ($this->type == 'deposit' && $this->status == 'success')
            return 'Deposit Berhasil';
        if ($this->type == 'withdrawal' && $this->status == 'success')
            return 'Penarikan Berhasil';
        if ($this->status == 'rejected')
            return 'Transaksi Ditolak';
        return 'Info Transaksi';
    }
}