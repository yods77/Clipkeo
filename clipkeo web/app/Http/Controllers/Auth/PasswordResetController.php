<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ResetPasswordMail;

class PasswordResetController extends Controller
{
    // STEP 1: Kirim OTP ke Email
    public function sendOtp(Request $request)
    {
        
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Email tidak terdaftar'], 404);
        }

        // Generate 6 digit angka
        $otp = rand(100000, 999999);
        Log::info("OTP untuk {$request->email} adalah: $otp");

        // Simpan ke tabel password_reset_tokens (hapus data lama dulu biar gak numpuk)
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($otp), // Kita hash biar aman
            'created_at' => now()
        ]);

        try {
            Mail::to($request->email)->send(new ResetPasswordMail($otp));
            
            return response()->json([
                'success' => true,
                'message' => 'Kode OTP berhasil dikirim ke email kamu.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email, coba lagi nanti.'
            ], 500);
        }

        // Kirim email (Pastikan .env SMTP sudah diisi)
        // Mail::to($request->email)->send(new \App\Mail\OtpMail($otp));
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim (Cek Log/Email)',
            'otp_debug' => $otp
        ]);
    }

    // STEP 2: Validasi OTP & Ganti Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        $resetData = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$resetData || !Hash::check($request->otp, $resetData->token)) {
            return response()->json(['success' => false, 'message' => 'Kode OTP salah atau kedaluwarsa'], 400);
        }

        // Update Password User
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Hapus token setelah dipakai
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['success' => true, 'message' => 'Password berhasil diperbarui']);
    }
}