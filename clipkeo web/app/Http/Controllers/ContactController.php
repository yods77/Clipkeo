<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\AdminActivityNotification;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
        ]);

        // 2. Simpan ke Database
        $message = ContactMessage::create($request->all());

        // 3. [BARU] Kirim Notifikasi ke Semua Admin
        // Cari user yang role-nya 'admin'
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            /** @var \App\Models\User $admin */
            $admin->notify(new AdminActivityNotification($message, 'contact_message'));
        }

        return back()->with('success', 'Pesan Anda berhasil dikirim! Tim kami akan segera menghubungi.');
    }
}