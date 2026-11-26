<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureHasPin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Cek apakah user login & PIN-nya masih NULL
        if ($user && is_null($user->security_pin)) {
            
            // 2. Cek agar tidak terjadi "Looping Redirect"
            // Jika user sudah berada di halaman setup pin, biarkan dia lewat.
            if ($request->routeIs('pin.setup') || $request->routeIs('pin.store') || $request->routeIs('logout')) {
                return $next($request);
            }

            // 3. Jika user di halaman lain (Dashboard dll), lempar ke Setup PIN
            return redirect()->route('pin.setup');
        }

        return $next($request);
    }
}