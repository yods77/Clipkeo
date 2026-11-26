<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $table = 'withdrawal_requests';

    protected $guarded = []; // Izinkan semua kolom diisi

    // Relasi ke User (Siapa yang minta duit)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Metode Pembayaran (Bank/E-wallet)
    public function payoutMethod()
    {
        return $this->belongsTo(PayoutMethod::class, 'payout_method_id');
    }

    public function transaction()
    {
        // Kita cari transaksi yang reference_id-nya mengandung ID WD ini
        return $this->hasOne(Transaction::class, 'user_id', 'user_id')
            ->where('type', 'withdrawal')
            ->latest();
    }
}