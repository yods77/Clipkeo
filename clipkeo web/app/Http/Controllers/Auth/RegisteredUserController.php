<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view with province and previously selected regency options.
     */
    public function create(): View
    {
        $provinces = DB::table('provinces')
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedProvince = old('province');
        $regencies = $selectedProvince
            ? DB::table('regencies')
                ->where('province_id', $selectedProvince)
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('auth.register', compact('provinces', 'regencies'));
    }

    /**
     * Return only regencies belonging to the selected province.
     */
    public function regencies(string $province): JsonResponse
    {
        $provinceExists = DB::table('provinces')
            ->where('id', $province)
            ->exists();

        if (!$provinceExists) {
            return response()->json(['message' => 'Provinsi tidak ditemukan.'], 404);
        }

        $regencies = DB::table('regencies')
            ->where('province_id', $province)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($regencies);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:clipper,publisher'],
            'gender' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'province' => ['required', 'string', Rule::exists('provinces', 'id')],
            'city' => [
                'required',
                'string',
                Rule::exists('regencies', 'id')->where(
                    fn ($query) => $query->where('province_id', $request->input('province'))
                ),
            ],
        ]);

        $baseUsername = Str::slug($request->input('name')) ?: 'clipper';

        do {
            $username = Str::limit($baseUsername, 40, '') . '-' . Str::lower(Str::random(6));
        } while (User::where('username', $username)->exists());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $username,
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
