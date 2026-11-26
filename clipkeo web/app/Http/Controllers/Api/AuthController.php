<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $baseUsername = Str::slug(Str::before($request->name, ' ')) ?: 'clipper';
        do {
            $username = substr($baseUsername, 0, 20) . random_int(100, 9999);
        } while (User::where('username', $username)->exists());

        $user = User::create([
            'name' => $request->name,
            'email' => Str::lower(trim($request->email)),
            'password' => Hash::make($request->password),
            'username' => $username,
            'phone_number' => $request->phone_number,
            'role' => 'clipper',
            'wallet_balance' => 0,
            'reputation_score' => 1,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Register berhasil',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // LOGIN
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email atau password tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = Str::lower(trim($request->input('email')));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user || !$user->password || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Email atau password tidak valid.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // GOOGLE LOGIN: only accept a Google ID token verified by Google.
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Token Google wajib dikirim.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $clientId = config('services.google.client_id');

        if (empty($clientId)) {
            return response()->json([
                'message' => 'Google Sign-In belum dikonfigurasi di server.',
            ], 503);
        }

        try {
            $googleClient = new GoogleClient(['client_id' => $clientId]);
            $payload = $googleClient->verifyIdToken($request->input('id_token'));

            if (
                !$payload ||
                empty($payload['sub']) ||
                empty($payload['email']) ||
                !filter_var($payload['email'], FILTER_VALIDATE_EMAIL) ||
                !filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ) {
                return response()->json([
                    'message' => 'Token Google tidak valid atau email belum terverifikasi.',
                ], 401);
            }

            $googleId = (string) $payload['sub'];
            $email = Str::lower(trim($payload['email']));

            $user = User::where('google_id', $googleId)->first();

            if (!$user) {
                $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

                if ($user && $user->google_id && !hash_equals((string) $user->google_id, $googleId)) {
                    return response()->json([
                        'message' => 'Akun Google tidak cocok dengan akun Clipkeo ini.',
                    ], 409);
                }

                if (!$user) {
                    $name = trim((string) ($payload['name'] ?? ''));
                    if ($name === '') {
                        $name = Str::before($email, '@');
                    }

                    $baseUsername = Str::slug(Str::before($name, ' ')) ?: 'clipper';
                    do {
                        $username = substr($baseUsername, 0, 20) . random_int(100, 9999);
                    } while (User::where('username', $username)->exists());

                    $picture = $payload['picture'] ?? null;
                    $avatarUrl = is_string($picture) && str_starts_with($picture, 'https://')
                        ? $picture
                        : null;

                    $user = User::create([
                        'name' => Str::limit($name, 255, ''),
                        'email' => $email,
                        'password' => Hash::make(Str::random(64)),
                        'username' => $username,
                        'google_id' => $googleId,
                        'avatar_url' => $avatarUrl,
                        'role' => 'clipper',
                    ]);
                } else {
                    // A verified Google email may be linked to the matching existing account.
                    $user->google_id = $googleId;
                    $user->save();
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (Throwable $exception) {
            // Never log the submitted ID token, email, or raw exception payload.
            Log::error('Google Sign-In token verification failed.', [
                'exception' => get_class($exception),
            ]);

            return response()->json([
                'message' => 'Google Sign-In tidak dapat diverifikasi saat ini. Silakan coba lagi.',
            ], 503);
        }
    }

    public function updatePhoneNumber(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:10|max:30|unique:users,phone_number',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 401);
        }

        $user->update(['phone_number' => $request->phone_number]);

        return response()->json([
            'success' => true,
            'message' => 'Nomor HP berhasil diperbarui!',
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string|max:4096',
        ]);

        $request->user()->update([
            'fcm_token' => $request->fcm_token,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FCM Token berhasil diperbarui!',
        ]);
    }

    // LOGOUT: revoke the current Sanctum token.
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $currentToken = $user->currentAccessToken();

            if ($currentToken && method_exists($currentToken, 'delete')) {
                $currentToken->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Token akses telah dicabut.',
        ]);
    }
}
