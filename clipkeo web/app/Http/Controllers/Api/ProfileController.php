<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\LinkedAccount; // Model Sosmed
use App\Models\PayoutMethod;  // Model Bank
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    // 1. GET PROFILE DATA (Multi-Table Fetch)
    public function index(Request $request)
    {
        $user = $request->user();

        // A. AMBIL DATA SOSMED (Dari Tabel LinkedAccount)
        // Kita ambil username-nya saja
        $tiktok = LinkedAccount::where('user_id', $user->id)->where('provider_name', 'tiktok')->value('username');
        $instagram = LinkedAccount::where('user_id', $user->id)->where('provider_name', 'instagram')->value('username');
        $youtube = LinkedAccount::where('user_id', $user->id)->where('provider_name', 'youtube')->value('username');

        // B. AMBIL DATA BANK (Dari Tabel PayoutMethod)
        $payout = PayoutMethod::where('user_id', $user->id)->first();

        // C. FIX AVATAR URL (Agar jadi http://...)
        $avatar = $user->avatar_url;
        if ($avatar && !str_starts_with($avatar, 'http')) {
            $avatar = url('storage/' . $avatar);
        }

        // 🔥 D. RETURN JSON LENGKAP (INI BAGIAN PENTINGNYA) 🔥
        return response()->json([
            'status' => 'success',
            'data' => [
                // Data Utama User
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'wallet_balance' => $user->wallet_balance,
                'avatar_url' => $avatar,

                // Data Sosmed (Kita selipkan di sini)
                'social_tiktok' => $tiktok,      // <-- Flutter nyari kunci ini
                'social_instagram' => $instagram, // <-- Flutter nyari kunci ini
                'social_youtube' => $youtube,    // <-- Flutter nyari kunci ini

                // Data Bank
                'bank_name' => $payout ? $payout->bank_name : null,
                'account_number' => $payout ? $payout->account_number : null,
                'account_name' => $payout ? $payout->account_name : null,
            ]
        ]);
    }

    // 2. UPDATE PROFILE (Multi-Table Update)
    public function update(Request $request)
    {
        $user = $request->user();

        // VALIDASI INPUT
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'avatar_url' => ['nullable', 'image', 'max:2048'], // Validasi Gambar
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // A. UPDATE INFO DASAR
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->phone_number = $request->phone_number;

        // B. HANDLE AVATAR UPLOAD (Multipart/Form-Data)
        if ($request->hasFile('avatar_url')) {
            // 1. (Opsional) Hapus avatar lama jika masih ada di storage lokal
            // Ini bagus untuk bersih-bersih masa transisi
            if ($user->avatar_url && !str_contains($user->avatar_url, 'http')) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // 2. Simpan avatar baru ke Cloudinary
            // Kita tambahkan auto-crop wajah agar foto profil selalu rapi
            $uploadedFileUrl = $request->file('avatar_url')->storeOnCloudinary('avatars', [
                'transformation' => [
                    'width' => 400,
                    'height' => 400,
                    'crop' => 'fill',
                    'gravity' => 'face'
                ]
            ])->getSecurePath();

            // 3. Simpan URL lengkap Cloudinary ke database
            $user->avatar_url = $uploadedFileUrl;
        }

        $user->save();

        // C. UPDATE SOSMED (Tabel LinkedAccounts)
        $socialMap = [
            'social_tiktok' => 'tiktok',
            'social_instagram' => 'instagram',
            'social_youtube' => 'youtube',
        ];

        foreach ($socialMap as $inputName => $providerName) {
            if ($request->filled($inputName)) {
                LinkedAccount::updateOrCreate(
                    ['user_id' => $user->id, 'provider_name' => $providerName],
                    [
                        'provider_id' => $request->input($inputName), // Biasanya username dijadikan ID juga
                        'username' => $request->input($inputName),
                        'verified_at' => now(),
                    ]
                );
            }
        }

        // D. UPDATE BANK (Tabel PayoutMethods)
        if ($request->filled('bank_name') && $request->filled('account_number')) {
            PayoutMethod::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                    'is_primary' => 1
                ]
            );
        }

        return response()->json([
            'status' => true,
            'message' => 'Profil berhasil diperbarui!',
            // Kirim balik URL avatar terbaru biar UI langsung update
            'new_avatar_url' => $user->avatar_url ? url('storage/' . $user->avatar_url) : null
        ]);
    }

    // 🔥 FUNGSI BARU: VALIDASI PIN (PANGGIL INI DARI ROUTE) 🔥
    public function validatePin(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'pin' => 'required|digits:6', // Wajib angka 6 digit
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format PIN harus 6 digit angka.'
            ], 422);
        }

        $user = $request->user();

        // 2. Cek apakah User sudah punya PIN?
        if (!$user->security_pin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda belum mengatur PIN Transaksi.'
            ], 400);
        }

        // 3. Cek Kecocokan PIN (Database ter-hash vs Input User)
        if (Hash::check($request->pin, $user->security_pin)) {
            return response()->json([
                'status' => 'success',
                'message' => 'PIN Benar'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'PIN Transaksi salah!'
            ], 401);
        }
    }
    public function updateAvatar(Request $request)
    {
        try {
            // 1. Cek apakah file ada
            if (!$request->hasFile('avatar')) {
                return response()->json(['message' => 'File tidak terdeteksi oleh server'], 400);
            }

            $user = $request->user();

            // 2. Proses upload ke Cloudinary
            // Kita gunakan upload() karena ini adalah file gambar fisik dari HP
            $upload = Cloudinary::upload($request->file('avatar')->getRealPath(), [
                'folder' => 'avatars',
                'transformation' => [
                    'width' => 300, 
                    'height' => 300, 
                    'crop' => 'fill', // Potong otomatis jadi kotak presisi
                    'gravity' => 'face' // Otomatis fokus ke wajah jika terdeteksi
                ]
            ]);

            // 3. Ambil URL aman (https)
            $cloudinaryPath = $upload->getSecurePath();

            // 4. Update kolom database
            // Sesuai catatan kamu, kolomnya adalah avatar_url
            $user->avatar_url = $cloudinaryPath;

            if ($user->save()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Foto profil berhasil diperbarui di Cloudinary!',
                    'avatar_url' => $cloudinaryPath 
                ]);
            } else {
                return response()->json(['message' => 'Gagal update database'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error Update Avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Server Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}