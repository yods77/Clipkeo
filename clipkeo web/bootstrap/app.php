<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // [PENTING] Wajib di-import untuk fitur Redirect
use App\Http\Middleware\Localization;

return Application::configure(basePath: dirname(__DIR__))
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI ROUTING
    |--------------------------------------------------------------------------
    | Mengatur jalur file untuk web, console commands, dan health check.
    */
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI MIDDLEWARE (PINTU GERBANG)
    |--------------------------------------------------------------------------
    | Disini kita mengatur semua logika yang berjalan sebelum request sampai ke Controller.
    | Saya gabungkan semua settingan Anda (Localization, Xendit, Redirect) disini.
    */
    ->withMiddleware(function (Middleware $middleware) {

        // [FUNGSI 1] LOCALIZATION (BAHASA)
        // Menambahkan middleware untuk mendeteksi bahasa (ID/EN) pada grup 'web'.
        $middleware->web(append: [
            Localization::class,
        ]);

        // [FUNGSI 2] EXCEPTION CSRF TOKEN (KEAMANAN)
        // Mengecualikan URL webhook Xendit dari pengecekan CSRF Token
        // agar Xendit bisa mengirim data konfirmasi pembayaran tanpa diblokir.
        $middleware->validateCsrfTokens(except: [
            'webhooks/xendit', 
        ]);

        $middleware->alias([
            'has.pin' => \App\Http\Middleware\EnsureHasPin::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);

        // [FUNGSI 3] REDIRECT GUEST / SESSION EXPIRED (UPDATE BARU)
        // Mengatur perilaku jika user belum login atau sesinya habis (expired).
        // Alih-alih ke halaman login default, kita lempar ke Landing Page
        // dengan parameter '?session_expired=true' agar Modal Login otomatis terbuka.
        $middleware->redirectGuestsTo(function (Request $request) {
            // Jika request berupa API (JSON), jangan redirect (biarkan 401 Unauthorized)
            if ($request->expectsJson()) {
                return null;
            }
            // Jika akses Web biasa, arahkan ke Home dengan sinyal session expired
            return '/?session_expired=true';
        });

    })

    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI EXCEPTION (ERROR HANDLING)
    |--------------------------------------------------------------------------
    */
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();