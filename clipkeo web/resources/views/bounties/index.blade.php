<x-app-layout>
    <div class="pt-32 pb-12 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: HERO HEADER (Light Mode)
            |--------------------------------------------------------------------------
            --}}
            <div class="text-center mb-16 relative">
                {{-- Efek Glow Halus Background --}}
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[100px] bg-emerald-200/40 blur-[80px] rounded-full pointer-events-none">
                </div>

                {{-- Headline Utama --}}
                <h1 class="relative text-4xl md:text-6xl font-black text-gray-900 tracking-tight mb-4 leading-tight">
                    Bikin Konten, <br class="md:hidden" />
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-pink-600 to-purple-600">
                        Panen Cuan.
                    </span>
                </h1>
                <p class="relative text-gray-500 text-sm md:text-base max-w-xl mx-auto leading-relaxed">
                    Gak perlu nunggu jadi artis. Cukup upload video kreatifmu dan menangkan hadiahnya.
                </p><br>

                {{-- SEARCH BAR --}}
                <div class="relative w-full max-w-4xl mx-auto flex items-center gap-4 z-20">
                    <form action="{{ route('bounties.index') }}" method="GET" class="relative flex-1 group">

                        {{-- Ikon Search --}}
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-500 group-focus-within:text-gray-600 transition"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>

                        {{-- Input Field Cekung --}}
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari brand atau misi..."
                            class="input-neumorph-inset w-full text-gray-800 text-sm rounded-full pl-12 pr-4 py-3.5 placeholder-gray-500">

                        <button type="submit" class="hidden"></button>
                    </form>

                    {{-- Tombol Buat Misi (Gunakan style Pop-out yang tadi) --}}
                    @auth
                        @if (auth()->user()->role === 'publisher')
                            <a href="{{ route('bounties.create') }}"
                                class="btn-neumorph-primary whitespace-nowrap px-6 py-3.5 text-sm font-bold rounded-2xl flex items-center gap-2 transform transition-all active:scale-95">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="hidden sm:inline">Buat Misi</span>
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: FILTER BUTTONS (UPDATED - KATEGORI CAMPAIGN)
            |--------------------------------------------------------------------------
            --}}
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-end gap-4 border-b border-gray-200 pb-4">

                {{-- Container Tombol dengan Scroll Horizontal --}}
                <div class="w-full overflow-x-auto custom-scrollbar pb-4">
                    <div class="flex gap-4 px-2">

                        {{-- Tombol SEMUA --}}
                        <a href="{{ route('bounties.index') }}"
                            class="whitespace-nowrap px-5 py-2 rounded-full text-xs font-bold transition-all
            {{ !request('category') ? 'btn-cat-active' : 'btn-cat-neumorph hover:text-indigo-600' }}">
                            Semua
                        </a>

                        {{-- Ambil kategori yang HANYA ada di database --}}
                        @php
                            $activeCategories = \App\Models\Bounty::whereNotNull('category')
                                ->distinct()
                                ->pluck('category');
                        @endphp

                        @foreach ($activeCategories as $cat)
                            <a href="{{ route('bounties.index', ['category' => $cat]) }}"
                                class="whitespace-nowrap px-5 py-2 rounded-full text-xs font-bold transition-all
                {{ request('category') == $cat ? 'btn-cat-active' : 'btn-cat-neumorph hover:text-indigo-600' }}">
                                {{ $cat }}
                            </a>
                        @endforeach

                    </div>
                </div>

                {{-- Total Count --}}
                <span class="text-xs text-gray-500 font-medium mb-2 whitespace-nowrap hidden sm:block">
                    {{ $bounties->count() }} Misi Aktif
                </span>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: GRID CARD (BOUNTY LIST)
            |--------------------------------------------------------------------------
            --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                @foreach ($bounties as $bounty)
                    @php
                        // Logic Status & Progress Budget
                        $isFinished = $bounty->status === 'completed' || $bounty->remaining_budget < 100; // Hard Cap Rp 100

                        $percentage = 0;
                        if ($bounty->reward_amount > 0) {
                            $percentage = ($bounty->remaining_budget / $bounty->reward_amount) * 100;
                        }

                        $displayMinViews = $bounty->min_views;

                        if (
                            $bounty->distribution_strategy === 'performance_tiered' &&
                            !empty($bounty->rules['tiers'])
                        ) {
                            // Collect array tiers, lalu ambil nilai minimal dari key 'target_views'
                            $displayMinViews = collect($bounty->rules['tiers'])->min('target_views') ?? 0;
                        }

                        // Config Badge berdasarkan Strategi Baru
                        $badgeConfig = match ($bounty->distribution_strategy) {
                            'cpm' => [
                                'icon' => '💰',
                                'text' => 'CPM / PPV',
                                'bg' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            ],
                            'performance_tiered' => [
                                'icon' => '🎯',
                                'text' => 'TIERED',
                                'bg' => 'bg-amber-100 text-amber-700 border-amber-200',
                            ],
                            default => [
                                'icon' => '📦',
                                'text' => 'OTHER',
                                'bg' => 'bg-gray-100 text-gray-700 border-gray-200',
                            ],
                        };

                        $minViewsToClaim = 0;

                                if (strtolower($bounty->distribution_strategy) === 'cpm') {
                                    // Jika CPM, ambil langsung dari kolom min_views
                                    $minViewsToClaim = $bounty->min_views;
                                } else {
                                    // Jika Tiered, ambil target_views paling kecil dari JSON tiers
                                    $tiers = $bounty->rules['tiers'] ?? [];
                                    if (!empty($tiers)) {
                                        $minViewsToClaim = collect($tiers)->min('target_views');
                                    }
                                }
                    @endphp

                    <div x-data="{ modalTier: false, modalInst: false, modalSubmit: false }" class="group relative flex flex-col h-full">

                        {{-- CARD BOX --}}
                        <div
                            class="card-neumorphism overflow-hidden relative transform transition-all duration-300 cursor-default group {{ $isFinished }}">

                            {{-- 1. COVER IMAGE --}}
                            <div class="relative h-48 overflow-hidden bg-gray-200">
                                @if ($bounty->image_path)
                                    <img src="{{ asset('storage/' . $bounty->image_path) }}"
                                        class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                                @else
                                    <img src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?q=80&w=1000&auto=format&fit=crop"
                                        class="w-full h-full object-cover opacity-80">
                                @endif

                                {{-- Gradient Overlay --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-white/10 via-transparent to-transparent">
                                </div>

                                {{-- BADGES (TIER & KATEGORI) --}}
                                <div class="absolute top-4 left-4 flex flex-col gap-2 items-start">
                                    {{-- [BARU] Badge Kategori --}}
                                    <div
                                        class="px-3 py-1 rounded-lg bg-gray-900/80 backdrop-blur-md text-white border border-gray-700 text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                        {{ $bounty->category ?? 'Miscellaneous' }}
                                    </div>
                                </div>

                                {{-- Badge Status (Live/Selesai) --}}
                                <div class="absolute top-4 right-4">
                                    @if ($isFinished)
                                        <span
                                            class="bg-gray-800 border border-gray-600 text-white text-[10px] font-black px-3 py-1.5 rounded-xl">SELESAI</span>
                                    @else
                                        <span
                                            class="bg-white border border-red-100 text-green-600 text-[10px] font-black px-3 py-1.5 rounded-xl flex items-center gap-1.5 shadow-sm animate-pulse">
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Active
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- 2. CARD BODY --}}
                            <div class="p-6 flex flex-col flex-1">
                                <h3
                                    class="text-xl font-bold text-gray-900 leading-tight mb-4 line-clamp-2 group-hover:text-emerald-600 transition">
                                    {{ $bounty->title }}
                                </h3>
                                <div class="flex flex-col gap-3 mb-6">
                                    <label class="block text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">
                                        Platform Target
                                    </label>

                                    <div class="flex flex-wrap gap-3">
                                        @foreach ((array) ($bounty->platform ?? []) as $plat)
                                            @php
                                                $p = strtolower(trim($plat));
                                                $shortName = explode('_', $plat)[0];
                                            @endphp

                                            <div
                                                class="card-timbul flex items-center gap-2.5 px-4 py-2 rounded-xl border-none">

                                                {{-- Logika Pencarian Kata (Bukan Perbandingan Presisi) --}}
                                                @if (str_contains($p, 'instagram'))
                                                    <i class="fab fa-instagram text-pink-500 text-xs"></i>
                                                @elseif(str_contains($p, 'tiktok'))
                                                    <i class="fab fa-tiktok text-gray-800 text-xs"></i>
                                                @elseif(str_contains($p, 'youtube'))
                                                    <i class="fab fa-youtube text-red-600 text-xs"></i>
                                                @else
                                                    <i class="fas fa-globe text-indigo-500 text-xs"></i>
                                                @endif

                                                <span
                                                    class="text-[10px] font-black text-gray-700 uppercase tracking-widest">
                                                    {{ $shortName }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                {{-- REWARD BAR --}}
                                <div
                                    class="card-inset-panel p-4 mb-6 mt-auto relative transition-colors group-hover:border-indigo-100">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p
                                                class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">
                                                Reward Detail
                                            </p>
                                            <button @click="modalTier = true"
                                                class="text-sm font-bold text-emerald-600 hover:text-emerald-800 underline decoration-emerald-200 underline-offset-4 transition">
                                                Lihat Tier
                                            </button>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">
                                                Min. Views
                                            </p>
                                            <div class="flex items-center justify-end gap-1">
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                                <p class="text-sm font-black text-gray-800">
                                                    {{ number_format($displayMinViews) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="w-full h-px bg-gray-300/50 mb-4"></div>

                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <span
                                                class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Sisa
                                                Budget</span>
                                            <span class="text-[10px] font-bold text-gray-700">
                                                {{ number_format($percentage, 0) }}%
                                                <span class="text-gray-500 font-normal">dari Rp
                                                    {{ number_format($bounty->reward_amount) }}</span>
                                            </span>
                                        </div>

                                        <div class="progress-track w-full rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-gradient-to-r from-emerald-400 to-green-300 h-full rounded-full transition-all duration-1000 ease-out"
                                                style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TOMBOL AKSI UTAMA --}}
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- TRIGGER MODAL INSTRUKSI --}}
                                    <button @click="modalInst = true"
                                        class="btn-neumorph-primary w-full py-3 rounded-2xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>

                                        <span>Instruksi</span>
                                    </button>

                                    @if (!$isFinished)
                                        {{-- TRIGGER MODAL SUBMIT --}}
                                        <button type="button" x-data data-bounty="{{ json_encode($bounty) }}"
                                            {{-- TAMBAHKAN console.log DI BAWAH INI --}}
                                            @click="
        let data = JSON.parse($el.dataset.bounty);
        let minViews = data.rules && data.rules.tiers && data.rules.tiers.length > 0 
                       ? parseInt(data.rules.tiers[0].target_views) 
                       : parseInt(data.min_views || 0);
        $dispatch('set-bounty-data', { 
            id: data.id, 
            min: minViews, // <-- Sekarang nilainya pasti akurat!
            detail: data 
        })"
                                            class="btn-neumorph-primary w-full py-3 rounded-2xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">
                                            Submit
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    @else
                                        <button disabled
                                            class="py-3 rounded-xl bg-gray-100 text-gray-400 font-bold text-xs uppercase tracking-wider cursor-not-allowed">Ditutup</button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | MODAL 1: TIER REWARD / CPM DETAIL (UPDATED) 🚀
                        |--------------------------------------------------------------------------
                        | Ini adalah bagian yang direquest untuk diupdate sesuai kategori baru.
                        | Menangani tampilan untuk 'cpm' dan 'performance_tiered'.
                        --}}
                        <template x-teleport="body">
                            <div x-show="modalTier" class="fixed inset-0 z-[100] overflow-y-auto"
                                style="display: none;">
                                {{-- Overlay dengan Blur yang lebih kuat agar Neumorphism menonjol --}}
                                <div @click="modalTier = false"
                                    class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity"></div>

                                <div class="flex min-h-full items-center justify-center p-4">
                                    {{-- Box Modal Utama --}}
                                    <div x-show="modalTier" x-transition
                                        class="card-neumorphism relative w-full max-w-lg overflow-hidden p-8 text-center border-none">

                                        {{-- Close Button dengan gaya Neumorph Kecil --}}
                                        <button @click="modalTier = false"
                                            class="btn-neumorph-primary absolute top-4 right-4 w-10 h-10 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>

                                        <h3 class="text-xl font-black text-gray-800 mb-2">Skema Hadiah</h3>
                                        <p class="text-xs text-gray-500 mb-8 px-4 leading-relaxed">
                                            @if ($bounty->distribution_strategy === 'cpm')
                                                Misi ini menggunakan sistem <strong>CPM (Pay Per View)</strong>.
                                                Kamu akan dibayar untuk setiap 1 view yang kamu dapatkan secara
                                                akumulatif.
                                            @elseif($bounty->distribution_strategy === 'performance_tiered')
                                                Misi ini menggunakan sistem <strong>Performance Tiered</strong>.
                                                Reward dibayarkan secara bertahap saat videomu mencapai target views
                                                tertentu.
                                            @endif
                                        </p>

                                        {{-- PANEL CPM --}}
                                        @if ($bounty->distribution_strategy === 'cpm')
                                            <div class="card-inset-panel p-6 mb-8">
                                                <div class="grid grid-cols-2 gap-4 text-left">
                                                    <div>
                                                        <p
                                                            class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">
                                                            Rate Per View</p>
                                                        <p class="text-2xl font-black text-gray-800">Rp
                                                            {{ number_format($bounty->rate_per_view, 2, ',', '.') }}
                                                        </p>
                                                        <p class="text-[10px] text-gray-400">Per 1 penonton</p>
                                                    </div>
                                                    <div class="border-l border-gray-300 pl-4">
                                                        <p
                                                            class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">
                                                            Min. Claim</p>
                                                        <p class="text-2xl font-black text-emerald-600">
                                                            {{ number_format($bounty->min_views) }}</p>
                                                        <p class="text-[10px] text-gray-400">Total Views Akumulasi</p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- PANEL TIERED --}}
                                        @elseif($bounty->distribution_strategy === 'performance_tiered')
                                            <div class="card-inset-panel overflow-hidden text-left mb-8">
                                                <div
                                                    class="p-4 bg-gray-200/50 border-b border-gray-300 flex justify-between items-center">
                                                    <span class="text-xs font-bold text-gray-600 uppercase">Target
                                                        Views</span>
                                                    <span
                                                        class="text-xs font-bold text-gray-600 uppercase">Hadiah</span>
                                                </div>
                                                <div class="divide-y divide-gray-300">
                                                    @php
                                                        $tiers = $bounty->rules['tiers'] ?? [];
                                                        usort(
                                                            $tiers,
                                                            fn($a, $b) => $a['target_views'] <=> $b['target_views'],
                                                        );
                                                    @endphp
                                                    @foreach ($tiers as $index => $tier)
                                                        <div
                                                            class="p-4 flex justify-between items-center hover:bg-gray-200/30 transition">
                                                            <div class="flex items-center gap-3">
                                                                <span
                                                                    class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold shadow-md">
                                                                    {{ $index + 1 }}
                                                                </span>
                                                                <span class="text-sm font-bold text-gray-700">
                                                                    {{ number_format($tier['target_views']) }} <span
                                                                        class="text-xs text-gray-400 font-normal">Views</span>
                                                                </span>
                                                            </div>
                                                            <span class="text-sm font-black text-emerald-600">Rp
                                                                {{ number_format($tier['reward']) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Footer Note --}}
                                        <div class="card-inset-panel p-4 text-left flex gap-3 items-start opacity-80">
                                            <svg class="w-5 h-5 text-gray-400 shrink-0 mt-0.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                            <p class="text-xs text-gray-500 leading-snug">
                                                <strong class="text-gray-700 block mb-1">Catatan:</strong>
                                                Payout final ditentukan berdasarkan verifikasi views oleh publisher.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>


                        {{-- 
                        |--------------------------------------------------------------------------
                        | MODAL 2: INSTRUKSI & ASET
                        |--------------------------------------------------------------------------
                        --}}
                        <template x-teleport="body">
                            <div x-show="modalInst" class="fixed inset-0 z-[100] overflow-y-auto"
                                style="display: none;">
                                {{-- Overlay dengan Blur Mewah --}}
                                <div @click="modalInst = false"
                                    class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity"></div>

                                <div class="flex min-h-full items-center justify-center p-4">
                                    <div x-show="modalInst" x-transition
                                        class="card-neumorphism relative w-full max-w-2xl overflow-hidden border-none shadow-none p-0">

                                        {{-- HEADER GAMBAR (Bungkus dengan Card Inset agar Terlihat Tertanam) --}}
                                        <div class="h-64 relative group bg-[#f3f4f6]">
                                            @if ($bounty->image_path)
                                                <img src="{{ asset('storage/' . $bounty->image_path) }}"
                                                    class="w-full h-full object-cover transition duration-700">
                                            @else
                                                <div
                                                    class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                            @endif

                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-transparent to-transparent">
                                            </div>

                                            {{-- Tombol Close Neumorph --}}
                                            <button @click="modalInst = false"
                                                class="btn-neumorph-primary absolute top-4 right-4 z-50 w-10 h-10 rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5 text-gray-800" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>

                                            <div class="absolute bottom-6 left-8 right-8">
                                                <h3
                                                    class="text-3xl font-black text-white leading-tight drop-shadow-lg">
                                                    {{ $bounty->title }}
                                                </h3>
                                            </div>
                                        </div>

                                        <div class="p-8 space-y-8">
                                            {{-- INSTRUKSI (Gunakan Card Inset Panel) --}}
                                            <div>
                                                <h4
                                                    class="text-xs font-bold text-gray-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                    Instruksi Lengkap
                                                </h4>
                                                <div
                                                    class="card-inset-panel text-gray-600 text-sm leading-relaxed p-6 max-h-64 overflow-y-auto custom-scrollbar">
                                                    {!! nl2br(e($bounty->description)) !!}
                                                </div>
                                            </div>

                                            {{-- GRID INFO (Kecil-kecil Cabe Rawit) --}}
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                                <div class="card-inset-panel p-4 flex flex-col justify-center">
                                                    <span
                                                        class="text-[10px] font-bold text-gray-600 uppercase mb-1 flex items-center gap-1">
                                                        <svg class="w-3 h-3 text-emerald-600" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                            </path>
                                                        </svg>
                                                        Deadline
                                                    </span>
                                                    <span
                                                        class="text-gray-800 font-bold text-sm">{{ \Carbon\Carbon::parse($bounty->deadline_at)->format('d M Y') }}</span>
                                                </div>

                                                <div class="card-inset-panel p-4">
                                                    <span
                                                        class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Hashtag</span>
                                                    <span
                                                        class="text-emerald-600 font-bold text-sm">{{ $bounty->required_hashtags ?? '-' }}</span>
                                                </div>

                                                <div class="card-inset-panel p-4">
                                                    <span
                                                        class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Mention</span>
                                                    <span
                                                        class="text-emerald-600 font-bold text-sm">{{ $bounty->required_mentions ?? '-' }}</span>
                                                </div>
                                            </div>

                                            {{-- DOWNLOAD SECTION --}}
                                            @if ($bounty->source_link)
                                                <div class="pt-6 border-t border-gray-300">
                                                    <a href="{{ $bounty->source_link }}" target="_blank"
                                                        class="btn-neumorph-primary inline-flex items-center rounded-2xl w-full justify-center gap-3 px-6 py-2.5 text-[11px] font-black uppercase tracking-widest">

                                                        {{-- Container Icon Lebih Kecil --}}
                                                        <div
                                                            class="flex items-center justify-center w-8 h-8 bg-emerald-100 rounded-full text-emerald-600 shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5"
                                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                                </path>
                                                            </svg>
                                                        </div>

                                                        <span>Download Materi</span>
                                                    </a>
                                                    <p
                                                        class="text-center text-[10px] text-gray-400 mt-4 italic font-medium">
                                                        Gunakan materi resmi untuk menghindari klaim hak cipta.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @endforeach
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: EMPTY STATE
            |--------------------------------------------------------------------------
            --}}
            @if ($bounties->isEmpty())
                <div
                    class="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-gray-200 rounded-[2rem] mt-10 bg-white">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Misi</h3>
                    <p class="text-gray-500 text-sm max-w-sm">Para brand sedang menyiapkan kampanye seru. Cek lagi
                        nanti ya!</p>
                </div>
            @endif
        </div>
    </div>
    <x-modal-submission :mySocials="$mySocials ?? collect()" />
</x-app-layout>
