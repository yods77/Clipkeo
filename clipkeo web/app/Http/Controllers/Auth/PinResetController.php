<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPinMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class PinResetController extends Controller
{
    private const OTP_TTL_MINUTES = 10;
    private const OTP_MAX_ATTEMPTS = 5;

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|max:255']);

        $email = Str::lower(trim($request->input('email')));
        $sendKey = $this->sendKey($email);

        if (RateLimiter::tooManyAttempts($sendKey, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan OTP. Silakan coba lagi beberapa saat.',
            ], 429);
        }

        RateLimiter::hit($sendKey, self::OTP_TTL_MINUTES * 60);

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // Do not reveal whether an email address has a Clipkeo account.
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email terdaftar, kode OTP akan dikirim.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        DB::table('pin_reset_tokens')->where('email', $email)->delete();
        DB::table('pin_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($otp),
            'created_at' => now(),
        ]);

        try {
            Mail::to($email)->send(new ResetPinMail($otp));
        } catch (Throwable $exception) {
            DB::table('pin_reset_tokens')->where('email', $email)->delete();

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP belum dapat dikirim. Silakan coba lagi nanti.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar, kode OTP akan dikirim.',
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'otp' => 'required|digits:6',
        ]);

        $email = Str::lower(trim($request->input('email')));
        $attemptKey = $this->verifyAttemptKey($email);

        if (RateLimiter::tooManyAttempts($attemptKey, self::OTP_MAX_ATTEMPTS)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan OTP. Minta kode baru dan coba lagi nanti.',
            ], 429);
        }

        $resetData = DB::table('pin_reset_tokens')->where('email', $email)->first();

        if (
            !$resetData ||
            !$resetData->created_at ||
            Carbon::parse($resetData->created_at)->addMinutes(self::OTP_TTL_MINUTES)->isPast()
        ) {
            if ($resetData) {
                DB::table('pin_reset_tokens')->where('email', $email)->delete();
            }

            RateLimiter::hit($attemptKey, self::OTP_TTL_MINUTES * 60);

            return response()->json([
                'success' => false,
                'message' => 'OTP tidak valid atau sudah kedaluwarsa. Silakan minta kode baru.',
            ], 400);
        }

        if (!Hash::check($request->input('otp'), $resetData->token)) {
            RateLimiter::hit($attemptKey, self::OTP_TTL_MINUTES * 60);

            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid.',
            ], 400);
        }

        RateLimiter::clear($attemptKey);

        return response()->json([
            'success' => true,
            'message' => 'OTP valid.',
        ]);
    }

    public function resetPin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'otp' => 'required|digits:6',
            'pin' => 'required|digits:6|confirmed',
        ]);

        $email = Str::lower(trim($request->input('email')));
        $attemptKey = $this->resetAttemptKey($email);

        if (RateLimiter::tooManyAttempts($attemptKey, self::OTP_MAX_ATTEMPTS)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan OTP. Minta kode baru dan coba lagi nanti.',
            ], 429);
        }

        $result = DB::transaction(function () use ($request, $email) {
            $resetData = DB::table('pin_reset_tokens')
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (!$resetData) {
                return 'invalid';
            }

            if (
                !$resetData->created_at ||
                Carbon::parse($resetData->created_at)->addMinutes(self::OTP_TTL_MINUTES)->isPast()
            ) {
                DB::table('pin_reset_tokens')->where('email', $email)->delete();
                return 'expired';
            }

            if (!Hash::check($request->input('otp'), $resetData->token)) {
                return 'invalid';
            }

            $user = User::whereRaw('LOWER(email) = ?', [$email])
                ->lockForUpdate()
                ->first();

            if (!$user) {
                DB::table('pin_reset_tokens')->where('email', $email)->delete();
                return 'invalid';
            }

            $user->security_pin = Hash::make($request->input('pin'));
            $user->save();

            DB::table('pin_reset_tokens')->where('email', $email)->delete();

            return 'success';
        });

        if ($result === 'success') {
            RateLimiter::clear($attemptKey);

            return response()->json([
                'success' => true,
                'message' => 'PIN berhasil diubah. Silakan gunakan PIN baru Anda.',
            ]);
        }

        RateLimiter::hit($attemptKey, self::OTP_TTL_MINUTES * 60);

        return response()->json([
            'success' => false,
            'message' => $result === 'expired'
                ? 'OTP sudah kedaluwarsa. Silakan minta kode baru.'
                : 'Kode OTP tidak valid.',
        ], 400);
    }

    private function sendKey(string $email): string
    {
        return 'pin-reset-send:' . hash('sha256', $email);
    }

    private function verifyAttemptKey(string $email): string
    {
        return 'pin-reset-verify:' . hash('sha256', $email);
    }

    private function resetAttemptKey(string $email): string
    {
        return 'pin-reset-attempt:' . hash('sha256', $email);
    }
}
