<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPinMail; // 🔥 Import Mailable yang baru dibuat
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PinResetController extends Controller
{
    // STEP 1: Kirim OTP ke Email untuk Lupa PIN
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Email tidak terdaftar'], 404);
        }

        // Generate 6 digit angka OTP
        $otp = rand(100000, 999999);
        Log::info("OTP Lupa PIN untuk {$request->email} adalah: $otp");

        // Simpan ke tabel pin_reset_tokens (hapus data lama dulu biar gak numpuk)
        DB::table('pin_reset_tokens')->where('email', $request->email)->delete();
        DB::table('pin_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($otp), // Kita hash biar hacker gak bisa baca
            'created_at' => now()
        ]);

        try {
            // 2. 🔥 KIRIM EMAIL ASLI PAKAI GMAIL SMTP KAMU
            Mail::to($request->email)->send(new ResetPinMail($otp));

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email kamu.'
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal kirim email OTP: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. Coba lagi nanti.'
            ], 500);
        }

        // Todo: Kirim email beneran pakai Mail::to() kalau SMTP sudah siap

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim',
            'otp_debug' => $otp // Membantu banget buat testing di Flutter!
        ]);
    }

    // STEP 1.5: Validasi OTP secara terpisah (Sebelum Buat PIN Baru)
    public function verifyOtp(Request $request)
    {
        $request->validate(['email' => 'required|email', 'otp' => 'required']);

        $resetData = DB::table('pin_reset_tokens')->where('email', $request->email)->first();

        // 1. Cek Token Ada/Tidak
        if (!$resetData) {
            return response()->json(['success' => false, 'message' => 'OTP tidak ditemukan. Minta ulang.'], 400);
        }

        // 2. Cek Kedaluwarsa
        $waktuBatas = \Carbon\Carbon::parse($resetData->created_at)->addMinutes(1);
        if (now()->isAfter($waktuBatas)) {
            DB::table('pin_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['success' => false, 'message' => 'OTP sudah kedaluwarsa. Silahkan Minta Ulang OTP!'], 400);
        }

        // 3. Cek Kecocokan OTP
        if (!\Illuminate\Support\Facades\Hash::check($request->otp, $resetData->token)) {
            return response()->json(['success' => false, 'message' => 'Kode OTP salah.'], 400);
        }

        return response()->json(['success' => true, 'message' => 'OTP Valid!']);
    }

    // STEP 2: Validasi OTP & Ganti PIN
    public function resetPin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'pin' => 'required|digits:6|confirmed'
        ]);

        $resetData = DB::table('pin_reset_tokens')->where('email', $request->email)->first();

        // 1. Cek apakah data token ada?
        if (!$resetData) {
            return response()->json(['success' => false, 'message' => 'Kode OTP tidak ditemukan. Silakan minta ulang.'], 400);
        }

        // 2. 🔥 CEK KEDALUWARSA (BATAS WAKTU 15 MENIT) 🔥
        $waktuBatas = \Carbon\Carbon::parse($resetData->created_at)->addMinutes(1);
        if (now()->isAfter($waktuBatas)) {
            DB::table('pin_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['success' => false, 'message' => 'OTP sudah kedaluwarsa. Silahkan Minta Ulang OTP!'], 400);
        }

        // 3. Cek kecocokan angka OTP
        if (!Hash::check($request->otp, $resetData->token)) {
            return response()->json(['success' => false, 'message' => 'Kode OTP salah.'], 400);
        }

        // Update PIN User
        User::where('email', $request->email)->update([
            'security_pin' => Hash::make($request->pin)
        ]);

        // Hapus token setelah berhasil dipakai
        DB::table('pin_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'PIN berhasil diubah! Silakan gunakan PIN baru Anda.'
        ]);
    }
}