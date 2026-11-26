<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\LinkedAccount;
use App\Models\PayoutMethod;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. EDIT PROFILE (FORM VIEW)
    |--------------------------------------------------------------------------
    | Menampilkan halaman edit profil.
    | Mengambil data user & sosial media untuk ditampilkan di form.
    */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // 1. AMBIL DATA SOSMED SECARA MANUAL
        // Kita ambil value 'username' satu per satu berdasarkan provider-nya
        $dataSosmed = [
            'tiktok' => \App\Models\LinkedAccount::where('user_id', $user->id)->where('provider_name', 'tiktok')->value('username'),
            'instagram' => \App\Models\LinkedAccount::where('user_id', $user->id)->where('provider_name', 'instagram')->value('username'),
            'youtube' => \App\Models\LinkedAccount::where('user_id', $user->id)->where('provider_name', 'youtube')->value('username'),
        ];

        // 2. KONVERSI KE OBJECT
        // Agar di Blade bisa dipanggil dengan panah: $getSosmed->instagram
        $getSosmed = (object) $dataSosmed;

        return view('profile.edit', [
            'user' => $user,
            'getSosmed' => $getSosmed,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. UPDATE PROFILE (PROCESS)
    |--------------------------------------------------------------------------
    | Menangani semua logika penyimpanan:
    | - Update Info Dasar (Nama, Email, HP)
    | - Upload Foto Profil (Avatar)
    | - Simpan Akun Sosmed (Linked Accounts)
    | - Simpan Rekening Bank (Payout Method)
    */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // A. UPDATE INFO DASAR (Name, Email, Username, Phone)
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(User::class)->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'birthday' => ['nullable', 'date'],
            'province_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
        ]);

        $user->fill($validatedData);

        // Jika email berubah, reset status verifikasi
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // B. HANDLE AVATAR UPLOAD
        if ($request->hasFile('avatar_url')) {
            $request->validate([
                'avatar_url' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Hapus avatar lama (kecuali url dari google/eksternal)
            if ($user->avatar_url && !str_contains($user->avatar_url, 'http')) {
                Storage::disk('public')->delete($user->avatar_url);
            }

            // Simpan gambar baru ke folder public/avatars
            $path = $request->file('avatar_url')->storeOnCloudinary('avatars', [
                'transformation' => [
                    'width' => 400,
                    'height' => 400,
                    'crop' => 'fill',
                    'gravity' => 'face'
                ]
            ])->getSecurePath();
            $user->avatar_url = $path;
        }

        $user->save();

        // C. HANDLE LINKED ACCOUNTS (Sosial Media)
        $socialMap = [
            'social_tiktok' => 'tiktok',
            'social_instagram' => 'instagram',
            'social_youtube' => 'youtube',
        ];

        foreach ($socialMap as $inputName => $providerName) {
            if ($request->filled($inputName)) {
                LinkedAccount::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'provider_name' => $providerName
                    ],
                    [
                        'provider_id' => $request->input($inputName),
                        'username' => $request->input($inputName),
                        'verified_at' => now(),
                    ]
                );
            }
        }

        // D. HANDLE PAYOUT METHOD (Rekening Bank)
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

        // [FIX] Menggunakan 'success' agar langsung ditangkap oleh alert app.blade.php
        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui!');
    }

    /*
    |--------------------------------------------------------------------------
    | 3. DELETE ACCOUNT
    |--------------------------------------------------------------------------
    | Menghapus akun user secara permanen dari database setelah konfirmasi password.
    */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Akun berhasil dihapus. Sampai jumpa lagi!');
    }

    public function updatePin(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            // User wajib masukkan password login dulu biar aman
            'current_password' => ['required', 'current_password'],
            // PIN wajib 6 angka & harus dikonfirmasi
            'pin' => ['required', 'numeric', 'digits:6', 'confirmed'],
        ]);

        // 2. Simpan PIN ke Database (Dienkripsi)
        $request->user()->update([
            'security_pin' => Hash::make($request->pin),
        ]);

        // 3. Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('status', 'pin-updated');
    }

    public function updateAvatar(Request $request)
    {
        // 1. Validasi: Pastikan data base64 dari cropper masuk
        $request->validate([
            'cropped_image' => 'required'
        ]);

        try {
            // 2. Inisialisasi Manual (Tanpa lewat Facade yang error tadi)
            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key' => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => [
                    'secure' => true
                ]
            ]);

            // 3. Upload Data Base64 ke Cloudinary
            $uploadResult = $cloudinary->uploadApi()->upload(
                $request->cropped_image,
                [
                    'folder' => 'avatars',
                    'transformation' => [
                        'width' => 400,
                        'height' => 400,
                        'crop' => 'fill',
                        'gravity' => 'face' // <--- Menjamin wajah user selalu di tengah
                    ]
                ]
            );

            // 4. Update Database
            auth()->user()->update([
                'avatar_url' => $uploadResult['secure_url']
            ]);

            // Karena frontend kamu pakai AJAX (fetch), kita kembalikan JSON
            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully!',
                'url' => $uploadResult['secure_url']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal upload: ' . $e->getMessage()
            ], 500);
        }
    }
}