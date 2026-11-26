<nav x-data="{ open: false }"
    class="fixed w-full z-50 bg-gray-100 backdrop-blur-xl border-b border-white/10 transition-all duration-300">
    <div class="w-full px-4 sm:px-6 lg:px-10">
        {{-- 
        |--------------------------------------------------------------------------
        | CONTAINER UTAMA NAVBAR
        |--------------------------------------------------------------------------
        | Membungkus seluruh elemen navigasi (Logo, Menu Tengah, Menu Kanan).
        --}}
        <div class="relative flex justify-between items-center h-20">

            {{-- 
            |--------------------------------------------------------------------------
            | 1. LOGO BRAND (KIRI)
            |--------------------------------------------------------------------------
            --}}
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    {{-- Logo Image --}}
                    <img src="{{ asset('images/logo_black.png') }}" alt="Clipkeo"
                        class="w-20 h-20 object-contain drop-shadow-[0_0_15px_rgba(216,255,21,0.4)] group-hover:drop-shadow-[0_0_25px_rgba(216,255,21,0.8)] transition-all duration-500 group-hover:scale-110">
                </a>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | 2. MENU NAVIGASI TENGAH (DESKTOP)
            |--------------------------------------------------------------------------
            | Logika: Menampilkan menu berbeda berdasarkan Role (Clipper/Publisher/Admin).
            --}}
            <div class="hidden md:flex absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2">
                <div
                    class=" card-timbul-nav flex items-center space-x-1 bg-white/5 p-1.5 rounded-full border border-white/5 backdrop-blur-md">

                    {{-- A. MENU KHUSUS CLIPPER --}}
                    @if (Auth::user()->role === 'clipper')
                        <x-nav-link-custom :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                            Dashboard
                        </x-nav-link-custom>
                        <x-nav-link-custom :href="route('bounties.index')" :active="request()->routeIs('bounties.index')" icon="search">
                            Cari Misi
                        </x-nav-link-custom>
                        <x-nav-link-custom :href="route('wallet.index')" :active="request()->routeIs('wallet.index')" icon="wallet">
                            Dompet
                        </x-nav-link-custom>
                        <x-nav-link-custom :href="route('linked-accounts.index')" :active="request()->routeIs('linked-accounts.*')" icon="link">
                            Sosmed
                        </x-nav-link-custom>

                        {{-- B. MENU KHUSUS PUBLISHER --}}
                    @elseif(Auth::user()->role === 'publisher')
                        <x-nav-link-custom :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                            Dashboard
                        </x-nav-link-custom>
                        <x-nav-link-custom :href="route('analytics.overview')" :active="request()->routeIs('analytics.overview')" icon="activity">
                            Analytics
                        </x-nav-link-custom>
                        <x-nav-link-custom :href="route('bounties.create')" :active="request()->routeIs('bounties.create')" icon="plus">
                            Buat Campaign
                        </x-nav-link-custom>
                        <x-nav-link-custom :href="route('wallet.index')" :active="request()->routeIs('wallet.index')" icon="wallet">
                            Dompet
                        </x-nav-link-custom>

                        {{-- C. MENU KHUSUS ADMIN (UPDATE DI SINI) --}}
                    @elseif(Auth::user()->role === 'admin')
                        {{-- 1. Overview --}}
                        <x-nav-link-custom :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                            Overview
                        </x-nav-link-custom>

                        {{-- 2. Moderasi Campaign (NEW) --}}
                        {{-- Hitung jumlah Pending Review --}}
                        @php $pendingCount = \App\Models\Bounty::where('status', 'pending_review')->count(); @endphp

                        <x-nav-link-custom :href="route('admin.bounties.review')" :active="request()->routeIs('admin.bounties.*')" icon="shield">
                            Moderasi
                            {{-- Tampilkan Badge Merah jika ada pending --}}
                            @if ($pendingCount > 0)
                                <span
                                    class="ml-1.5 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full animate-pulse">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </x-nav-link-custom>

                        {{-- 3. Monitor --}}
                        <x-nav-link-custom :href="route('admin.monitor.index')" :active="request()->routeIs('admin.monitor.index')" icon="activity">
                            {{-- 1. Wrapper RELATIVE: Agar badge bisa nempel di pojok teks ini --}}
                            <div class="relative inline-flex items-center">

                                <span>Monitor</span>

                                {{-- Cek Apakah ada Antrian --}}
                                @if (isset($monitorCount) && $monitorCount > 0)
                                    {{-- 2. BADGE ABSOLUTE: Posisi di Ujung Kanan Atas (-top, -right) --}}
                                    <div class="absolute -top-1.5 -right-3 flex h-4 w-4 items-center justify-center">

                                        {{-- Layer 1: Animasi PING (Denyut) --}}
                                        {{-- Kita pakai warna RED standar biar pasti muncul --}}
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>

                                        {{-- Layer 2: Badge Angka (Gradient & Shadow) --}}
                                        <span
                                            class="relative inline-flex h-3 w-3 items-center justify-center rounded-full 
                             bg-gradient-to-r from-red-500 to-orange-500 
                             text-[8px] font-bold text-white 
                             shadow-md shadow-red-500/50 
                             ring-1 ring-white">
                                            {{ $monitorCount }}
                                        </span>

                                    </div>
                                @endif
                            </div>
                        </x-nav-link-custom>

                        {{-- 4. Keuangan --}}
                        <x-nav-link-custom :href="route('wallet.index')" :active="request()->routeIs('wallet.index')" icon="wallet">
                            Keuangan
                        </x-nav-link-custom>

                        {{-- 5. Users --}}
                        <x-nav-link-custom :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="users">
                            Users
                        </x-nav-link-custom>
                    @endif

                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | 3. MENU KANAN (NOTIFIKASI & PROFIL)
            |--------------------------------------------------------------------------
            --}}
            <div class="hidden sm:flex sm:items-center sm:ml-6">

                {{-- DROPDOWN NOTIFIKASI (NEUMORPH VERSION) --}}
                <div class="mr-6 relative" x-data="{ open: false }">
                    @php
                        $notifications = Auth::user()->unreadNotifications;
                        $unreadCount = $notifications->count();
                    @endphp

                    {{-- Trigger Button (Style: Timbul/Embossed) --}}
                    <button @click="open = !open"
                        class="relative p-3 text-gray-400 btn-neumorph-primary rounded-2xl group transition-all duration-300">

                        <svg class="w-4 h-4 group-hover:text-emerald-600 transition-colors duration-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>

                        {{-- Badge Notifikasi (Glow Effect) --}}
                        @if ($unreadCount > 0)
                            <div class="absolute -top-1 -right-1 flex items-center justify-center w-5 h-5">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span
                                    class="relative inline-flex h-4 w-4 items-center justify-center rounded-full 
                           bg-gradient-to-br from-rose-500 to-red-600 
                           text-[9px] font-black text-white 
                           shadow-lg shadow-rose-500/40 
                           ring-2 ring-[#f3f4f6]">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            </div>
                        @endif
                    </button>

                    {{-- Dropdown Panel (Style: Timbul Tinggi) --}}
                    <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        class="absolute right-0 mt-6 w-[380px] card-neumorphism border-none overflow-hidden z-[100] origin-top-right shadow-2xl">

                        {{-- Header Dropdown --}}
                        <div
                            class="px-6 py-5 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-[#f3f4f6]/95 backdrop-blur-md z-10">
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-xs font-black text-gray-800 uppercase tracking-[0.2em]">Notifikasi</span>
                                @if ($unreadCount > 0)
                                    <span
                                        class="px-2.5 py-0.5 rounded-full card-inset-panel text-rose-500 text-[9px] font-black tracking-widest">
                                        {{ $unreadCount }} BARU
                                    </span>
                                @endif
                            </div>

                            @if ($unreadCount > 0)
                                <a href="{{ route('notifications.readAll') }}"
                                    class="text-[9px] font-black text-gray-600 hover:text-gray-800 transition uppercase tracking-widest flex items-center gap-1.5 px-3 py-1.5 btn-neumorph-primary rounded-lg">
                                    <i class="fas fa-check-double text-[10px]"></i>
                                    Tandai Baca
                                </a>
                            @endif
                        </div>

                        {{-- List Notifikasi --}}
                        <div class="max-h-[420px] overflow-y-auto custom-scrollbar p-3">
                            @forelse($notifications as $notif)
                                <a href="{{ route('notifications.open', $notif->id) }}"
                                    class="block p-4 mb-2 rounded-[1.5rem] transition-all duration-300 group relative hover:card-inset-panel">

                                    <div class="flex gap-4 relative z-10">
                                        {{-- Icon Wrapper (Style: Inset) --}}
                                        <div class="shrink-0">
                                            @php
                                                $iconType = $notif->data['icon'] ?? '';
                                                $iconStyle = match ($iconType) {
                                                    'money' => 'text-emerald-500 bg-emerald-50',
                                                    'video' => 'text-indigo-500 bg-indigo-50',
                                                    'check-circle' => 'text-emerald-500 bg-emerald-50',
                                                    'x-circle' => 'text-rose-500 bg-rose-50',
                                                    'user' => 'text-blue-500 bg-blue-50',
                                                    'clock' => 'text-amber-500 bg-amber-50',
                                                    default => 'text-gray-400 bg-gray-50',
                                                };
                                            @endphp
                                            <div
                                                class="w-12 h-12 rounded-2xl card-timbul-nav flex items-center justify-center transition-transform duration-300 group-hover:scale-90">
                                                <div
                                                    class="w-8 h-8 rounded-xl {{ $iconStyle }} flex items-center justify-center shadow-inner">
                                                    {{-- Mapping Ikon (Sesuaikan dengan library lu, gue pakai Font Awesome) --}}
                                                    @if ($iconType == 'money')
                                                        <i class="fas fa-wallet text-sm"></i>
                                                    @elseif($iconType == 'video')
                                                        <i class="fas fa-play text-sm"></i>
                                                    @elseif(in_array($iconType, ['check-circle', 'check']))
                                                        <i class="fas fa-check-circle text-sm"></i>
                                                    @elseif(in_array($iconType, ['x-circle', 'failed']))
                                                        <i class="fas fa-times-circle text-sm"></i>
                                                    @else
                                                        <i class="fas fa-bell text-sm"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Konten Teks --}}
                                        <div class="flex-1">
                                            <h4
                                                class="text-xs font-black text-gray-700 tracking-tight leading-snug mb-1 group-hover:text-gray-600 transition-colors">
                                                {{ $notif->data['title'] ?? 'Info Baru' }}
                                            </h4>
                                            <p class="text-[10px] text-gray-400 font-bold leading-relaxed line-clamp-2">
                                                {{ $notif->data['message'] ?? '' }}
                                            </p>
                                            <div class="mt-2 flex items-center gap-2">
                                                <div class="w-1 h-1 rounded-full bg-gray-400"></div>
                                                <span
                                                    class="text-[9px] text-gray-400 font-black uppercase tracking-tighter italic">
                                                    {{ $notif->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="py-20 text-center flex flex-col items-center justify-center group">
                                    <div
                                        class="w-16 h-16 card-inset-panel flex items-center justify-center mb-6 text-gray-300">
                                        <i class="fas fa-bell-slash text-2xl"></i>
                                    </div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Semua
                                        notifikasi sudah dibaca</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                {{-- DROPDOWN PROFIL (NEUMORPH VERSION) --}}
                <x-dropdown align="right" width="44">
                    <x-slot name="trigger">
                        <button
                            class="flex items-center gap-4 px-2 py-2.5 rounded-[2rem] transition-all duration-300 group outline-none">

                            {{-- Info Nama & Role (Hidden di Mobile) --}}
                            <div class="hidden md:flex flex-col items-end">
                                <span
                                    class="text-xs font-black text-gray-700 group-hover:text-gray-600 transition-colors tracking-tighter">
                                    {{ Auth::user()->name }}
                                </span>

                                @php
                                    $roleClasses = match (Auth::user()->role) {
                                        'publisher' => 'text-emerald-500',
                                        'admin' => 'text-rose-500',
                                        default => 'text-emerald-500',
                                    };
                                @endphp
                                <span
                                    class="text-[8px] font-black uppercase tracking-[0.2em] {{ $roleClasses }} opacity-80">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>

                            {{-- Avatar Wrapper (Style: Inset) --}}
                            <div
                                class="relative p-1 card-inset-panel rounded-full overflow-hidden w-11 h-11 flex items-center justify-center">
                                <img class="w-9 h-9 rounded-full object-cover shadow-inner group-hover:scale-110 transition-transform duration-500"
                                    src="{{ Auth::user()->avatar_url ? (str_starts_with(Auth::user()->avatar_url, 'http') ? Auth::user()->avatar_url : asset('storage/' . Auth::user()->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff' }}"
                                    alt="{{ Auth::user()->name }}">

                                {{-- Status Online Indicator --}}
                                <span
                                    class="absolute bottom-1 right-1 w-2.5 h-2.5 bg-emerald-500 border-2 border-[#f3f4f6] rounded-full shadow-sm"></span>
                            </div>

                            {{-- Icon Arrow --}}
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-emerald-500 transition-transform duration-300"
                                :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Dropdown Body (Style: Timbul/Embossed) --}}
                        <div class="card-neumorphism border-none overflow-hidden w-72 p-3 shadow-2xl">

                            {{-- User Header (Inset Panel) --}}
                            <div
                                class="px-5 py-4 mb-3 card-inset-panel rounded-2xl flex flex-col items-center text-center">
                                <p class="text-[9px] text-gray-400 font-black uppercase tracking-[0.2em] mb-1">
                                    Terhubung Sebagai</p>
                                <p class="text-xs font-black text-gray-800 truncate w-full tracking-tight"
                                    title="{{ Auth::user()->email }}">
                                    {{ Auth::user()->email }}
                                </p>
                            </div>

                            <div class="space-y-1">
                                {{-- Link: Pengaturan --}}
                                <x-dropdown-link :href="route('profile.edit')"
                                    class="flex items-center gap-3 px-5 py-3.5 rounded-xl text-[11px] font-black tracking-widest text-gray-600 hover:card-inset-panel hover:!text-emerald-600 transition-all duration-200 group">
                                    <i
                                        class="fas fa-user-cog text-[13px] opacity-70 group-hover:scale-110 transition-transform"></i>
                                    Pengaturan Profil
                                </x-dropdown-link>

                                {{-- Link Dinamis: Finance --}}
                                @if (Auth::user()->role === 'publisher')
                                    <x-dropdown-link :href="route('deposits.create')"
                                        class="flex items-center gap-3 px-5 py-3.5 rounded-xl text-[11px] font-black tracking-widest text-gray-600 hover:card-inset-panel hover:!text-emerald-600 transition-all duration-200 group">
                                        <i class="fas fa-plus-circle text-[13px] opacity-70"></i>
                                        Isi Saldo
                                    </x-dropdown-link>
                                @elseif(Auth::user()->role === 'clipper')
                                    <x-dropdown-link :href="route('withdrawals.create')"
                                        class="flex items-center gap-3 px-5 py-3.5 rounded-xl text-[11px] font-black tracking-widest text-gray-600 hover:card-inset-panel hover:!text-emerald-600 transition-all duration-200 group">
                                        <i class="fas fa-hand-holding-usd text-[13px] opacity-70"></i>
                                        Tarik Dana
                                    </x-dropdown-link>
                                @endif

                                {{-- Separator Cekung --}}
                                <div class="h-[2px] card-inset-panel my-3 mx-2 opacity-50"></div>

                                {{-- Link: Logout --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="flex items-center gap-3 px-5 py-3.5 rounded-xl text-[11px] font-black tracking-widest text-rose-500 hover:card-inset-panel hover:!text-rose-600 transition-all duration-200 group w-full text-left">
                                        <i
                                            class="fas fa-power-off text-[13px] opacity-80 group-hover:rotate-90 transition-transform duration-500"></i>
                                        {{ __('Keluar (Log Out)') }}
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- HAMBURGER MENU (MOBILE) --}}
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-white/10 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- 
    |--------------------------------------------------------------------------
    | MOBILE MENU LIST (SMARTPHONE)
    |--------------------------------------------------------------------------
    --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden bg-[#0A0A0A] border-t border-white/10">
        <div class="pt-2 pb-3 space-y-1">

            {{-- A. MOBILE ADMIN (UPDATE DI SINI) --}}
            @if (Auth::user()->role === 'admin')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-red-500">{{ __('Overview') }}</x-responsive-nav-link>

                {{-- Menu Moderasi Mobile --}}
                @php $pendingBounties = \App\Models\Bounty::where('status', 'pending_review')->count(); @endphp
                <x-responsive-nav-link :href="route('admin.bounties.review')" :active="request()->routeIs('admin.bounties.*')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-red-500 flex justify-between items-center">
                    {{ __('Moderasi Campaign') }}
                    @if ($pendingBounties > 0)
                        <span
                            class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $pendingBounties }}</span>
                    @endif
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('wallet.index')" :active="request()->routeIs('wallet.index')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-red-500">{{ __('Keuangan') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white">{{ __('Users') }}</x-responsive-nav-link>

                {{-- B. MOBILE CLIPPER --}}
            @elseif(Auth::user()->role === 'clipper')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-orange-500">{{ __('Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('wallet.index')" :active="request()->routeIs('wallet.index')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-orange-500">{{ __('Dompet & Transaksi') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('bounties.index')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white">{{ __('Cari Misi') }}</x-responsive-nav-link>

                {{-- C. MOBILE PUBLISHER --}}
            @elseif(Auth::user()->role === 'publisher')
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-blue-500">{{ __('Dashboard') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('analytics.overview')" :active="request()->routeIs('analytics.overview')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white border-blue-500">{{ __('Analytics') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('bounties.create')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white">{{ __('Buat Sayembara') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('wallet.index')" :active="request()->routeIs('wallet.index')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white">{{ __('Dompet & Transaksi') }}</x-responsive-nav-link>
            @endif
        </div>

        {{-- Mobile Profile --}}
        <div class="pt-4 pb-1 border-t border-white/10">
            <div class="px-4 flex items-center gap-3">
                <div class="shrink-0"><img class="w-8 h-8 rounded-full border border-white/10 object-cover"
                        src="{{ Auth::user()->avatar_url ? (str_starts_with(Auth::user()->avatar_url, 'http') ? Auth::user()->avatar_url : asset('storage/' . Auth::user()->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random&color=fff' }}"
                        alt="{{ Auth::user()->name }}"></div>
                <div>
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')"
                    class="text-gray-300 hover:bg-white/5 hover:text-white">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="text-red-400 hover:bg-white/5 hover:text-red-300">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
