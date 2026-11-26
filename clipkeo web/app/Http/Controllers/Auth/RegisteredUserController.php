<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. VALIDASI: Pastikan user memilih role yang valid (clipper atau publisher)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:clipper,publisher'], // <--- BARIS PENTING INI
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'birthday' => ['required', 'date'],
            'province' => ['required', 'exists:provinces,id'], // Pastikan ID ada di tabel provinces
            'city' => ['required', 'exists:regencies,id'],
        ]);

        // 2. SIMPAN KE DATABASE
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => \Illuminate\Support\Str::slug($request->name) . rand(10, 99),
            'phone_number' => $request->phone_number,
            'role' => $request->role,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'province_id' => $request->province,
            'city_id' => $request->city,
            'reputation_points' => 0,
            'reputation_score' => 1,
        ]);

        $starterBadge = \App\Models\Badge::where('xp_req', 0)->first();

        if ($starterBadge) {
            $user->badges()->attach($starterBadge->id, ['awarded_at' => now()]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
