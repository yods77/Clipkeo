<?php

namespace App\Http\Controllers;

use App\Models\LinkedAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkedAccountController extends Controller
{
    // Menampilkan Halaman List Akun
    public function index()
    {
        // Ambil semua akun sosmed milik user yang login
        $accounts = Auth::user()->linkedAccounts;
        
        // Kelompokkan berdasarkan provider biar gampang ditampilkan di View
        $groupedAccounts = [
            'instagram' => $accounts->where('provider_name', 'instagram'),
            'tiktok'    => $accounts->where('provider_name', 'tiktok'),
            'youtube'   => $accounts->where('provider_name', 'youtube'),
        ];

        return view('linked-accounts.index', compact('groupedAccounts'));
    }

    // Menyimpan Akun Baru
    public function store(Request $request)
    {
        $request->validate([
            'provider_name' => 'required|in:instagram,tiktok,youtube',
            'username' => 'required|string|max:255',
        ]);

        // Cek apakah username sudah dipakai orang lain (Anti-Smurf)
        $exists = LinkedAccount::where('provider_name', $request->provider_name)
                    ->where('username', $request->username)
                    ->exists();

        if ($exists) {
            return back()->withErrors(['msg' => "Username {$request->username} sudah terdaftar di sistem kami."]);
        }

        // Simpan ke database
        Auth::user()->linkedAccounts()->create([
            'provider_name' => $request->provider_name,
            'username' => $request->username,
            // provider_id & verified_at dikosongkan dulu (tunggu verifikasi API nanti)
        ]);

        return back()->with('success', 'Akun berhasil ditambahkan! Silakan verifikasi.');
    }

    // Menghapus Akun
    public function destroy($id)
    {
        $account = Auth::user()->linkedAccounts()->findOrFail($id);
        $account->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}