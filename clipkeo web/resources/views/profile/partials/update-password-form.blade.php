<section>
    <header>
        {{-- Judul H2 dihapus agar tidak duplikat --}}
        <p class="text-sm text-gray-500 mb-6">
            {{ __('Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        @if (!Auth::user()->google_id)
            <div>
                <label for="update_password_current_password" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">{{ __('Password Saat Ini') }}</label>
                <input id="update_password_current_password" name="current_password" type="password" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition placeholder-gray-400 hover:bg-white" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>
        @endif

        <div>
            <label for="update_password_password" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">{{ __('Password Baru') }}</label>
            <input id="update_password_password" name="password" type="password" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition placeholder-gray-400 hover:bg-white" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">{{ __('Konfirmasi Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3.5 text-sm text-gray-900 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition placeholder-gray-400 hover:bg-white" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            {{-- Tombol Amber/Yellow menyesuaikan ikon "Keamanan" di parent card --}}
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-amber-200 transition transform hover:-translate-y-0.5 text-sm uppercase tracking-widest">
                {{ __('Update Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600 font-bold flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ __('Tersimpan.') }}
                </p>
            @endif
        </div>
    </form>
</section>