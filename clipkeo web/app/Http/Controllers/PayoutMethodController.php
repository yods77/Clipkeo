<?php

namespace App\Http\Controllers;

use App\Models\PayoutMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutMethodController extends Controller
{
    // 1. Tampilkan Halaman List & Form
    public function index()
    {
        $methods = Auth::user()->payoutMethods; // Mengambil data relasi
        return view('payout_methods.index', compact('methods'));
    }

    // 2. Simpan Rekening Baru
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:50',
            'account_number' => 'required|numeric',
            'account_name' => 'required|string|max:100',
        ]);

        // Simpan via relasi user agar user_id otomatis terisi
        Auth::user()->payoutMethods()->create([
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'is_primary' => false, // Default false dulu
        ]);

        return back()->with('success', 'Metode pembayaran berhasil ditambahkan!');
    }

    // 3. Hapus Rekening
    public function destroy($id)
    {
        // Pastikan yang dihapus adalah milik user yang sedang login (Keamanan)
        $method = PayoutMethod::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $method->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }
}