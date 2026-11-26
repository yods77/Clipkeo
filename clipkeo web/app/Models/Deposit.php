<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_gateway',
        'external_ref',
        'amount',
        'unique_code', // Cukup ini saja
        'proof_image',
        'status',
        'rejection_reason',
    ];

    // --- ACCESSOR (FITUR PENTING) ---
    // Ini membuat kita bisa panggil $deposit->total_amount meskipun kolomnya tidak ada di DB
    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->unique_code;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reject($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $deposit = Deposit::findOrFail($id);
        
        if ($deposit->status !== 'pending') {
            return back()->with('error', 'Hanya deposit pending yang bisa ditolak.');
        }

        // Update status jadi failed/rejected
        $deposit->update(['status' => 'failed']);

        return back()->with('success', 'Deposit berhasil ditolak.');
    }
}