<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    {{-- SweetAlert tetap diload untuk kebutuhan lain (misal konfirmasi delete), tapi tidak untuk flash message --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>

<body class="is-dashboard antialiased custom-scrollbar text-gray-900">

    {{-- 
    |--------------------------------------------------------------------------
    | BAGIAN 1: ALERT / FLASH MESSAGE (ANIMATED FIXED)
    |--------------------------------------------------------------------------
    | Update:
    | 1. show: false (Mulai sembunyi dulu).
    | 2. setTimeout show = true (Munculkan setelah 100ms agar animasi jalan).
    | 3. setTimeout show = false (Hilang otomatis setelah 5 detik).
    --}}
    <div class="fixed top-24 right-4 sm:right-8 z-[110] w-full max-w-[350px] space-y-4 pointer-events-none">

    {{-- =========================== --}}
    {{--    NOTIFIKASI SUKSES        --}}
    {{-- =========================== --}}
    @if (session('success'))
        <div x-data="{ show: false }" 
            x-init="setTimeout(() => show = true, 100); setTimeout(() => show = false, 5000)" 
            x-show="show"
            x-transition:enter="transform ease-out duration-500 transition"
            x-transition:enter-start="translate-x-full opacity-0" 
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform ease-in duration-300 transition"
            x-transition:leave-start="translate-x-0 opacity-100" 
            x-transition:leave-end="translate-x-full opacity-0"
            style="display: none;"
            class="pointer-events-auto relative card-timbul p-5 rounded-[2rem] border-none flex items-start gap-4 group transition-all duration-300 bg-[#f3f4f6]">

            {{-- Ikon Success Inset --}}
            <div class="shrink-0">
                <div class="w-10 h-10 card-timbul flex items-center justify-center rounded-xl text-emerald-500 group-hover:scale-110 transition duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            {{-- Teks --}}
            <div class="flex-1 pt-0.5">
                <h4 class="text-[11px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-2">Berhasil</h4>
                <p class="text-[10px] text-gray-500 font-black uppercase tracking-tight leading-relaxed">
                    {{ session('success') }}
                </p>
            </div>

            {{-- Tombol Close --}}
            <button @click="show = false" class="text-gray-400 hover:text-rose-500 transition -mt-1 -mr-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    @endif

    {{-- =========================== --}}
    {{--    NOTIFIKASI ERROR         --}}
    {{-- =========================== --}}
    @if (session('error'))
        <div x-data="{ show: false }" 
            x-init="setTimeout(() => show = true, 100); setTimeout(() => show = false, 8000)" 
            x-show="show"
            x-transition:enter="transform ease-out duration-500 transition"
            x-transition:enter-start="translate-x-full opacity-0" 
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform ease-in duration-300 transition"
            x-transition:leave-start="translate-x-0 opacity-100" 
            x-transition:leave-end="translate-x-full opacity-0"
            style="display: none;"
            class="pointer-events-auto relative card-timbul p-5 rounded-[2rem] border-none flex items-start gap-4 group transition-all duration-300 bg-[#f3f4f6]">

            {{-- Ikon Error Inset --}}
            <div class="shrink-0">
                <div class="w-10 h-10 card-timbul flex items-center justify-center rounded-xl text-rose-500 group-hover:scale-110 transition duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

            {{-- Teks --}}
            <div class="flex-1 pt-0.5">
                <h4 class="text-[11px] font-black text-rose-600 uppercase tracking-widest leading-none mb-2">Aksi Ditolak</h4>
                <p class="text-[10px] text-gray-500 font-black uppercase tracking-tight leading-relaxed">
                    {{ session('error') }}
                </p>
            </div>

            {{-- Tombol Close --}}
            <button @click="show = false" class="text-gray-400 hover:text-rose-500 transition -mt-1 -mr-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
    @endif
</div>


    <div class="min-h-screen flex flex-col bg-gray-100">

        {{-- 
        |--------------------------------------------------------------------------
        | BAGIAN 2: NAVIGATION BAR
        |--------------------------------------------------------------------------
        --}}
        @include('layouts.navigation')

        {{-- 
        |--------------------------------------------------------------------------
        | BAGIAN 3: PAGE HEADER
        |--------------------------------------------------------------------------
        --}}
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        {{-- 
        |--------------------------------------------------------------------------
        | BAGIAN 4: PAGE CONTENT (MAIN SLOT)
        |--------------------------------------------------------------------------
        --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>

        {{-- 
        |--------------------------------------------------------------------------
        | BAGIAN 5: FOOTER (DARK THEME + GIANT WATERMARK)
        |--------------------------------------------------------------------------
        --}}
        <footer class="bg-[#09090b] border-t border-white/10 mt-12 relative overflow-hidden">
            <div
                class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full text-center pointer-events-none select-none z-0">
                <h1 class="text-[15vw] font-black text-white/[0.02] leading-none tracking-tighter">CLIPKEO</h1>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo.png') }}" alt="Clipkeo Logo"
                                class="w-20 h-20 object-contain drop-shadow-[0_0_15px_rgba(216,255,21,0.5)]">
                        </div>
                        <p class="text-gray-400 text-sm max-w-xs leading-relaxed font-light">
                            Platform terbaik untuk menghubungkan Brand dan Clipper kreatif untuk konten viral di seluruh
                            media sosial.
                        </p>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase text-xs tracking-widest">Layanan</h4>
                        <ul class="space-y-2 text-sm text-gray-500">
                            <li><a href="{{ route('bounties.index') }}" class="hover:text-indigo-400 transition">Cari
                                    Misi</a></li>
                            <li><a href="{{ route('pages.about') }}" class="hover:text-indigo-400 transition">Tentang
                                    Kami</a></li>
                            <li><a href="#" class="hover:text-indigo-400 transition">Gabung Discord</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-white font-bold mb-4 uppercase text-xs tracking-widest">Bantuan</h4>
                        <ul class="space-y-2 text-sm text-gray-500">
                            <li><a href="{{ route('pages.faq') }}" class="hover:text-indigo-400 transition">FAQ</a>
                            </li>
                            <li><a href="{{ route('pages.privacy') }}"
                                    class="hover:text-indigo-400 transition">Kebijakan Privasi</a></li>
                            <li><a href="{{ route('pages.contact') }}"
                                    class="hover:text-indigo-400 transition">Hubungi Kami</a></li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-12 pt-8 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-gray-500 text-xs">&copy; 2026 Clipkeo. Semua Hak Dilindungi.</p>
                    <div class="flex gap-6 text-gray-500 italic text-xs">
                        <span>Secure & Encrypted Transaction</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    {{-- 
    |--------------------------------------------------------------------------
    | BAGIAN 6: JAVASCRIPT GLOBAL
    |--------------------------------------------------------------------------
    --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // A. Notifikasi VALIDASI FORM (Gaya Neumorph)
            @if ($errors->any())
                let errorMsg = '<div class="space-y-2 mt-4 text-left">';
                @foreach ($errors->all() as $error)
                    errorMsg += `
                    <div class="flex items-center gap-3 p-3 card-inset-panel rounded-xl border-none">
                        <div class="w-2 h-2 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.6)] shrink-0"></div>
                        <span class="text-[10px] font-black text-gray-600 uppercase tracking-wide">{{ $error }}</span>
                    </div>`;
                @endforeach
                errorMsg += '</div>';

                Swal.fire({
                    icon: 'warning',
                    title: '<span class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 italic">Security Check</span><br><span class="text-xl font-black uppercase tracking-tighter text-gray-800 italic">Periksa Inputan</span>',
                    html: errorMsg,
                    background: '#f3f4f6', // Warna dasar dashboard Lu
                    showConfirmButton: true,
                    confirmButtonText: 'SAYA PERBAIKI 🛠️',
                    buttonsStyling: false, // Kita pakai class sendiri
                    customClass: {
                        popup: 'card-neumorphism rounded-[3rem] border-none p-10 shadow-2xl',
                        confirmButton: 'btn-neumorph-primary px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600 hover:scale-105 transition-all outline-none mt-6'
                    },
                    showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut animate__faster'
                    }
                });
            @endif
        });
    </script>

    {{-- 
    |--------------------------------------------------------------------------
    | BAGIAN 7: FLOATING DISCORD POPUP
    |--------------------------------------------------------------------------
    --}}
    @if (Auth::check() && Auth::user()->role === 'clipper')
        <div x-data="{
            showDiscord: false,
            init() { setTimeout(() => this.showDiscord = true, 1500); },
            closePopup() { this.showDiscord = false; }
        }" x-show="showDiscord" x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-20 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-20 scale-95"
            class="fixed bottom-8 right-8 z-[100] w-full max-w-[360px]" style="display: none;">

            {{-- Container Utama: Menggunakan card-neumorphism --}}
            <div class="card-neumorphism relative rounded-[2.5rem] border-none overflow-hidden group bg-[#f3f4f6]">

                {{-- Tetap Pertahankan Glow Warna (Elemen Warna Tidak Dirubah) --}}
                <div
                    class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/30 blur-[60px] rounded-full pointer-events-none">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-24 h-24 bg-purple-500/40 blur-[50px] rounded-full pointer-events-none">
                </div>

                {{-- Button Close --}}
                <button @click="closePopup()"
                    class="absolute top-5 right-5 text-gray-400 hover:text-rose-500 transition-colors z-20">
                    <i class="fas fa-times text-sm"></i>
                </button>

                <div class="p-8 relative z-10">
                    {{-- Header Section --}}
                    <div class="flex items-center gap-5 mb-6">
                        {{-- Icon Discord dalam Frame Inset --}}
                        <div
                            class="w-14 h-14 card-inset-panel flex items-center justify-center rounded-2xl bg-[#5865F2] text-indigo-500 shadow-lg shadow-indigo-200/50 transform group-hover:rotate-6 transition duration-500 shrink-0">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19.27 5.33C17.94 4.71 16.5 4.26 15 4a.09.09 0 0 0-.07.03c-.18.33-.39.76-.53 1.09a16.09 16.09 0 0 0-4.8 0c-.14-.34-.35-.76-.54-1.09c-.01-.02-.04-.03-.07-.03c-1.5.26-2.93.71-4.27 1.33c-.01 0-.02.01-.03.02c-2.72 4.07-3.47 8.03-3.1 11.95c0 .02.01.04.03.05c1.8 1.32 3.53 2.12 5.24 2.65c.03.01.06 0 .07-.02c.4-.55.76-1.13 1.07-1.74c.02-.04 0-.08-.04-.09c-.57-.22-1.11-.48-1.64-.78c-.04-.02-.04-.08.01-.11c.11-.08.22-.17.33-.25c.02-.02.05-.02.07-.01c3.44 1.57 7.15 1.57 10.55 0c.02-.01.05-.01.07.01c.11.09.22.17.33.26c.04.03.04.09-.01.11c-.52.31-1.07.56-1.64.78c-.04.01-.05.06-.04.09c.32.61.68 1.19 1.07 1.74c.03.01.06.02.09.01c1.72-.53 3.45-1.33 5.25-2.65c.02-.01.03-.03.03-.05c.44-4.53-.73-8.46-3.1-11.95c-.01-.01-.02-.02-.04-.02zM8.52 14.91c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.84 2.12-1.89 2.12zm6.97 0c-1.03 0-1.89-.95-1.89-2.12s.84-2.12 1.89-2.12c1.06 0 1.9.96 1.89 2.12c0 1.17-.84 2.12-1.89 2.12z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-gray-900 font-black text-xl tracking-tighter leading-none">ClipSquad Elite
                            </h3>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                <p class="text-[10px] text-indigo-500 font-black uppercase tracking-widest">Online
                                    Community</p>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-gray-500 mb-6 leading-relaxed font-bold tracking-wide">
                        Dapatkan info <span class="text-indigo-600 italic">campaign mahal</span> & tips FYP eksklusif.
                        Gabung circle clipper pro sekarang! 🔥
                    </p>

                    <div class="space-y-4">
                        {{-- Button Gabung: Tetap Biru Discord tapi Gaya Neumorph --}}
                        <a href="https://discord.gg/LINK_INVITE_KAMU" target="_blank"
                            class="flex items-center justify-center gap-3 w-full bg-[#5865F2] text-white text-[10px] font-black uppercase tracking-[0.2em] py-4 rounded-2xl transition-all hover:scale-[1.02] active:scale-95 shadow-lg shadow-indigo-200/50">
                            <span>Gabung Discord</span>
                            <i class="fas fa-external-link-alt text-[10px]"></i>
                        </a>

                        <a href="{{ route('profile.edit') }}" class="block w-full text-center group/link">
                            <p
                                class="text-[9px] text-gray-400 font-black uppercase tracking-widest transition-colors group-hover/link:text-gray-600">
                                Sudah join? <span
                                    class="text-indigo-600 italic underline decoration-2 underline-offset-4">Verifikasi
                                    di sini</span>
                            </p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

</body>

</html>
