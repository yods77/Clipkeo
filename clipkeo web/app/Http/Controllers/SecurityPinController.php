<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecurityPinController extends Controller
{
    // Tampilkan Form
    public function show()
    {
        return view('auth.setup-pin');
    }

    // Simpan PIN
    public function store(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'numeric', 'digits:6', 'confirmed'],
        ]);

        $request->user()->update([
            'security_pin' => Hash::make($request->pin),
        ]);

        // Redirect ke Dashboard dengan pesan sukses
        return redirect()->route('dashboard')->with('success', 'PIN Keamanan berhasil dibuat!');
    }
}