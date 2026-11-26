<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | TAMPILKAN NOTA TRANSAKSI (INVOICE)
    |--------------------------------------------------------------------------
    | Menampilkan detail transaksi dalam format Invoice yang siap cetak.
    | Validasi: Hanya User pemilik transaksi & Admin yang bisa melihat.
    */
    public function show($reference_id)
    {
        // 1. Cari Transaksi berdasarkan Reference ID (contoh: DEP-xxxx atau WD-xxxx)
        $transaction = Transaction::with('user')->where('reference_id', $reference_id)->firstOrFail();

        // 2. Keamanan: Cek Hak Akses
        $currentUser = Auth::user();
        
        // Jika bukan pemilik DAN bukan admin, tolak akses (403 Forbidden)
        if ($currentUser->id !== $transaction->user_id && $currentUser->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk melihat nota ini.');
        }

        // 3. Tampilkan View Invoice
        return view('invoice.show', compact('transaction'));
    }
}