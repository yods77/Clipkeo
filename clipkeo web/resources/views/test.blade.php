<x-app-layout>
    <x-slot name="header">
        <div class="pt-24 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-white leading-tight">
                    {{ __('Papan Misi') }}
                </h2>
                {{-- Breadcrumb kecil --}}
                <div class="flex items-center gap-2 text-xs text-zinc-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-zinc-300 transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-zinc-300">Cari Misi</span>
                </div>
            </div>

            {{-- SEARCH BAR (Tetap ada di atas untuk akses cepat) --}}
            <div class="flex flex-1 md:max-w-xl gap-2">
                <div class="relative w-full group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-zinc-500 group-focus-within:text-indigo-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" placeholder="Cari brand atau misi..." 
                        class="w-full bg-[#0A0A0A] border border-zinc-800 text-zinc-300 text-sm rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 placeholder-zinc-600 transition shadow-inner">
                </div>
                
                @auth
                    @if (auth()->user()->role === 'publisher')
                        <a href="{{ route('bounties.create') }}" class="whitespace-nowrap px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl transition shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                            <span class="hidden sm:inline">Buat Misi</span>
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- === BAGIAN BARU: HERO HEADER (Sesuai Request) === --}}
            <div class="text-center mb-16 relative">
                {{-- Efek Glow di Belakang Teks --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[100px] bg-indigo-500/20 blur-[80px] rounded-full pointer-events-none"></div>

                <h1 class="relative text-4xl md:text-6xl font-black text-white tracking-tight mb-4 leading-tight">
                    Bikin Konten, <br class="md:hidden" />
                    {{-- Teks Gradasi (Orange -> Pink -> Ungu) --}}
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-400 via-pink-500 to-purple-600">
                        Panen Cuan.
                    </span>
                </h1>
                <p class="relative text-zinc-400 text-sm md:text-base max-w-xl mx-auto leading-relaxed">
                    Gak perlu nunggu jadi artis. Cukup upload video kreatifmu dan menangkan hadiahnya.
                </p>
            </div>
            {{-- === AKHIR BAGIAN HERO === --}}

            {{-- FILTER BUTTONS --}}
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-end gap-4 border-b border-zinc-800/50 pb-4">
                <div class="flex gap-3 overflow-x-auto no-scrollbar w-full sm:w-auto pb-2">
                    <button class="px-5 py-2 rounded-full bg-white text-black text-xs font-black uppercase tracking-wider border border-white hover:scale-105 transition shadow-lg shadow-white/10">
                        Semua
                    </button>
                    {{-- TikTok --}}
                    <button class="flex items-center gap-2 px-5 py-2 rounded-full bg-[#000000] border border-zinc-800 text-white text-xs font-bold hover:border-zinc-500 transition group">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 1 0-1 13.6 6.84 6.84 0 0 0 6.25-6.62V9.42c1 .56 2.15.86 3.28.88v-3.6a4.86 4.86 0 0 1-.3-4.01z"/></svg>
                        TikTok
                    </button>
                    {{-- YouTube --}}
                    <button class="flex items-center gap-2 px-5 py-2 rounded-full bg-[#000000] border border-zinc-800 text-white text-xs font-bold hover:border-red-600 hover:text-red-500 transition group">
                        <svg class="w-3 h-3 fill-current text-red-600 group-hover:text-red-500" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        Shorts
                    </button>
                    {{-- Instagram --}}
                    <button class="flex items-center gap-2 px-5 py-2 rounded-full bg-[#000000] border border-zinc-800 text-white text-xs font-bold hover:border-pink-500 hover:text-pink-400 transition group">
                        <svg class="w-3 h-3 fill-current text-pink-500 group-hover:text-pink-400" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.948-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        Reels
                    </button>
                </div>
                
                {{-- Total Count --}}
                <span class="text-xs text-zinc-500 font-medium mb-2">Menampilkan {{ $bounties->count() }} Misi Aktif</span>
            </div>

            {{-- GRID CARD (CARD UTAMA) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($bounties as $bounty)
                    @php
                        $isFinished = $bounty->status === 'completed' || !is_null($bounty->winner_id) || $bounty->remaining_budget < 1000;
                        
                        // Hitung Persentase
                        $percentage = 0;
                        if ($bounty->reward_amount > 0) {
                            $percentage = ($bounty->remaining_budget / $bounty->reward_amount) * 100;
                        }

                        // Warna Bar
                        $barColor = $percentage > 50 ? 'bg-green-500' : ($percentage > 20 ? 'bg-yellow-500' : 'bg-red-500');
                        $textColor = $percentage < 20 ? 'text-red-400' : 'text-zinc-400';
                        
                        // Konfig Badge
                        $badgeConfig = match ($bounty->distribution_strategy) {
                            'top_3_tiered' => ['icon' => '🥇', 'text' => 'TOP 3 JUARA', 'bg' => 'bg-amber-600 border-amber-500'],
                            'equal_split' => ['icon' => '🤝', 'text' => 'BAGI RATA', 'bg' => 'bg-blue-600 border-blue-500'],
                            default => ['icon' => '📈', 'text' => 'TIER VIEWS', 'bg' => 'bg-emerald-600 border-emerald-500'],
                        };
                    @endphp

                    {{-- X-DATA SEKARANG PUNYA 3 STATUS --}}
                    <div x-data="{ modalTier: false, modalInst: false, modalSubmit: false }" class="group relative flex flex-col h-full">
                        
                        {{-- CARD BOX --}}
                        <div class="relative bg-[#0F0F0F] border border-zinc-800 rounded-[2rem] overflow-hidden hover:border-zinc-600 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 flex flex-col h-full {{ $isFinished ? 'opacity-60 grayscale-[0.8]' : 'hover:-translate-y-1' }}">
                            
                            {{-- 1. COVER IMAGE --}}
                            <div class="relative h-48 overflow-hidden bg-zinc-900">
                                @if ($bounty->image_path)
                                    <img src="{{ asset('storage/' . $bounty->image_path) }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                                @else
                                    <img src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?q=80&w=1000&auto=format&fit=crop" class="w-full h-full object-cover opacity-50">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0F0F0F] via-transparent to-transparent"></div>
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <div class="px-3 py-1.5 rounded-lg border {{ $badgeConfig['bg'] }} text-white text-[10px] font-black uppercase tracking-wider flex items-center gap-1 shadow-lg">
                                        <span>{{ $badgeConfig['icon'] }}</span> {{ $badgeConfig['text'] }}
                                    </div>
                                </div>
                                <div class="absolute top-4 right-4">
                                    @if($isFinished)
                                        <span class="bg-zinc-800 border border-zinc-600 text-white text-[10px] font-black px-3 py-1.5 rounded-lg">SELESAI</span>
                                    @else
                                        <span class="bg-red-600 border border-red-500 text-white text-[10px] font-black px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-lg animate-pulse">
                                            <span class="w-2 h-2 rounded-full bg-white"></span> LIVE
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- 2. CARD BODY --}}
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-xl font-bold text-white leading-tight mb-4 line-clamp-2 group-hover:text-indigo-400 transition">{{ $bounty->title }}</h3>

                                <div class="flex items-center gap-3 mb-6">
                                    @php
                                        $publisherName = $bounty->publisher->name ?? 'Unknown Publisher';
                                        $publisherAvatar = $bounty->publisher->avatar_url ?? null;
                                    @endphp
                                    <img class="w-9 h-9 rounded-full border border-zinc-700 object-cover" 
                                        src="{{ $publisherAvatar ? (str_starts_with($publisherAvatar, 'http') ? $publisherAvatar : asset('storage/' . $publisherAvatar)) : 'https://ui-avatars.com/api/?name=' . urlencode($publisherName) . '&background=random&color=fff' }}" 
                                        alt="{{ $publisherName }}">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-white">{{ $publisherName }}</span>
                                        <span class="text-[10px] text-zinc-500">Verified</span>
                                    </div>
                                </div>

                                {{-- REWARD BAR --}}
                                <div class="bg-zinc-900/80 border border-zinc-800 rounded-xl p-4 mb-6 mt-auto relative group-hover:border-orange-500/30 transition-colors">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Reward Detail</p>
                                            {{-- TOMBOL TRIGGER MODAL TIER --}}
                                            <button @click="modalTier = true" class="text-sm font-bold text-orange-500 hover:text-orange-400 underline decoration-orange-500/30 underline-offset-4 transition">
                                                Lihat Tier
                                            </button>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-1">Min. Views</p>
                                            <div class="flex items-center justify-end gap-1">
                                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                <p class="text-sm font-black text-orange-500">{{ number_format($bounty->min_views) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-full h-px bg-zinc-800 mb-4"></div>
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider">Sisa Budget</span>
                                            <span class="text-[10px] font-bold text-orange-500">{{ number_format($percentage, 0) }}% <span class="text-zinc-600 font-normal">dari Rp {{ number_format($bounty->reward_amount) }}</span></span>
                                        </div>
                                        <div class="w-full bg-zinc-800 rounded-full h-2 overflow-hidden">
                                            <div class="bg-gradient-to-r from-orange-600 to-amber-500 h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(245,158,11,0.5)]" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TOMBOL AKSI UTAMA --}}
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- TOMBOL TRIGGER MODAL INSTRUKSI --}}
                                    <button @click="modalInst = true" class="py-3 rounded-xl border border-zinc-700 text-zinc-300 font-bold text-xs uppercase tracking-wider hover:bg-zinc-800 hover:text-white transition flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Instruksi
                                    </button>
                                    
                                    @if(!$isFinished)
                                        {{-- TOMBOL TRIGGER MODAL SUBMIT --}}
                                        <button @click="modalSubmit = true" class="py-3 rounded-xl bg-indigo-600 text-white font-bold text-xs uppercase tracking-wider hover:bg-indigo-500 transition shadow-lg shadow-indigo-900/30 flex items-center justify-center gap-2">
                                            Submit
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </button>
                                    @else
                                        <button disabled class="py-3 rounded-xl bg-zinc-800 text-zinc-500 font-bold text-xs uppercase tracking-wider cursor-not-allowed">Ditutup</button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ========================================================== --}}
                        {{--                     MODAL 1: TIER REWARD                   --}}
                        {{-- ========================================================== --}}
                        <template x-teleport="body">
                            <div x-show="modalTier" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                                <div @click="modalTier = false" class="fixed inset-0 bg-black/90 backdrop-blur-sm transition-opacity"></div>
                                <div class="flex min-h-full items-center justify-center p-4">
                                    <div x-show="modalTier" x-transition class="relative w-full max-w-lg bg-[#0F0F0F] border border-zinc-800 rounded-3xl overflow-hidden shadow-2xl p-6 text-center">
                                        <button @click="modalTier = false" class="absolute top-4 right-4 text-zinc-500 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                        
                                        <h3 class="text-xl font-black text-white mb-2">Skema Hadiah</h3>
                                        
                                        {{-- 1. PENJELASAN SISTEM (HEADER) --}}
                                        <p class="text-xs text-zinc-400 mb-6 px-4 leading-relaxed">
                                            @if ($bounty->distribution_strategy === 'performance_tiered')
                                                Misi ini menggunakan sistem <strong>Performance Tiered</strong>. Semakin banyak views videomu, semakin besar reward yang bisa kamu cairkan.
                                            @elseif($bounty->distribution_strategy === 'top_3_tiered')
                                                Misi ini adalah <strong>Kompetisi Juara</strong>. Buat video terkreatif dan raih posisi Top 3 untuk memenangkan hadiah utama.
                                            @elseif($bounty->distribution_strategy === 'equal_split')
                                                Misi ini menggunakan sistem <strong>Bagi Rata (Flat)</strong>. Total hadiah akan dibagi rata kepada semua pemenang yang terpilih.
                                            @endif
                                        </p>

                                        {{-- KONTEN TIER --}}
                                        @if ($bounty->distribution_strategy === 'performance_tiered')
                                            <div class="bg-zinc-900/50 rounded-xl border border-zinc-800 overflow-hidden text-left mb-6">
                                                <div class="p-4 border-b border-zinc-800 bg-zinc-900 flex justify-between items-center">
                                                    <span class="text-xs text-zinc-400">Target: <strong class="text-white">{{ number_format($bounty->min_views) }} Views</strong></span>
                                                    <span class="text-[10px] px-2 py-1 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold uppercase">Performance</span>
                                                </div>
                                                <table class="w-full text-sm">
                                                    <thead class="bg-white/5 text-xs text-zinc-400 uppercase font-bold">
                                                        <tr><th class="px-4 py-3">Views</th><th class="px-4 py-3 text-right">Reward</th></tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-white/5 text-zinc-300">
                                                        <tr><td class="px-4 py-3">{{ number_format($bounty->min_views) }} - {{ number_format($bounty->min_views * 5) }}</td><td class="px-4 py-3 text-right font-mono text-emerald-400">Rp 10.000</td></tr>
                                                        <tr><td class="px-4 py-3">{{ number_format($bounty->min_views * 5) }} - {{ number_format($bounty->min_views * 20) }}</td><td class="px-4 py-3 text-right font-mono text-emerald-400">Rp 100.000</td></tr>
                                                        <tr class="bg-emerald-500/10"><td class="px-4 py-3 font-bold text-white">{{ number_format($bounty->min_views * 20) }}+ (Max)</td><td class="px-4 py-3 text-right font-mono text-emerald-400 font-bold">Rp 500.000</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @elseif($bounty->distribution_strategy === 'top_3_tiered')
                                            <div class="space-y-3 text-left mb-6">
                                                <div class="flex justify-between items-center p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl">
                                                    <div class="flex items-center gap-3"><div class="w-8 h-8 rounded-full bg-amber-500 text-black flex items-center justify-center font-bold">1</div><span class="text-amber-400 font-bold">Juara 1</span></div><span class="text-white font-mono font-bold">Rp {{ number_format($bounty->reward_amount * 0.5) }}</span>
                                                </div>
                                                <div class="flex justify-between items-center p-3 bg-zinc-900 border border-zinc-700 rounded-xl">
                                                    <div class="flex items-center gap-3"><div class="w-7 h-7 rounded-full bg-zinc-700 text-white flex items-center justify-center font-bold text-sm">2</div><span class="text-zinc-300 font-bold">Juara 2</span></div><span class="text-zinc-300 font-mono">Rp {{ number_format($bounty->reward_amount * 0.3) }}</span>
                                                </div>
                                                <div class="flex justify-between items-center p-3 bg-zinc-900/50 border border-zinc-800 rounded-xl">
                                                    <div class="flex items-center gap-3"><div class="w-7 h-7 rounded-full bg-orange-900 text-orange-200 flex items-center justify-center font-bold text-sm">3</div><span class="text-zinc-500 font-bold">Juara 3</span></div><span class="text-zinc-500 font-mono">Rp {{ number_format($bounty->reward_amount * 0.2) }}</span>
                                                </div>
                                            </div>
                                        @elseif($bounty->distribution_strategy === 'equal_split')
                                            <div class="p-6 bg-blue-500/10 border border-blue-500/20 rounded-2xl mb-6">
                                                <p class="text-blue-200 text-xs font-bold uppercase mb-2">Flat Reward Per Orang</p>
                                                <div class="text-3xl font-black text-blue-400 font-mono mb-2">Rp {{ number_format($bounty->reward_amount / ($bounty->winner_count > 0 ? $bounty->winner_count : 1), 0, ',', '.') }}</div>
                                                <p class="text-xs text-blue-300/60">Untuk {{ $bounty->winner_count }} Pemenang</p>
                                            </div>
                                        @endif

                                        {{-- 2. CATATAN PAYOUT (FOOTER) --}}
                                        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 text-left flex gap-3 items-start">
                                            <svg class="w-5 h-5 text-zinc-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <p class="text-xs text-zinc-400 leading-snug">
                                                <strong class="text-zinc-300 block mb-1">Catatan:</strong>
                                                Payout final akan ditentukan berdasarkan jumlah views yang diverifikasi oleh publisher setelah submission disetujui.
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </template>


                        {{-- ========================================================== --}}
                        {{--                  MODAL 2: INSTRUKSI & ASET                 --}}
                        {{-- ========================================================== --}}
                        <template x-teleport="body">
                            <div x-show="modalInst" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                                <div @click="modalInst = false" class="fixed inset-0 bg-black/90 backdrop-blur-sm transition-opacity"></div>
                                <div class="flex min-h-full items-center justify-center p-4">
                                    <div x-show="modalInst" x-transition class="relative w-full max-w-2xl bg-[#0F0F0F] border border-zinc-800 rounded-3xl overflow-hidden shadow-2xl">
                                        
                                        {{-- 1. HEADER GAMBAR (COVER) --}}
                                        <div class="h-56 relative group">
                                            @if ($bounty->image_path)
                                                <img src="{{ asset('storage/' . $bounty->image_path) }}" class="w-full h-full object-cover transition duration-700">
                                            @else
                                                <div class="w-full h-full bg-zinc-800 flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            @endif
                                            
                                            {{-- Overlay Gradasi --}}
                                            <div class="absolute inset-0 bg-gradient-to-t from-[#0F0F0F] via-[#0F0F0F]/40 to-transparent"></div>
                                            
                                            {{-- Tombol Close --}}
                                            <button @click="modalInst = false" class="absolute top-4 right-4 z-50 p-2 bg-black/40 hover:bg-white hover:text-black text-white rounded-full transition border border-white/10 backdrop-blur-md">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>

                                            {{-- Judul di Cover --}}
                                            <div class="absolute bottom-6 left-8 right-8">
                                                <h3 class="text-3xl font-black text-white leading-tight drop-shadow-lg">{{ $bounty->title }}</h3>
                                            </div>
                                        </div>

                                        <div class="p-8 space-y-8">
                                            
                                            {{-- 2. DETAIL INSTRUKSI (SCROLLABLE AREA) --}}
                                            <div>
                                                <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    Instruksi Lengkap
                                                </h4>
                                                
                                                {{-- UPDATE DI SINI: --}}
                                                {{-- 1. max-h-64 : Membatasi tinggi maksimal sekitar 250px --}}
                                                {{-- 2. overflow-y-auto : Memunculkan scrollbar jika teks kepanjangan --}}
                                                {{-- 3. pr-2 : Memberi jarak sedikit agar teks tidak tertutup scrollbar --}}
                                                <div class="text-zinc-300 text-sm leading-snug font-light p-5 bg-zinc-900/50 rounded-2xl border border-zinc-800/50 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                                                    {!! nl2br(e($bounty->description)) !!}
                                                </div>
                                                
                                                {{-- Hint kecil untuk user (opsional) --}}
                                                <p class="text-[10px] text-zinc-600 mt-2 text-right italic">Scroll ke bawah untuk baca selengkapnya &darr;</p>
                                            </div>

                                            {{-- 3. GRID INFO (DEADLINE, HASHTAG, MENTION) --}}
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                {{-- Deadline --}}
                                                <div class="p-4 bg-zinc-900 rounded-xl border border-zinc-800 flex flex-col justify-center">
                                                    <span class="text-[10px] font-bold text-zinc-500 uppercase mb-1 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        Deadline
                                                    </span>
                                                    <span class="text-white font-bold text-sm">
                                                        {{ \Carbon\Carbon::parse($bounty->deadline_at)->format('d M Y') }}
                                                    </span>
                                                </div>

                                                {{-- Hashtag --}}
                                                <div class="p-4 bg-zinc-900 rounded-xl border border-zinc-800">
                                                    <span class="block text-[10px] font-bold text-zinc-500 uppercase mb-1">Wajib Hashtag</span>
                                                    <span class="text-indigo-400 font-mono text-xs break-words font-medium">
                                                        {{ $bounty->required_hashtags ?? '-' }}
                                                    </span>
                                                </div>

                                                {{-- Mention --}}
                                                <div class="p-4 bg-zinc-900 rounded-xl border border-zinc-800">
                                                    <span class="block text-[10px] font-bold text-zinc-500 uppercase mb-1">Wajib Mention</span>
                                                    <span class="text-indigo-400 font-mono text-xs break-words font-medium">
                                                        {{ $bounty->required_mentions ?? '-' }}
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- 4. TOMBOL DOWNLOAD BAHAN --}}
                                            @if($bounty->source_link)
                                                <div class="pt-4 border-t border-zinc-800">
                                                    <a href="{{ $bounty->source_link }}" target="_blank" class="flex items-center justify-center gap-3 w-full py-4 bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition border border-zinc-700 group shadow-lg hover:-translate-y-1">
                                                        <div class="p-1.5 bg-white/10 rounded-full group-hover:bg-indigo-500 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                        </div>
                                                        Download Bahan / Video Asli
                                                    </a>
                                                    <p class="text-center text-[10px] text-zinc-600 mt-3">
                                                        Klik tombol di atas untuk mengunduh aset materi dari Brand.
                                                    </p>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>


                        {{-- ========================================================== --}}
                        {{--                    MODAL 3: FORM SUBMIT                    --}}
                        {{-- ========================================================== --}}
                        <template x-teleport="body">
                            <div x-show="modalSubmit" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                                <div @click="modalSubmit = false" class="fixed inset-0 bg-black/90 backdrop-blur-sm transition-opacity"></div>
                                
                                <div class="flex min-h-full items-center justify-center p-4 text-center"
                                     x-data="{ 
                                        step: 1, 
                                        inputName: '', 
                                        checks: { c1: false, c2: false, c3: false, c4: false, c5: false },
                                        isValid() {
                                            return this.inputName === '{{ auth()->user()->name ?? '' }}' && 
                                                   this.checks.c1 && this.checks.c2 && this.checks.c3 && this.checks.c4 && this.checks.c5;
                                        },
                                        copyCaption() {
                                            const captionText = document.getElementById('fullCaption').innerText;
                                            navigator.clipboard.writeText(captionText);
                                            alert('Caption berhasil disalin! Silakan paste di video Anda.');
                                        }
                                     }">
                                    
                                    {{-- Container Modal (Lebar disesuaikan agar muat form panjang) --}}
                                    <div x-show="modalSubmit" x-transition class="relative w-full max-w-2xl bg-[#0F0F0F] border border-zinc-800 rounded-2xl overflow-hidden shadow-2xl text-left">
                                        
                                        {{-- HEADER --}}
                                        <div class="px-6 py-4 border-b border-zinc-800 flex justify-between items-center bg-zinc-900/50">
                                            <h3 class="text-sm font-bold text-white">
                                                <span x-show="step === 1">Pernyataan & Persetujuan</span>
                                                <span x-show="step === 2">Upload Bukti Tayang</span>
                                            </h3>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors duration-300" :class="step === 1 ? 'bg-indigo-600 text-white' : 'bg-zinc-800 text-zinc-500'">1</span>
                                                <span class="w-4 h-px bg-zinc-700"></span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors duration-300" :class="step === 2 ? 'bg-indigo-600 text-white' : 'bg-zinc-800 text-zinc-500'">2</span>
                                            </div>
                                            <button @click="modalSubmit = false" class="text-zinc-500 hover:text-white ml-4"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                        </div>

                                        {{-- FORM WRAPPER (PENTING: enctype untuk upload file) --}}
                                        <form action="{{ route('submissions.store', $bounty->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf

                                            {{-- STEP 1: PERNYATAAN (Sama seperti sebelumnya) --}}
                                            <div x-show="step === 1" class="p-6 space-y-4">
                                                <div>
                                                    <label class="block text-[10px] text-zinc-400 uppercase font-bold mb-1">
                                                        Ketik nama lengkap Anda untuk konfirmasi: <span class="text-indigo-400">"{{ auth()->user()->name ?? 'Guest' }}"</span>
                                                    </label>
                                                    <input type="text" x-model="inputName" placeholder="Ketik nama anda..." class="w-full bg-black border border-zinc-700 rounded-lg px-3 py-2 text-white text-xs focus:ring-1 focus:ring-indigo-500 outline-none transition">
                                                </div>

                                                <div class="space-y-2 bg-zinc-900/30 p-3 rounded-xl border border-zinc-800/50">
                                                    <label class="flex items-start gap-3 cursor-pointer group">
                                                        <input type="checkbox" x-model="checks.c1" class="mt-0.5 w-4 h-4 rounded bg-zinc-800 border-zinc-600 text-indigo-600 focus:ring-0 cursor-pointer">
                                                        <span class="text-[11px] text-zinc-400 group-hover:text-zinc-300 leading-tight">Link video adalah <strong class="text-red-400">milik sendiri</strong>. Admin akan mengecek kepemilikan.</span>
                                                    </label>
                                                    <label class="flex items-start gap-3 cursor-pointer group">
                                                        <input type="checkbox" x-model="checks.c2" class="mt-0.5 w-4 h-4 rounded bg-zinc-800 border-zinc-600 text-indigo-600 focus:ring-0 cursor-pointer">
                                                        <span class="text-[11px] text-zinc-400 group-hover:text-zinc-300 leading-tight">Tidak menggunakan <strong class="text-white">AI Generator</strong> instan.</span>
                                                    </label>
                                                    <label class="flex items-start gap-3 cursor-pointer group">
                                                        <input type="checkbox" x-model="checks.c3" class="mt-0.5 w-4 h-4 rounded bg-zinc-800 border-zinc-600 text-indigo-600 focus:ring-0 cursor-pointer">
                                                        <span class="text-[11px] text-zinc-400 group-hover:text-zinc-300 leading-tight">Views 100% <strong class="text-green-400">ORGANIK</strong>. Tidak suntik bot/ads.</span>
                                                    </label>
                                                    <label class="flex items-start gap-3 cursor-pointer group">
                                                        <input type="checkbox" x-model="checks.c4" class="mt-0.5 w-4 h-4 rounded bg-zinc-800 border-zinc-600 text-indigo-600 focus:ring-0 cursor-pointer">
                                                        <span class="text-[11px] text-zinc-400 group-hover:text-zinc-300 leading-tight">View Count sudah menyentuh target <strong class="text-white">{{ number_format($bounty->min_views) }} views</strong>.</span>
                                                    </label>
                                                    <label class="flex items-start gap-3 cursor-pointer group">
                                                        <input type="checkbox" x-model="checks.c5" class="mt-0.5 w-4 h-4 rounded bg-zinc-800 border-zinc-600 text-indigo-600 focus:ring-0 cursor-pointer">
                                                        <span class="text-[11px] text-zinc-400 group-hover:text-zinc-300 leading-tight">Saya siap menerima sanksi <strong class="text-red-500">BANNED PERMANEN</strong> jika terbukti curang.</span>
                                                    </label>
                                                </div>

                                                <button type="button" @click="if(isValid()) step = 2" 
                                                    :disabled="!isValid()"
                                                    :class="isValid() ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-500/20' : 'bg-zinc-800 text-zinc-500 cursor-not-allowed'"
                                                    class="w-full py-3 rounded-xl font-bold text-xs uppercase tracking-widest transition flex items-center justify-center gap-2">
                                                    Lengkapi Persyaratan & Lanjut
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                                </button>
                                            </div>

                                            {{-- STEP 2: DETAIL FORM (SESUAI REQUEST BARU) --}}
                                            <div x-show="step === 2" class="p-6 h-[70vh] overflow-y-auto custom-scrollbar">
                                                
                                                {{-- 1. Platform & Warning --}}
                                                <div class="mb-6">
                                                    <label class="block text-[10px] text-zinc-400 uppercase font-bold mb-2">Platform Campaign</label>
                                                    <div class="flex gap-2 mb-3">
                                                        @foreach((array)$bounty->platform as $plat)
                                                            <div class="flex items-center gap-2 px-3 py-2 bg-zinc-900 border border-zinc-700 rounded-lg text-xs font-bold text-white capitalize">
                                                                {{ str_replace('_', ' ', $plat) }}
                                                                <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    {{-- Dummy Warning (Bisa diaktifkan logic-nya nanti) --}}
                                                    <div class="flex items-start gap-2 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                                                        <svg class="w-4 h-4 text-yellow-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                        <div>
                                                            <p class="text-[11px] text-yellow-200 font-bold">Pastikan akun sosmed sudah terhubung!</p>
                                                            <a href="{{ route('profile.edit') }}" class="text-[10px] text-yellow-500 hover:text-yellow-400 underline">Cek Profil Saya &rarr;</a>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- 2. Copy Caption --}}
                                                <div class="mb-6">
                                                    <label class="block text-[10px] text-zinc-400 uppercase font-bold mb-2 flex items-center gap-2">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                        Copy Caption Lengkap (Wajib)
                                                    </label>
                                                    <div class="bg-zinc-950 border border-zinc-800 rounded-xl overflow-hidden">
                                                        <div class="p-4 text-xs font-mono text-zinc-300 leading-relaxed break-words" id="fullCaption">
                                                            {{ '#' . ($bounty->required_hashtags ?? 'ClipSquad') }}<br>
                                                            {{ '@' . ($bounty->required_mentions ?? 'ClipSquad') }}<br><br>
                                                            {{-- Generator Kode Unik Dummy --}}
                                                            CS-{{ date('Y') }}-{{ strtoupper(substr(md5($bounty->id . auth()->id()), 0, 6)) }}
                                                        </div>
                                                        <button type="button" @click="copyCaption()" class="w-full py-2 bg-zinc-900 hover:bg-zinc-800 text-indigo-400 text-[10px] font-bold uppercase tracking-wider border-t border-zinc-800 transition">
                                                            Copy Semua untuk Caption
                                                        </button>
                                                    </div>
                                                    <p class="text-[10px] text-zinc-500 mt-2 flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        Paste caption ini di deskripsi video TikTok/Reels Anda.
                                                    </p>
                                                </div>

                                                {{-- 3. Form Input --}}
                                                <div class="space-y-4 mb-6">
                                                    {{-- Video URL --}}
                                                    <div>
                                                        <label class="block text-[10px] text-zinc-400 uppercase font-bold mb-1">Video URL *</label>
                                                        <input type="url" name="video_link" required placeholder="https://tiktok.com/..." class="w-full bg-black border border-zinc-700 rounded-lg px-3 py-2 text-white text-xs focus:ring-1 focus:ring-indigo-500 outline-none">
                                                    </div>
                                                    
                                                    {{-- Caption Used --}}
                                                    <div>
                                                        <label class="block text-[10px] text-zinc-400 uppercase font-bold mb-1">Caption Video Anda *</label>
                                                        <textarea name="caption_content" rows="2" placeholder="Paste caption yang Anda gunakan di video..." class="w-full bg-black border border-zinc-700 rounded-lg px-3 py-2 text-white text-xs focus:ring-1 focus:ring-indigo-500 outline-none"></textarea>
                                                    </div>

                                                    {{-- Current Views --}}
                                                    <div>
                                                        <label class="block text-[10px] text-zinc-400 uppercase font-bold mb-1">Jumlah Views Saat Ini *</label>
                                                        <div class="relative">
                                                            <input type="number" name="current_views" required placeholder="0" class="w-full bg-black border border-zinc-700 rounded-lg px-3 py-2 text-white text-xs focus:ring-1 focus:ring-indigo-500 outline-none">
                                                            <div class="absolute right-3 top-2 text-[10px] text-zinc-500">Views</div>
                                                        </div>
                                                        <p class="text-[9px] text-zinc-600 mt-1">*Admin akan memverifikasi manual. Pastikan sesuai.</p>
                                                    </div>
                                                </div>

                                                {{-- 4. Upload Analytics (Highlights) --}}
                                                <div class="mb-6 p-4 bg-zinc-900/50 border border-dashed border-zinc-700 rounded-xl hover:border-zinc-500 transition group cursor-pointer relative">
                                                    <input type="file" name="analytics_file" accept="video/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                                    
                                                    <div class="text-center">
                                                        <div class="w-10 h-10 bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-2 text-zinc-400 group-hover:text-white group-hover:scale-110 transition">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                        </div>
                                                        <h4 class="text-xs font-bold text-white mb-1">Upload Screen Recording Analytics Dashboard</h4>
                                                        <p class="text-[10px] text-zinc-500 mb-2">Format: MP4, MOV, ScreenRecord (Max 50MB)</p>
                                                        
                                                        <div class="inline-block px-3 py-1 bg-zinc-950 border border-zinc-800 rounded text-[9px] text-indigo-400 mb-3">
                                                            Lihat Tutorial Cara Record &rarr;
                                                        </div>

                                                        <div class="p-2 bg-red-900/20 border border-red-500/20 rounded text-[10px] text-red-300 font-bold leading-tight">
                                                            ⚠️ TOLONG UPLOAD VIDEO ANALYTICS SAJA.<br>JANGAN UPLOAD VIDEO CLIPPINGNYA!
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- 5. Checklist Final --}}
                                                <div class="text-left space-y-1 mb-6 border-l-2 border-zinc-700 pl-3">
                                                    <p class="text-[10px] text-zinc-400 font-bold uppercase mb-1">Before Submitting:</p>
                                                    <p class="text-[10px] text-zinc-500 flex items-center gap-1"><svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Pastikan Video sudah menggunakan hashtag yang diminta</p>
                                                    <p class="text-[10px] text-zinc-500 flex items-center gap-1"><svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Pastikan Video sudah mention akun yang diminta</p>
                                                    <p class="text-[10px] text-zinc-500 flex items-center gap-1"><svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Pastikan konten sesuai brief campaign</p>
                                                    <p class="text-[10px] text-zinc-500 flex items-center gap-1"><svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Video sudah di-publish dan bisa diakses publik</p>
                                                </div>

                                                {{-- Action Buttons --}}
                                                <div class="flex gap-2 border-t border-zinc-800 pt-4">
                                                    <button type="button" @click="step = 1" class="w-1/3 py-3 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold rounded-xl text-xs uppercase tracking-widest transition">
                                                        Kembali
                                                    </button>
                                                    <button type="submit" class="w-2/3 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition shadow-lg shadow-indigo-900/20 text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                                                        Kirim Submission 🚀
                                                    </button>
                                                </div>
                                            </div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>
                @endforeach
            </div>
            
            {{-- EMPTY STATE --}}
            @if ($bounties->isEmpty())
                <div class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-zinc-800 rounded-3xl mt-10">
                    <div class="w-20 h-20 bg-zinc-900 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Belum Ada Misi</h3>
                    <p class="text-zinc-500 text-sm max-w-sm">Para brand sedang menyiapkan kampanye seru. Cek lagi nanti ya!</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>