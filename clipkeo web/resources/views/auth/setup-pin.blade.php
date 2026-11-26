<x-guest-layout>
    {{-- Container Utama --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-[#050505] overflow-hidden">
        
        {{-- ================================================= --}}
        {{-- 1. LOGO KIRI ATAS                                --}}
        {{-- ================================================= --}}
        <div class="absolute top-6 left-6 z-50 flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" 
                 alt="Logo" 
                 class="w-20 h-20 object-contain drop-shadow-[0_0_15px_rgba(216,255,21,0.6)]">
        </div>

        {{-- ================================================= --}}
        {{-- 2. LOGOUT KANAN ATAS                             --}}
        {{-- ================================================= --}}
        <div class="absolute top-8 right-8 z-50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-bold text-gray-400 hover:text-[#d8ff15] transition-colors tracking-widest uppercase flex items-center gap-2 group">
                    Logout
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>

        {{-- ================================================= --}}
        {{-- 3. BACKGROUND GRADIENT NEON (ENHANCED)           --}}
        {{-- ================================================= --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-tr from-green-800/20 via-[#d8ff15]/10 to-transparent rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-green-500/20 rounded-full blur-[80px] pointer-events-none"></div>

        {{-- ================================================= --}}
        {{-- CARD FORM UTAMA                                  --}}
        {{-- ================================================= --}}
        <div class="w-full max-w-md bg-[#0A0A0A] border border-white/10 rounded-[2.5rem] shadow-2xl p-8 relative z-10 backdrop-blur-sm">
            
            <div class="text-center mb-8">
                
                <h2 class="text-2xl font-black text-white tracking-tight">Amankan Akun Anda</h2>
                <p class="text-sm text-gray-400 mt-2">
                    Halo <span class="text-white font-bold">{{ Auth::user()->name }}</span>, demi keamanan transaksi, mohon buat <span class="text-neon font-bold">PIN 6 Digit</span> sebelum melanjutkan.
                </p>
            </div>

            <form method="POST" action="{{ route('pin.store') }}" class="space-y-6">
                @csrf

                {{-- Input PIN Baru --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Buat PIN Baru</label>
                    <input type="password" name="pin" required maxlength="6" inputmode="numeric" pattern="[0-9]*" autofocus
                        class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-4 text-center text-2xl font-mono font-bold tracking-[0.5em] text-white focus:ring-2 focus:ring-[#d8ff15] focus:border-transparent outline-none transition placeholder-gray-700 shadow-inner"
                        placeholder="••••••">
                    @error('pin')
                        <p class="text-red-500 text-xs mt-2 font-bold text-center">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Konfirmasi PIN --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Konfirmasi PIN</label>
                    <input type="password" name="pin_confirmation" required maxlength="6" inputmode="numeric" pattern="[0-9]*"
                        class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-4 text-center text-2xl font-mono font-bold tracking-[0.5em] text-white focus:ring-2 focus:ring-[#d8ff15] focus:border-transparent outline-none transition placeholder-gray-700 shadow-inner"
                        placeholder="••••••">
                </div>

                {{-- Tombol Submit --}}
                <button type="submit" 
                    class="w-full py-4 bg-[#d8ff15] hover:bg-[#b5d600] text-black font-black rounded-xl transition-all shadow-[0_0_20px_rgba(216,255,21,0.3)] hover:shadow-[0_0_30px_rgba(216,255,21,0.5)] transform hover:scale-[1.02]">
                    SIMPAN & LANJUTKAN
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>