<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Buat Username Otomatis (Contoh: "Yoga" -> "yoga832")
        $baseUsername = strtolower(explode(' ', $request->name)[0]);
        $username = $baseUsername . rand(100, 999);

        // 3. Simpan User Baru (Lengkap dengan default value)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'username' => $username,          // Wajib diisi
            'phone_number' => $request->phone_number,
            'role' => 'clipper',              // Default role
            'wallet_balance' => 0,
            'reputation_score' => 1,
        ]);

        // 4. Buat Token (Login otomatis setelah daftar)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
    // LOGIN (Versi Bypass Auth::attempt)
    public function login(Request $request)
    {
        // 1. CATAT LOG (Ini CCTV-nya)
        \Illuminate\Support\Facades\Log::info('Login Request Masuk:', $request->all());

        // 2. Cek User
        $user = User::where('email', $request->email)->first();

        // 3. Cek apakah user ketemu?
        if (!$user) {
            \Illuminate\Support\Facades\Log::error('Login Gagal: User tidak ditemukan untuk email ' . $request->email);
            return response()->json(['message' => 'User tidak ditemukan'], 401);
        }

        // 4. Cek Password
        if (!Hash::check($request->password, $user->password)) {
            \Illuminate\Support\Facades\Log::error('Login Gagal: Password salah untuk ' . $request->email);
            return response()->json(['message' => 'Password Salah'], 401);
        }

        // 5. Jika lolos semua
        $token = $user->createToken('auth_token')->plainTextToken;
        \Illuminate\Support\Facades\Log::info('Login Sukses: Token dibuat untuk ' . $request->email);

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function googleLogin(Request $request)
{
    // 🕵️ DETEKTIF: Catat data awal yang dikirim dari Flutter
    Log::info('--- [DETEKTIF] Google Login Start ---');
    Log::info('Payload dari Flutter:', $request->all());

    // 1. Validasi Input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'name' => 'required|string',
        'google_id' => 'required',
        'avatar' => 'nullable'
    ]);

    if ($validator->fails()) {
        Log::warning('Validasi Gagal:', $validator->errors()->toArray());
        return response()->json($validator->errors(), 422);
    }

    try {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::info('User baru terdeteksi, mencoba mendaftarkan...');

            $cloudinaryUrl = null;
            
            // 🕵️ DETEKTIF: Cek Avatar
            if ($request->avatar) {
                // Cek apakah ini avatar placeholder Google (/0)
                if (str_contains($request->avatar, '/0')) {
                    Log::info('Avatar adalah placeholder (/0), melewati upload Cloudinary.');
                } else {
                    Log::info('Mencoba upload avatar ke Cloudinary: ' . $request->avatar);
                    try {
                        $upload = Cloudinary::upload($request->avatar, [
                            'folder' => 'avatars',
                            'transformation' => [
                                'width' => 200, 'height' => 200, 'crop' => 'fill'
                            ]
                        ]);
                        $cloudinaryUrl = $upload->getSecurePath();
                        Log::info('Cloudinary Berhasil! URL: ' . $cloudinaryUrl);
                    } catch (\Exception $e) {
                        Log::error('Cloudinary Ngambek: ' . $e->getMessage());
                    }
                }
            }

            // Generate Username
            $username = strtolower(explode(' ', $request->name)[0]) . rand(100, 999);
            Log::info('Generated Username: ' . $username);

            // 🕵️ DETEKTIF: Simpan ke DB
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                'username' => $username,
                'google_id' => $request->google_id,
                'avatar_url' => $cloudinaryUrl, // 🔥 Sesuaikan nama kolom DB kamu (avatar_url)
                'role' => 'clipper',
            ]);

            Log::info('User Berhasil Dibuat. ID: ' . $user->id);
        } else {
            Log::info('User lama ditemukan. ID: ' . $user->id);
        }

        // 🕵️ DETEKTIF: Tokenizing
        $token = $user->createToken('auth_token')->plainTextToken;
        Log::info('Token Berhasil Dibuat.');
        Log::info('--- [DETEKTIF] Google Login Selesai (SUCCESS) ---');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);

    } catch (\Exception $e) {
        // 🕵️ DETEKTIF: Tangkap Error yang bikin server crash
        Log::error('--- [DETEKTIF] CRASH DI GOOGLE LOGIN ---');
        Log::error('Pesan Error: ' . $e->getMessage());
        Log::error('File: ' . $e->getFile() . ' baris ' . $e->getLine());
        
        return response()->json([
            'status' => 'error',
            'message' => 'Internal Server Error: ' . $e->getMessage()
        ], 500);
    }
}

    public function updatePhoneNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|unique:users,phone_number',
        ]);

        $user = auth()->user(); // Mengambil user dari token yang sedang login

        if ($user) {
            $user->update([
                'phone_number' => $request->phone_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nomor HP berhasil diperbarui!',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 401);
    }

    public function updateFcmToken(\Illuminate\Http\Request $request)
    {
        // 1. Validasi
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // 2. Ambil User Login
        $user = auth()->user();

        // 3. Simpan Token
        if ($user) {
            $user->update([
                'fcm_token' => $request->fcm_token
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM Token berhasil diupdate!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User tidak ditemukan',
        ], 401);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        // 1. Ambil user yang sedang request
        $user = $request->user();

        if ($user) {
            // 🔥 PENTING: Hapus FCM Token biar notif gak nyasar
            $user->update([
                'fcm_token' => null
            ]);

            // 2. Hapus Token Login (Sanctum)
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil & Token FCM dibersihkan'
        ]);
    }
}