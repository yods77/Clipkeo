<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ClipSquad') }} - The Future of Viral Marketing</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #050505;
            /* Hitam Premium (Bukan #000) */
            color: #ffffff;
            overflow-x: hidden;
        }

        /* PREMIUM GRID BACKGROUND */
        .premium-bg {
            background-size: 50px 50px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center top, black 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(circle at center top, black 40%, transparent 100%);
        }

        /* SPOTLIGHT EFFECT (UPDATED GREEN) */
        /* Cahaya hijau neon di bagian atas tengah */
        .spotlight {
            position: absolute;
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 800px;
            /* Update: Merah -> Hijau (#d8ff15 = 216, 255, 21) */
            background: radial-gradient(circle, rgba(216, 255, 21, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
        }

        /* GLASS CARD */
        .glass-card {
            background: rgba(20, 20, 20, 0.6);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.2);
        }

        /* HOVER GLOW PADA CARD (UPDATED GREEN) */
        .glass-card:hover {
            /* Update: Border & Shadow jadi Hijau Neon */
            border-color: rgba(216, 255, 21, 0.3);
            box-shadow: 0 0 30px rgba(216, 255, 21, 0.1);
        }

        /* TEXT GRADIENT (UPDATED GREEN) */
        .animated-text-gradient {
            /* Update: Gradient Merah-Orange -> Hijau Neon (#d8ff15) & Hijau Lime (#a3d900) */
            background: linear-gradient(to right, #fff, #d8ff15, #a3d900, #fff);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientMove 5s linear infinite;
        }

        @keyframes gradientMove {
            to {
                background-position: 200% center;
            }
        }

        /* MARQUEE */
        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
            position: relative;
        }

        .marquee-content {
            display: inline-block;
            animation: scroll 30s linear infinite;
        }

        @keyframes scroll {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        .stroke-text {
            -webkit-text-stroke: 1px rgba(255, 255, 255, 0.2);
            color: transparent;
        }

        /* ANIMASI SCROLL INFINITE */
        @keyframes scrollDown {
            0% {
                transform: translateY(-50%);
            }

            100% {
                transform: translateY(0);
            }
        }

        @keyframes scrollUp {
            0% {
                transform: translateY(0);
            }

            100% {
                transform: translateY(-50%);
            }
        }

        .animate-scroll-down {
            animation: scrollDown 40s linear infinite;
        }

        .animate-scroll-up {
            animation: scrollUp 40s linear infinite;
        }

        /* MASKING GRADIENT */
        .mask-gradient-y {
            mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
        }
    </style>
</head>

<body class="selection:bg-red-500 selection:text-white" x-data="{
    activeModal: '{{ request()->query('session_expired') == 'true'
        ? 'login'
        : (session('status')
            ? 'forgot'
            : (old('name') || $errors->has('name') || $errors->has('password_confirmation')
                ? 'register'
                : ($errors->any()
                    ? 'login'
                    : null))) }}'
}">

    <div class="fixed inset-0 z-0 pointer-events-none premium-bg opacity-50"></div>
    <div class="fixed inset-0 z-0 pointer-events-none spotlight"></div>
    <nav class="fixed w-full z-40 top-0 transition-all duration-300" x-data="{ scrolled: false }"
        @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div :class="scrolled ? 'bg-[#050505]/80 backdrop-blur-xl border-b border-white/5' : 'bg-transparent'"
            class="transition-all duration-300">
            <div class="max-w-7xl mx-auto px-6 h-24 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    {{-- LOGO IMAGE --}}
                    {{-- Pastikan file ada di folder: public/images/logo.png --}}
                    <img src="{{ asset('images/logo.png') }}" alt="Clipkeo Logo"
                        class="w-20 h-20 object-contain drop-shadow-[0_0_15px_rgba(216,255,21,0.5)]">
                </div>

                <div class="hidden md:flex items-center gap-8">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}"
                                class="text-sm font-medium text-gray-400 hover:text-white transition">Dashboard</a>
                        @else
                            <button @click="activeModal = 'login'"
                                class="text-sm font-medium text-gray-400 hover:text-white transition">Masuk</button>
                            @if (Route::has('register'))
                                <a href="{{ route('auth.google') }}"
                                    class="group relative px-5 py-2 bg-white text-black text-sm font-bold rounded-full overflow-hidden transition-all hover:scale-105 flex items-center justify-center gap-3">

                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M23.766 12.2764C23.766 11.4607 23.6999 10.6406 23.5588 9.83807H12.24V14.4591H18.7217C18.4528 15.9494 17.5885 17.2678 16.323 18.1056V21.1039H20.19C22.4608 19.0139 23.766 15.9274 23.766 12.2764Z"
                                            fill="#4285F4" />
                                        <path
                                            d="M12.2401 24.0008C15.4766 24.0008 18.2059 22.9382 20.1945 21.1039L16.3275 18.1055C15.2517 18.8375 13.8627 19.252 12.2445 19.252C9.11388 19.252 6.45946 17.1399 5.50705 14.3003H1.5166V17.3912C3.55371 21.4434 7.7029 24.0008 12.2401 24.0008Z"
                                            fill="#34A853" />
                                        <path
                                            d="M5.50253 14.3003C5.00236 12.8099 5.00236 11.1961 5.50253 9.70575V6.61481H1.51649C-0.18551 10.0056 -0.18551 14.0004 1.51649 17.3912L5.50253 14.3003Z"
                                            fill="#FBBC05" />
                                        <path
                                            d="M12.2401 4.74966C13.9509 4.7232 15.6044 5.36697 16.8434 6.54867L20.2695 3.12262C18.1001 1.0855 15.2208 -0.034466 12.2401 0.000808666C7.7029 0.000808666 3.55371 2.55822 1.5166 6.61481L5.50264 9.70575C6.45064 6.86173 9.10947 4.74966 12.2401 4.74966Z"
                                            fill="#EA4335" />
                                    </svg>

                                    <span>Sign up with Google</span>
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <section
        class="relative z-10 min-h-screen flex flex-col justify-center items-center text-center px-6 pt-32 pb-20 overflow-hidden">

        {{-- BADGE --}}
        <div class="mb-8" data-aos="fade-down" data-aos-duration="1000">
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 backdrop-blur-md shadow-2xl">
                <span class="relative flex h-2 w-2">
                    {{-- Badge tetap hijau standard, karena cocok dengan tema Neon --}}
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                <span class="text-xs font-semibold tracking-widest text-gray-400 uppercase">The #1 Creator
                    Ecosystem</span>
            </div>
        </div>

        {{-- HEADLINE --}}
        <h1 class="text-6xl md:text-8xl lg:text-9xl font-extrabold tracking-tighter mb-8 leading-none drop-shadow-2xl"
            data-aos="fade-up" data-aos-duration="1200">
            Make Content. <br>
            {{-- Class ini menggunakan custom CSS .animated-text-gradient yang sudah kita update sebelumnya --}}
            <span class="animated-text-gradient">Get Paid.</span>
        </h1>

        {{-- SUB-HEADLINE --}}
        <p class="text-lg md:text-md text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed font-light"
            data-aos="fade-up" data-aos-delay="200">
            Platform <strong class="text-white">Endorsement 2.0</strong> untuk Brand Lokal & Kreator. Tingkatkan <i
                class="text-white">Brand Value</i>
            dengan budget minimal, biarkan ribuan Clipper bekerja serentak demi viralitas produk Anda.
        </p>

        {{-- TOMBOL UTAMA --}}
        <div class="flex flex-col sm:flex-row gap-5 items-center w-full justify-center mb-16" data-aos="fade-up"
            data-aos-delay="400">

            {{-- [UPDATE] Tombol pakai class 'bg-neon' yang baru kita buat --}}
            <button @click="activeModal = 'register'"
                class="relative w-full sm:w-auto px-10 py-4 bg-neon hover:bg-neon-dark rounded-full font-bold text-black text-lg overflow-hidden transition-all hover:scale-105 shadow-[0_10px_40px_-10px_rgba(216,255,21,0.5)] border border-transparent hover:border-neon/50">
                <span class="relative z-10">Start Earning</span>
            </button>

            <a href="#demo"
                class="w-full sm:w-auto px-10 py-4 glass-card rounded-full font-medium text-white hover:bg-white/5 transition text-lg">
                View Statistics
            </a>
        </div>

        {{-- === SLIDER GAMBAR START === --}}
        <div class="w-full max-w-7xl mx-auto relative mb-12" data-aos="fade-up" data-aos-delay="600">

            {{-- [UPDATE] Glow Slider pakai class 'bg-neon/5' --}}
            <div class="absolute inset-0 bg-neon/5 blur-3xl pointer-events-none rounded-full"></div>

            <div
                class="relative w-full h-[500px] mask-gradient-y overflow-hidden border-t border-b border-white/5 bg-white/[0.02]">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 w-full px-4 transform -skew-y-0">

                    {{-- KOLOM 1 (Turun) --}}
                    <div class="relative w-full -mt-20">
                        <div class="animate-scroll-down flex flex-col gap-4">
                            <img src="{{ asset('images/scroll/1_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/2_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/3_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/1_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/2_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/3_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                        </div>
                    </div>

                    {{-- KOLOM 2 (Naik) --}}
                    <div class="relative w-full hidden md:block -mt-10">
                        <div class="animate-scroll-up flex flex-col gap-4">
                            <img src="{{ asset('images/scroll/4_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/5_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/6_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/4_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/5_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/6_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                        </div>
                    </div>

                    {{-- KOLOM 3 (Turun) --}}
                    <div class="relative w-full -mt-20">
                        <div class="animate-scroll-down flex flex-col gap-4" style="animation-duration: 35s;">
                            <img src="{{ asset('images/scroll/7_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/8_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/9_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/7_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/8_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/9_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                        </div>
                    </div>

                    {{-- KOLOM 4 (Naik) --}}
                    <div class="relative w-full hidden md:block -mt-10">
                        <div class="animate-scroll-up flex flex-col gap-4" style="animation-duration: 45s;">
                            <img src="{{ asset('images/scroll/10_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/11_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/12_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/10_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/11_TikTok.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                            <img src="{{ asset('images/scroll/12_Instagram.jpg') }}"
                                class="w-full rounded-lg opacity-60 hover:opacity-100 transition duration-500 border border-white/10">
                        </div>
                    </div>

                </div>
            </div>

            <p class="text-[10px] text-center text-gray-500 mt-4 uppercase tracking-widest opacity-50">Powered by
                Clipkeo Community</p>
        </div>
        {{-- === SLIDER GAMBAR END === --}}

        {{-- SCROLL DOWN ARROW --}}
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 animate-bounce opacity-30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3">
                </path>
            </svg>
        </div>
    </section>

    <div class="relative z-20 py-10 bg-black border-y border-white/5 backdrop-blur-sm">
        <div class="marquee-container">
            <div class="marquee-content opacity-50">
                <span class="text-5xl font-black stroke-text px-10">VIRAL</span>
                <span class="text-5xl font-black text-white px-10">MONETIZE</span>
                <span class="text-5xl font-black stroke-text px-10">SCALE</span>
                <span class="text-5xl font-black text-white px-10">CREATORS</span>
                <span class="text-5xl font-black stroke-text px-10">BOUNTY</span>
                <span class="text-5xl font-black text-white px-10">PAYOUT</span>
                <span class="text-5xl font-black stroke-text px-10">VIRAL</span>
                <span class="text-5xl font-black text-white px-10">MONETIZE</span>
                <span class="text-5xl font-black stroke-text px-10">SCALE</span>
                <span class="text-5xl font-black text-white px-10">CREATORS</span>
                <span class="text-5xl font-black stroke-text px-10">BOUNTY</span>
                <span class="text-5xl font-black text-white px-10">PAYOUT</span>
            </div>
        </div>
    </div>

    <section class="relative z-10 py-24 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- CARD 1: TOTAL DISBURSED (UPDATED TO NEON) --}}
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group hover:border-neon/50 transition-all duration-500"
                    data-aos="fade-up" data-aos-delay="0">

                    {{-- Background Icon Decoration --}}
                    <div
                        class="absolute -right-6 -bottom-6 text-neon/10 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.15-1.46-3.27-3.4h1.96c.1 1.05 1.18 1.91 2.53 1.91 1.29 0 2.13-.59 2.13-1.66 0-.85-.31-1.34-1.85-1.77l-.96-.28c-1.83-.48-3.05-1.46-3.05-3.51 0-2.09 1.49-3.32 3.44-3.66V4h2.67v1.93c1.5.25 2.91 1.26 3.11 3h-1.97c-.21-.92-1.12-1.63-2.31-1.63-1.04 0-1.91.43-1.91 1.5 0 .86.51 1.28 2.05 1.74l.96.26c1.9.57 3.03 1.55 3.03 3.63.02 1.95-1.35 3.25-3.35 3.63z">
                            </path>
                        </svg>
                    </div>

                    {{-- Gradient Overlay --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-neon/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            {{-- Icon Box Kecil --}}
                            <div class="p-2 bg-neon/10 rounded-lg border border-neon/20 text-neon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>

                            {{-- Badge Persentase (Tetap Hijau Standard karena cocok) --}}
                            <span
                                class="flex items-center gap-1 text-[10px] font-bold text-green-400 bg-green-900/20 px-2 py-1 rounded-full border border-green-500/20">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                </svg>
                                +12.5% Month
                            </span>
                        </div>

                        <h3
                            class="text-5xl font-black text-white tracking-tight mb-1 group-hover:scale-105 transition-transform origin-left">
                            {{ number_format($totalPayout) }}
                        </h3>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">IDR Total Disbursed
                        </p>

                        {{-- Progress Bar --}}
                        <div class="w-full bg-gray-800 h-1 rounded-full overflow-hidden">
                            <div class="bg-neon h-full w-[75%] rounded-full animate-pulse"></div>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2 text-right">Target Q1: 75% Achieved</p>
                    </div>
                </div>

                {{-- CARD 2: VERIFIED CLIPPERS (ORANGE - TETAP) --}}
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group hover:border-orange-500/50 transition-all duration-500"
                    data-aos="fade-up" data-aos-delay="100">

                    <div
                        class="absolute -right-6 -bottom-6 text-orange-600/10 transform -rotate-12 group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z">
                            </path>
                        </svg>
                    </div>

                    <div
                        class="absolute inset-0 bg-gradient-to-br from-orange-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-orange-500/10 rounded-lg border border-orange-500/20 text-orange-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                            </div>

                            <div class="flex -space-x-2 overflow-hidden py-1 pl-1">
                                @if (isset($recentClippers) && count($recentClippers) > 0)
                                    @foreach ($recentClippers as $clipper)
                                        @php
                                            $hasPhoto = !empty($clipper->avatar_url);
                                            $photoLink = $hasPhoto
                                                ? asset('storage/' . $clipper->avatar_url)
                                                : 'https://ui-avatars.com/api/?name=' .
                                                    urlencode($clipper->name) .
                                                    '&background=random&color=fff&size=64';
                                        @endphp
                                        <img class="inline-block h-8 w-8 rounded-full ring-2 ring-[#0A0A0A] object-cover bg-gray-800"
                                            src="{{ $photoLink }}" alt="{{ $clipper->name }}"
                                            title="{{ $clipper->name }}"
                                            onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($clipper->name) }}&background=red&color=fff';">
                                    @endforeach
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-800 ring-2 ring-[#0A0A0A]"></div>
                                @endif

                                @if (isset($totalClipper) && $totalClipper > 4)
                                    <div
                                        class="w-8 h-8 rounded-full bg-orange-600 ring-2 ring-[#0A0A0A] flex items-center justify-center z-10 relative">
                                        <span class="text-[10px] font-bold text-white leading-none">
                                            +{{ $totalClipper > 99 ? '99' : $totalClipper - 4 }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h3
                            class="text-5xl font-black text-white tracking-tight mb-1 group-hover:scale-105 transition-transform origin-left">
                            {{ number_format($totalClipper ?? 0) }}
                        </h3>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Verified Clippers</p>

                        <div class="flex items-center gap-2 mt-4">
                            <span class="relative flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                            </span>
                            <p class="text-[10px] text-orange-400">Users online now</p>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: ACTIVE CAMPAIGNS (PURPLE - TETAP) --}}
                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group hover:border-purple-500/50 transition-all duration-500"
                    data-aos="fade-up" data-aos-delay="200">
                    <div
                        class="absolute -right-6 -bottom-6 text-purple-600/10 -rotate-12 group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.62 0-2.99 1.28-3 2.9V4.91C11.99 3.29 10.62 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.1 0-2 .9-2 2v2c0 .55.45 1 1 1h1v8c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2v-8h1c.55 0 1-.45 1-1V8c0-1.1-.9-2-2-2ZM9 4c.55 0 1 .45 1 1s-.45 1-1 1h-1.18c-.46 0-.82-.45-.82-1s.36-1 .82-1H9Zm5 2h-1.18c-.46 0-.82-.45-.82-1s.36-1 .82-1H15c.55 0 1 .45 1 1s-.45 1-1 1ZM7 10h4v8H7v-8Zm10 8h-4v-8h4v8Zm3-10H4v-2h16v2Z" />
                        </svg>
                    </div>

                    <div
                        class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-2 bg-purple-500/10 rounded-lg border border-purple-500/20 text-purple-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                    </path>
                                </svg>
                            </div>
                            <span
                                class="text-[10px] font-bold text-white bg-gradient-to-r from-purple-600 to-pink-600 px-3 py-1 rounded-full shadow-lg shadow-purple-900/50">
                                Hadiah Terbesar 🔥
                            </span>
                        </div>

                        <h3
                            class="text-4xl font-black text-white tracking-tight mb-1 group-hover:scale-105 transition-transform origin-left">
                            Rp {{ number_format($topPrizePool, 0, ',', '.') }}
                        </h3>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Total Reward from</p>

                        <h3
                            class="text-4xl font-black text-white tracking-tight mb-1 group-hover:scale-105 transition-transform origin-left">
                            {{ $activeBounties }} <span
                                class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Campaigns</span>
                        </h3>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ======================================================================= --}}
    {{-- SECTION: DOWNLOAD APP (ON-THE-GO CONTEXT)                               --}}
    {{-- ======================================================================= --}}
    <section class="relative py-24 md:py-32 overflow-hidden border-t border-white/5">

        {{-- Background Glow Effects --}}
        <div
            class="absolute top-1/2 left-0 -translate-y-1/2 w-[500px] h-[500px] bg-neon/5 blur-[120px] rounded-full pointer-events-none">
        </div>
        <div
            class="absolute top-1/2 right-0 -translate-y-1/2 w-[400px] h-[400px] bg-blue-600/5 blur-[100px] rounded-full pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-8 items-center">

                {{-- KOLOM KIRI: TEKS & TOMBOL DOWNLOAD --}}
                <div data-aos="fade-right" data-aos-duration="1000">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-neon/10 border border-neon/20 mb-8">
                        <span class="w-2 h-2 rounded-full bg-neon animate-pulse"></span>
                        <span class="text-[10px] font-black text-neon uppercase tracking-widest">Aplikasi Mobile
                            Resmi</span>
                    </div>

                    <h2
                        class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight mb-6">
                        Cuan Kapan Aja, <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-emerald-400">Dari Mana
                            Aja.</span>
                    </h2>

                    <p class="text-gray-400 text-lg leading-relaxed mb-10 max-w-lg">
                        Tarik penghasilan, pantau status submission, dan dapatkan notifikasi <span
                            class="text-gray-200 font-bold">Bounty terbaru</span> langsung dari saku kamu. Rasakan
                        pengalaman yang lebih cepat dan responsif dengan aplikasi Clipkeo.
                    </p>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        {{-- Tombol Google Play (Custom Style) --}}
                        <a href="#"
                            class="relative inline-block transition-all duration-300 transform hover:-translate-y-1 hover:scale-105 hover:drop-shadow-[0_0_20px_rgba(57,255,20,0.3)]">
                            {{-- Ganti src di bawah ini dengan link gambar kamu --}}
                            <img src="https://res.cloudinary.com/df5cppjg8/image/upload/v1772512942/googleplay_urwvam.png"
                                alt="Get it on Google Play" class="h-16 w-auto object-contain">
                        </a>

                        {{-- Info Rating / Download --}}
                        <div class="flex items-center gap-3">
                            <div class="flex -space-x-2">
                                <img class="w-8 h-8 rounded-full border-2 border-[#050505]"
                                    src="https://i.pravatar.cc/100?img=1" alt="User">
                                <img class="w-8 h-8 rounded-full border-2 border-[#050505]"
                                    src="https://i.pravatar.cc/100?img=2" alt="User">
                                <img class="w-8 h-8 rounded-full border-2 border-[#050505]"
                                    src="https://i.pravatar.cc/100?img=3" alt="User">
                            </div>
                            <div class="text-xs">
                                <div class="flex text-yellow-400 text-[10px] mb-0.5">
                                    ★★★★★
                                </div>
                                <span class="font-bold text-gray-300">4.9/5</span> <span
                                    class="text-gray-500">Rating</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: MOCKUP APLIKASI --}}
                <div class="relative" data-aos="fade-left" data-aos-duration="1200" data-aos-delay="200">
                    {{-- Lingkaran Dekorasi Belakang --}}
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] border border-neon/20 rounded-full animate-[spin_10s_linear_infinite]">
                    </div>
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] border border-dashed border-white/5 rounded-full animate-[spin_15s_linear_infinite_reverse]">
                    </div>

                    {{-- Mockup HP (Bisa menggunakan gambar transparan .png) --}}
                    <div
                        class="relative z-10 mx-auto w-[400px] sm:w-[300px] lg:w-[350px] transform hover:scale-105 transition-transform duration-700 ease-out">
                        <img src="https://res.cloudinary.com/df5cppjg8/image/upload/v1774240486/App_Launch_Instagram_Post_in_Slate_Grey_Yellow_Cool_Corporate_Style_3_uigqkf.png"
                            alt="Clipkeo App Mockup"
                            class="w-full h-auto drop-shadow-[0_30px_50px_rgba(57,255,20,0.2)]">
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="relative z-10 py-24 px-6 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            {{-- KOLOM KIRI: TEKS PENJELASAN --}}
            <div data-aos="fade-right">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-neon/30 bg-neon/10 mb-6">
                    <span class="w-2 h-2 rounded-full bg-neon animate-pulse"></span>
                    <span class="text-[10px] font-bold text-neon uppercase tracking-widest">Peluang Karir 2026</span>
                </div>

                <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight text-white">
                    Apa itu
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-emerald-500">
                        Clipper?
                    </span>
                </h2>

                <p class="text-lg text-gray-400 leading-relaxed mb-10">
                    Clipper adalah <strong>Arsitek Konten Viral</strong>. Tugasmu bukan syuting video dari nol, melainkan menemukan "Momen Emas" dari video berdurasi panjang dan mengubahnya menjadi cuplikan vertikal yang siap FYP.
                </p>

                <div class="space-y-8">
                    {{-- Step 1 --}}
                    <div class="flex gap-5 group">
                        <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center flex-shrink-0 border border-white/10 group-hover:border-neon/50 group-hover:bg-neon/10 transition-all duration-300 shadow-[0_0_0_rgba(57,255,20,0)] group-hover:shadow-[0_0_20px_rgba(57,255,20,0.2)]">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-neon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white mb-1 group-hover:text-neon transition-colors">1. Tonton & Temukan</h4>
                            <p class="text-sm text-gray-500">Tonton video podcast atau livestream, cari 30-60 detik bagian yang paling lucu, sedih, atau kontroversial.</p>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex gap-5 group">
                        <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center flex-shrink-0 border border-white/10 group-hover:border-neon/50 group-hover:bg-neon/10 transition-all duration-300 shadow-[0_0_0_rgba(57,255,20,0)] group-hover:shadow-[0_0_20px_rgba(57,255,20,0.2)]">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-neon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm8.486-.486a5 5 0 01-7.072 0m0-7.072a5 5 0 017.072 0M5 5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white mb-1 group-hover:text-neon transition-colors">2. Potong & Percantik</h4>
                            <p class="text-sm text-gray-500">Edit menjadi format vertikal. Tambahkan subtitle dinamis (hook) dan efek suara agar penonton betah melihatnya.</p>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex gap-5 group">
                        <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center flex-shrink-0 border border-white/10 group-hover:border-neon/50 group-hover:bg-neon/10 transition-all duration-300 shadow-[0_0_0_rgba(57,255,20,0)] group-hover:shadow-[0_0_20px_rgba(57,255,20,0.2)]">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-neon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-white mb-1 group-hover:text-neon transition-colors">3. Viralkan & Hasilkan Uang</h4>
                            <p class="text-sm text-gray-500">Upload klip tersebut ke TikTok, Reels, atau Shorts. Dapatkan bayaran dari setiap views yang berhasil kamu kumpulkan.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: VISUALISASI WORKFLOW --}}
            <div class="relative w-full max-w-lg mx-auto" data-aos="fade-left">
                
                {{-- Glow Belakang --}}
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-neon/10 rounded-full blur-[80px] pointer-events-none"></div>

                <div class="relative z-10 flex flex-col items-center gap-6">
                    
                    {{-- TAHAP 1: VIDEO PANJANG (RAW) --}}
                    <div class="w-full card-neumorphism border border-white/10 rounded-2xl p-4 shadow-xl transform hover:-translate-y-2 transition-transform duration-300">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-white">Podcast Episode #124</h5>
                                <p class="text-[10px] text-gray-400">Durasi: 2 Jam 15 Menit</p>
                            </div>
                        </div>
                        <div class="w-full h-2 bg-gray-800 rounded-full overflow-hidden">
                            <div class="w-full h-full bg-gray-600"></div>
                        </div>
                    </div>

                    {{-- ANIMASI GUNTING / PROSES CUTTING --}}
                    <div class="flex flex-col items-center animate-[pulse_2s_ease-in-out_infinite]">
                        <div class="w-[2px] h-8 bg-gradient-to-b from-transparent to-neon"></div>
                        <div class="w-8 h-8 rounded-full bg-neon/20 border border-neon flex items-center justify-center -my-2 z-10">
                            <svg class="w-4 h-4 text-neon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm8.486-.486a5 5 0 01-7.072 0m0-7.072a5 5 0 017.072 0M5 5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="w-[2px] h-8 bg-gradient-to-b from-neon to-transparent"></div>
                    </div>

                    {{-- TAHAP 2: HASIL KLIP PENDEK (VIRAL SHORTS) --}}
                    <div class="flex justify-center gap-4 w-full">
                        
                        {{-- Klip 1 --}}
                        <div class="w-24 h-40 bg-[#0a0a0a] border border-white/10 rounded-xl relative overflow-hidden transform -rotate-6 hover:rotate-0 hover:scale-110 transition-all duration-300 shadow-[0_10px_20px_rgba(0,0,0,0.5)] flex flex-col justify-end p-2">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent z-10"></div>
                            <div class="relative z-20 text-center">
                                <p class="text-[10px] font-bold text-white mb-0.5">Part 1</p>
                                <div class="bg-neon/20 border border-neon/50 rounded text-[9px] text-neon font-black py-0.5 px-1">1.2M Views</div>
                            </div>
                        </div>

                        {{-- Klip 2 (Tengah, lebih besar/fokus) --}}
                        <div class="w-28 h-48 bg-[#0a0a0a] border border-neon/30 rounded-xl relative overflow-hidden transform hover:scale-110 transition-all duration-300 shadow-[0_0_20px_rgba(57,255,20,0.15)] flex flex-col justify-end p-2 z-10 -mt-4">
                            <div class="absolute inset-0 bg-gradient-to-t from-neon/40 via-transparent to-transparent z-10"></div>
                            <div class="relative z-20 text-center">
                                <p class="text-xs font-bold text-white mb-1">Highlight!</p>
                                <div class="bg-neon text-black rounded text-[10px] font-black py-1 px-2 shadow-[0_0_10px_rgba(57,255,20,0.5)]">3.5M Views</div>
                            </div>
                        </div>

                        {{-- Klip 3 --}}
                        <div class="w-24 h-40 bg-[#0a0a0a] border border-white/10 rounded-xl relative overflow-hidden transform rotate-6 hover:rotate-0 hover:scale-110 transition-all duration-300 shadow-[0_10px_20px_rgba(0,0,0,0.5)] flex flex-col justify-end p-2">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent z-10"></div>
                            <div class="relative z-20 text-center">
                                <p class="text-[10px] font-bold text-white mb-0.5">Part 2</p>
                                <div class="bg-neon/20 border border-neon/50 rounded text-[9px] text-neon font-black py-0.5 px-1">800K Views</div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

    <section id="demo" class="relative z-10 py-20 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="mb-20 text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight" data-aos="fade-up">Ecosystem <span
                        class="text-neon">Intelligence.</span></h2>
                <p class="text-gray-400" data-aos="fade-up" data-aos-delay="100">Teknologi di balik platform
                    crowdsourcing tercepat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 grid-rows-2 gap-6 h-auto md:h-[600px]">

                {{-- CARD 1: LIGHTNING FAST PAYOUTS (UPDATED TO NEON) --}}
                <div class="md:col-span-2 md:row-span-2 glass-card rounded-3xl p-8 relative overflow-hidden group flex flex-col justify-between"
                    data-aos="fade-right">

                    {{-- Gradient Hover --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-neon/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    {{-- Logo S dollar Besar --}}
                    <div
                        class="absolute -right-10 -bottom-10 opacity-10 transform rotate-12 group-hover:scale-125 transition-transform duration-500 pointer-events-none">
                        <svg class="w-64 h-64 text-neon" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.15-1.46-3.27-3.4h1.96c.1 1.05 1.18 1.91 2.53 1.91 1.29 0 2.13-.59 2.13-1.66 0-.85-.31-1.34-1.85-1.77l-.96-.28c-1.83-.48-3.05-1.46-3.05-3.51 0-2.09 1.49-3.32 3.44-3.66V4h2.67v1.93c1.5.25 2.91 1.26 3.11 3h-1.97c-.21-.92-1.12-1.63-2.31-1.63-1.04 0-1.91.43-1.91 1.5 0 .86.51 1.28 2.05 1.74l.96.26c1.9.57 3.03 1.55 3.03 3.63.02 1.95-1.35 3.25-3.35 3.63z">
                            </path>
                        </svg>
                    </div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            {{-- Icon Box Kecil --}}
                            <div
                                class="w-12 h-12 rounded-xl bg-neon/10 border border-neon/20 flex items-center justify-center text-neon">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>

                            {{-- Badge System Online --}}
                            <span
                                class="px-3 py-1 rounded-full bg-neon/10 border border-neon/30 text-neon text-[10px] font-bold uppercase tracking-wider animate-pulse">
                                System Online
                            </span>
                        </div>

                        <h3 class="text-3xl font-bold text-white mb-3">Lightning Fast Payouts</h3>
                        <p class="text-gray-400 text-sm leading-relaxed mb-6">
                            Algoritma pembayaran otomatis kami memproses ribuan request dalam hitungan detik.
                            Mendukung transfer ke <strong>20+ Bank Lokal</strong> & <strong>E-Wallet</strong> (Dana,
                            OVO, GoPay) secara real-time. Tanpa potongan tersembunyi, transparan 100%.
                        </p>

                        <div
                            class="flex items-center gap-3 mb-4 opacity-70 grayscale group-hover:grayscale-0 transition-all duration-500">

                            {{-- LOGO BANK (TETAP SAMA) --}}
                            <div
                                class="w-9 h-9 rounded-full bg-white p-1.5 flex items-center justify-center shadow-lg shadow-white/5 overflow-hidden">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg"
                                    class="w-full h-full object-contain" alt="BCA">
                            </div>
                            <div
                                class="w-9 h-9 rounded-full bg-white p-1.5 flex items-center justify-center shadow-lg shadow-white/5 overflow-hidden">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/6/68/BANK_BRI_logo.svg"
                                    class="w-full h-full object-contain" alt="BRI">
                            </div>
                            <div
                                class="w-9 h-9 rounded-full bg-white p-1.5 flex items-center justify-center shadow-lg shadow-white/5 overflow-hidden">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/2560px-Bank_Mandiri_logo_2016.svg.png"
                                    class="w-full h-full object-contain" alt="Mandiri">
                            </div>
                            <div
                                class="w-9 h-9 rounded-full bg-white p-1.5 flex items-center justify-center shadow-lg shadow-white/5 overflow-hidden">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg"
                                    class="w-full h-full object-contain" alt="DANA">
                            </div>
                            <div
                                class="w-9 h-9 rounded-full bg-white p-2 flex items-center justify-center shadow-lg shadow-white/5 overflow-hidden">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg"
                                    class="w-full h-full object-contain" alt="OVO">
                            </div>
                            <div
                                class="w-9 h-9 rounded-full bg-white p-1.5 flex items-center justify-center shadow-lg shadow-white/5 overflow-hidden">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg"
                                    class="w-full h-full object-contain" alt="GoPay">
                            </div>
                            <span class="text-xs text-gray-500 font-medium ml-1">+16 Others</span>
                        </div>
                    </div>

                    <div class="relative z-10 h-[280px] overflow-hidden mask-gradient-y pt-2">
                        <div class="animate-scroll-up flex flex-col gap-3 hover:pause"
                            style="animation-duration: 15s;">

                            {{-- LOOP WITHDRAWAL --}}
                            @for ($i = 0; $i < 2; $i++)
                                @if (isset($recentWithdrawals) && count($recentWithdrawals) > 0)
                                    @foreach ($recentWithdrawals as $trx)
                                        @php
                                            $userName = $trx->user->name ?? 'Clipper Member';
                                            $rawAvatar = $trx->user->avatar_url ?? null;
                                            if ($rawAvatar && !empty($rawAvatar)) {
                                                if (str_starts_with($rawAvatar, 'http')) {
                                                    $avatar = $rawAvatar;
                                                } else {
                                                    $avatar = asset('storage/' . $rawAvatar);
                                                }
                                            } else {
                                                $avatar =
                                                    'https://ui-avatars.com/api/?name=' .
                                                    urlencode($userName) .
                                                    '&background=random&color=fff&size=64';
                                            }
                                            $timeAgo =
                                                \Carbon\Carbon::parse($trx->created_at)->diffForHumans(null, true) .
                                                ' ago';
                                        @endphp

                                        <div
                                            class="bg-[#0A0A0A]/50 backdrop-blur-md rounded-xl p-3 border border-white/5 flex items-center justify-between transform hover:scale-[1.02] hover:bg-white/5 transition-all duration-300">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $avatar }}"
                                                    class="w-8 h-8 rounded-full bg-gray-800 object-cover border border-white/10"
                                                    alt="User">
                                                <div>
                                                    <div class="text-xs font-bold text-white flex items-center gap-1">
                                                        Withdraw Success
                                                        {{-- Icon Centang Neon --}}
                                                        <svg class="w-3 h-3 text-neon" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                    {{-- Teks Nama Neon --}}
                                                    <div class="text-[10px] text-neon">to
                                                        {{ \Illuminate\Support\Str::limit($userName, 12) }}</div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                {{-- Nominal Uang Neon --}}
                                                <div class="font-mono font-bold text-neon text-sm">
                                                    Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                                </div>
                                                <div class="text-[9px] text-gray-600 font-mono">{{ $timeAgo }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div
                                        class="bg-[#0A0A0A] rounded-xl p-3 border border-white/5 flex items-center justify-center text-gray-500 text-xs italic py-8">
                                        Waiting for payouts...
                                    </div>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- CARD 2: PUBLISHER ANALYTICS (TETAP ORANGE) --}}
                <div class="md:col-span-2 glass-card rounded-3xl p-8 relative group overflow-hidden"
                    data-aos="fade-left" data-aos-delay="100">
                    <svg class="absolute bottom-0 left-0 w-full h-24 text-orange-500/10 stroke-current fill-none pointer-events-none group-hover:text-orange-500/20 transition-colors"
                        viewBox="0 0 100 25" preserveAspectRatio="none">
                        <path stroke-width="0.5" d="M0 20 Q 10 15, 20 18 T 40 10 T 60 15 T 80 5 T 100 10 V 25 H 0 Z"
                            fill="currentColor" stroke="none" />
                        <path stroke-width="2" d="M0 20 Q 10 15, 20 18 T 40 10 T 60 15 T 80 5 T 100 10" />
                    </svg>

                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <h3 class="text-2xl font-bold text-white">Publisher Analytics</h3>
                                <span class="flex h-2 w-2 relative">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                                </span>
                            </div>
                            <p class="text-sm text-gray-400 mb-4 max-w-sm">
                                Pantau <span class="text-white font-bold">Virality Score</span>, Engagement Rate, dan
                                ROI campaign Anda secara real-time. Data driven decision untuk hasil maksimal.
                            </p>

                            <div class="flex gap-4">
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase">Views</p>
                                    <p class="text-lg font-bold text-white">1.2M+</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase">Conv. Rate</p>
                                    <p class="text-lg font-bold text-green-400">4.8%</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="w-16 h-16 rounded-2xl bg-[#0A0A0A] border border-orange-500/30 flex items-center justify-center shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: SECURITY (TETAP PURPLE) --}}
                <div class="glass-card rounded-3xl p-8 group relative overflow-hidden flex flex-col justify-center"
                    data-aos="fade-up" data-aos-delay="200">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-purple-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-4">
                            <div
                                class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                            </div>
                            <svg class="w-5 h-5 text-gray-600 group-hover:text-green-500 transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>

                        <h3 class="text-xl font-bold text-white mb-2">Military-Grade Security</h3>
                        <p class="text-xs text-gray-400 leading-relaxed mb-4">
                            Data Anda dilindungi enkripsi <span class="text-purple-400 font-mono">AES-256</span>.
                            Sistem kami dilengkapi Fraud Detection otomatis untuk mencegah kecurangan traffic.
                        </p>

                        <div class="w-full bg-gray-800 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-purple-500 h-1.5 rounded-full" style="width: 100%"></div>
                        </div>
                        <p class="text-[10px] text-right text-gray-500 mt-1">Status: Protected</p>
                    </div>
                </div>

                {{-- CARD 4: DISCORD (TETAP BLUE) --}}
                <div class="glass-card rounded-3xl p-8 relative overflow-hidden group cursor-pointer transition-all duration-500 hover:border-[#5865F2]/50 hover:shadow-[0_0_50px_rgba(88,101,242,0.2)] flex flex-col justify-between"
                    onclick="window.open('https://discord.gg/link-kamu', '_blank')" data-aos="fade-up"
                    data-aos-delay="300">

                    <div
                        class="absolute inset-0 bg-gradient-to-br from-[#5865F2]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                    <div
                        class="absolute -right-6 -bottom-6 text-[#5865F2] opacity-10 transform rotate-12 group-hover:scale-125 transition-transform duration-500 pointer-events-none">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.5151.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.419-2.1568 2.419zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.419-2.1568 2.419z" />
                        </svg>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#5865F2] text-white">NEW</span>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Community</span>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-1">Join Discord</h3>
                        <p class="text-xs text-gray-400">1.000+ Creators online.</p>
                    </div>

                    <div class="mt-4 flex justify-between items-end relative z-10">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full border-2 border-black bg-gray-700"></div>
                            <div class="w-8 h-8 rounded-full border-2 border-black bg-gray-600"></div>
                            <div
                                class="w-8 h-8 rounded-full border-2 border-black bg-gray-500 flex items-center justify-center text-[9px] font-bold">
                                +99</div>
                        </div>
                        <div
                            class="w-8 h-8 rounded-full bg-white text-[#5865F2] flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="roles" class="relative z-10 py-24 px-6">
        <div class="max-w-7xl mx-auto">

            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Choose Your <span class="text-neon">Path.</span>
                </h2>
                <p class="text-gray-400">Dua cara untuk sukses di ekosistem ClipSquad.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 h-auto md:h-[600px]">

                {{-- CARD 1: PUBLISHER (UPDATED: RED -> NEON) --}}
                <div
                    class="group relative rounded-3xl overflow-hidden border border-white/5 bg-[#0A0A0A] hover:border-neon/50 transition-all duration-700 flex flex-col justify-between p-8 md:p-12 cursor-default">

                    {{-- Gradient Overlay --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-neon/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                    </div>

                    {{-- SVG Map Background --}}
                    <div
                        class="absolute right-0 top-0 w-full h-full opacity-20 group-hover:opacity-30 transition-opacity duration-700 pointer-events-none">
                        <svg class="w-full h-full" viewBox="0 0 400 400" preserveAspectRatio="none">
                            <path d="M0 300 Q 100 200, 200 250 T 400 100" stroke="url(#grad1)" stroke-width="2"
                                fill="none"
                                class="group-hover:translate-y-[-10px] transition-transform duration-1000" />
                            <defs>
                                <linearGradient id="grad1" x1="0%" y1="0%" x2="100%"
                                    y2="0%">
                                    {{-- Update Gradient Line: Neon Dark -> Neon Bright --}}
                                    <stop offset="0%" style="stop-color:#b5d600;stop-opacity:0" />
                                    <stop offset="100%" style="stop-color:#d8ff15;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>

                    <div
                        class="relative z-10 translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                        {{-- Icon Box --}}
                        <div
                            class="w-16 h-16 rounded-2xl bg-neon/10 border border-neon/20 flex items-center justify-center text-neon mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-2">For Publishers</h3>
                        <p class="text-neon font-mono text-sm mb-6 uppercase tracking-widest">Brand & Agencies</p>
                        <p class="text-gray-400 leading-relaxed max-w-sm">
                            Ingin Brand Anda viral tanpa repot? Buat "Sayembara" (Bounty) dan biarkan ribuan kreator
                            berlomba upload video terbaik untuk brand Anda. Bayar hanya untuk performa.
                        </p>
                    </div>

                    <div class="relative z-10 mt-8 md:mt-0">
                        {{-- Features List --}}
                        <ul class="space-y-3 mb-8 opacity-60 group-hover:opacity-100 transition-opacity duration-500">
                            <li class="flex items-center gap-3 text-sm text-gray-300">
                                <svg class="w-5 h-5 text-neon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Unlimited Video Submission
                            </li>
                            <li class="flex items-center gap-3 text-sm text-gray-300">
                                <svg class="w-5 h-5 text-neon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Real-time Virality Analytics
                            </li>
                            <li class="flex items-center gap-3 text-sm text-gray-300">
                                <svg class="w-5 h-5 text-neon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Full Copyright Ownership
                            </li>
                        </ul>

                        {{-- Button (Hover Neon + Text Black) --}}
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-white text-black font-bold rounded-full hover:bg-neon hover:text-black transition-all duration-300 group-hover:translate-x-2">
                            Create Campaign
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- CARD 2: CLIPPER (TETAP GREEN STANDARD UNTUK VARIASI) --}}
                <div
                    class="group relative rounded-3xl overflow-hidden border border-white/5 bg-[#0A0A0A] hover:border-green-500/50 transition-all duration-700 flex flex-col justify-between p-8 md:p-12 cursor-default">

                    <div
                        class="absolute inset-0 bg-gradient-to-bl from-green-900/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700">
                    </div>

                    <div
                        class="absolute -right-10 -bottom-10 opacity-10 transform rotate-12 group-hover:scale-125 transition-transform duration-500 pointer-events-none">
                        <svg class="w-64 h-64 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.15-1.46-3.27-3.4h1.96c.1 1.05 1.18 1.91 2.53 1.91 1.29 0 2.13-.59 2.13-1.66 0-.85-.31-1.34-1.85-1.77l-.96-.28c-1.83-.48-3.05-1.46-3.05-3.51 0-2.09 1.49-3.32 3.44-3.66V4h2.67v1.93c1.5.25 2.91 1.26 3.11 3h-1.97c-.21-.92-1.12-1.63-2.31-1.63-1.04 0-1.91.43-1.91 1.5 0 .86.51 1.28 2.05 1.74l.96.26c1.9.57 3.03 1.55 3.03 3.63.02 1.95-1.35 3.25-3.35 3.63z">
                            </path>
                        </svg>
                    </div>

                    <div
                        class="relative z-10 translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                        <div
                            class="w-16 h-16 rounded-2xl bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-500 mb-6 group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-3xl md:text-4xl font-bold text-white mb-2">For Clippers</h3>
                        <p class="text-green-400 font-mono text-sm mb-6 uppercase tracking-widest">Video Editors &
                            Creators</p>
                        <p class="text-gray-400 leading-relaxed max-w-sm">
                            Punya skill editing dan suka bikin Konten? Hasilkan uang dari setiap video yang kamu
                            buat. Pilih misi, edit semenarik mungkin, upload, dan cairkan hadiahnya.
                        </p>
                    </div>

                    <div class="relative z-10 mt-8 md:mt-0">
                        <ul class="space-y-3 mb-8 opacity-60 group-hover:opacity-100 transition-opacity duration-500">
                            <li class="flex items-center gap-3 text-sm text-gray-300">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Work from Anywhere
                            </li>
                            <li class="flex items-center gap-3 text-sm text-gray-300">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Instant Payout to E-Wallet
                            </li>
                            <li class="flex items-center gap-3 text-sm text-gray-300">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                No Minimum Followers
                            </li>
                        </ul>

                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-green-600 to-green-500 text-white font-bold rounded-full hover:shadow-[0_0_20px_rgba(34,197,94,0.4)] transition-all duration-300 group-hover:translate-x-2">
                            Start Earning
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="relative z-10 py-32 px-6 border-t border-white/5 bg-[#050505] overflow-hidden">

        {{-- Glow Background Merah -> Neon --}}
        <div
            class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-b from-neon/10 to-transparent blur-[120px] pointer-events-none">
        </div>
        <div class="absolute inset-0 premium-bg opacity-30 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 items-start">

                <div class="lg:sticky lg:top-32" data-aos="fade-right">
                    <div class="flex items-center gap-3 mb-6">
                        {{-- Garis & Teks Merah -> Neon --}}
                        <div class="h-[1px] w-12 bg-neon"></div>
                        <span class="text-sm font-bold text-neon uppercase tracking-widest">Our Story</span>
                    </div>

                    <h2 class="text-5xl md:text-7xl font-bold mb-8 tracking-tight leading-tight text-white">
                        We are <br>
                        {{-- Gradient Text Merah-Orange -> Neon-Emerald --}}
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-emerald-500">Clipkeo.</span>
                    </h2>

                    <div class="space-y-6 text-lg text-gray-400 leading-relaxed font-light">
                        <p>
                            Lahir dari semangat inovasi di <strong class="text-white">Indonesia</strong>, kami melihat
                            celah besar di industri konten digital. Brand membutuhkan viralitas yang masif, sedangkan
                            Ribuan Kreator membutuhkan penghasilan yang real.
                        </p>
                        <p>
                            ClipSquad hadir sebagai jembatan teknologi. Kami bukan sekadar agensi marketing, kami adalah
                            <strong class="text-white">Ekosistem Performa</strong>. Di sini, algoritma bertemu dengan
                            kreativitas manusia untuk menciptakan dampak yang nyata.
                        </p>
                    </div>

                    {{-- Quote Box Border Merah -> Neon --}}
                    <div class="mt-12 p-6 border-l-4 border-neon bg-white/5 backdrop-blur-sm rounded-r-2xl">
                        <p class="text-xl italic text-gray-300 mb-4">"Misi kami sederhana: Memastikan setiap detik
                            kreativitas dihargai dengan benefit yang layak."</p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-white border border-gray-500">
                                CEO
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">Founder Clipkeo</p>
                                <p class="text-[10px] text-gray-500 uppercase">Yoga Dwi Sutrisno</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 relative">
                    {{-- Garis Timeline Vertical Merah -> Neon --}}
                    <div
                        class="absolute left-8 top-0 bottom-0 w-[1px] bg-gradient-to-b from-neon/50 via-gray-800 to-transparent hidden md:block">
                    </div>

                    {{-- CARD 1: SPEED (UPDATED TO NEON) --}}
                    <div class="glass-card p-8 rounded-3xl group hover:bg-white/5 transition-all duration-500 relative ml-0 md:ml-12"
                        data-aos="fade-up" data-aos-delay="100">
                        {{-- Dot Timeline Merah -> Neon --}}
                        <div
                            class="absolute -left-12 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[#050505] border-2 border-neon z-10 hidden md:block group-hover:scale-125 transition-transform">
                        </div>
                        {{-- Icon Box Merah -> Neon --}}
                        <div
                            class="w-14 h-14 rounded-2xl bg-neon/10 flex items-center justify-center text-neon mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Speed is Everything</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Di dunia viral, telat 1 jam bisa fatal. Sistem kami didesain untuk approval misi dan payout
                            tercepat di industri. Tidak ada birokrasi berbelit.
                        </p>
                    </div>

                    {{-- CARD 2: TRANSPARENCY (BLUE - TETAP) --}}
                    <div class="glass-card p-8 rounded-3xl group hover:bg-white/5 transition-all duration-500 relative ml-0 md:ml-12"
                        data-aos="fade-up" data-aos-delay="200">
                        <div
                            class="absolute -left-12 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[#050505] border-2 border-gray-700 group-hover:border-white z-10 hidden md:block transition-colors">
                        </div>
                        <div
                            class="w-14 h-14 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500 mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Radical Transparency</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Tidak ada shadow banning, tidak ada potongan tersembunyi. Data analytics Publisher dan
                            penghasilan Clipper ditampilkan real-time apa adanya.
                        </p>
                    </div>

                    {{-- CARD 3: COMMUNITY (ORANGE - TETAP) --}}
                    <div class="glass-card p-8 rounded-3xl group hover:bg-white/5 transition-all duration-500 relative ml-0 md:ml-12"
                        data-aos="fade-up" data-aos-delay="300">
                        <div
                            class="absolute -left-12 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full bg-[#050505] border-2 border-gray-700 group-hover:border-orange-500 z-10 hidden md:block transition-colors">
                        </div>
                        <div
                            class="w-14 h-14 rounded-2xl bg-orange-500/10 flex items-center justify-center text-orange-500 mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Community First</h3>
                        <p class="text-gray-400 text-sm leading-relaxed">
                            Kami tumbuh karena komunitas. Feedback dari Clipper dan Publisher adalah blueprint utama
                            pengembangan fitur kami. Kami besar bersama Anda.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="relative z-10 py-24 px-6 overflow-hidden">
        {{-- UPDATE: Glow Background Merah -> Neon --}}
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] bg-neon/10 rounded-full blur-[100px] pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-yellow-500/30 bg-yellow-500/10 mb-6">
                    <span class="text-[10px] font-bold text-yellow-500 uppercase tracking-widest">Real Results</span>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Success <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">Stories.</span>
                </h2>
                <p class="text-gray-400 max-w-2xl mx-auto">Mereka yang telah membuktikan kekuatan ekosistem ClipSquad.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                {{-- CARD 1: CLIPPER (GREEN STANDARD - TETAP) --}}
                <div class="glass-card p-8 rounded-3xl relative group hover:-translate-y-2 transition-transform duration-500"
                    data-aos="fade-up" data-aos-delay="0">
                    <div class="absolute top-4 right-6 text-7xl font-serif text-white/5 font-black">"</div>

                    <div class="flex items-center gap-4 mb-6">
                        <img src="https://ui-avatars.com/api/?name=Rizky+A&background=22c55e&color=fff" alt="User"
                            class="w-12 h-12 rounded-full border-2 border-green-500 p-0.5">
                        <div>
                            <h4 class="text-white font-bold">Rizky Aditya</h4>
                            <p class="text-xs text-green-400 uppercase tracking-wider font-bold">Top Clipper •
                                Mahasiswa</p>
                        </div>
                    </div>

                    <p class="text-gray-300 text-sm leading-relaxed mb-6">
                        "Awalnya iseng motong live streaming youtuber gaming. Gak nyangka video 45 detik saya tembus 2
                        juta views. Bulan ini cair <strong>Rp 4.5 Juta</strong>, lumayan buat bayar UKT tanpa minta
                        ortu!"
                    </p>

                    <div class="border-t border-white/10 pt-4 mt-auto">
                        <p class="text-[10px] text-gray-500 uppercase mb-1">Total Earnings</p>
                        <p class="text-2xl font-mono font-bold text-green-400">Rp 12.850.000</p>
                    </div>
                </div>

                {{-- CARD 2: PUBLISHER (UPDATED: RED -> NEON) --}}
                <div class="glass-card p-8 rounded-3xl relative group hover:-translate-y-2 transition-transform duration-500 border-neon/30 bg-neon/5"
                    data-aos="fade-up" data-aos-delay="100">
                    <div class="absolute top-4 right-6 text-7xl font-serif text-neon/10 font-black">"</div>

                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-black font-bold text-xl">
                            K</div>
                        <div>
                            <h4 class="text-white font-bold">Kopi Senja</h4>
                            {{-- Role Badge Neon --}}
                            <p class="text-xs text-neon uppercase tracking-wider font-bold">F&B Brand • Publisher
                            </p>
                        </div>
                    </div>

                    <p class="text-gray-300 text-sm leading-relaxed mb-6">
                        "Budget marketing kami terbatas, tapi butuh viral. Kami buka bounty Rp 2 Juta di ClipSquad.
                        Hasilnya? <strong class="text-white">500+ Video TikTok</strong> dibuat oleh clipper. Sales naik
                        300% dalam seminggu."
                    </p>

                    <div class="border-t border-white/10 pt-4 mt-auto flex justify-between items-center">
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase mb-1">Total Views Generated</p>
                            <p class="text-2xl font-mono font-bold text-white">3.4M <span
                                    class="text-sm text-gray-500 font-normal">Views</span></p>
                        </div>
                        {{-- Icon Box Neon --}}
                        <div
                            class="w-10 h-10 rounded-full bg-neon/20 flex items-center justify-center text-neon animate-pulse">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: FREELANCER (ORANGE - TETAP) --}}
                <div class="glass-card p-8 rounded-3xl relative group hover:-translate-y-2 transition-transform duration-500"
                    data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute top-4 right-6 text-7xl font-serif text-white/5 font-black">"</div>

                    <div class="flex items-center gap-4 mb-6">
                        <img src="https://ui-avatars.com/api/?name=Sarah+P&background=f97316&color=fff" alt="User"
                            class="w-12 h-12 rounded-full border-2 border-orange-500 p-0.5">
                        <div>
                            <h4 class="text-white font-bold">Sarah Putri</h4>
                            <p class="text-xs text-orange-400 uppercase tracking-wider font-bold">Freelancer • Ibu RT
                            </p>
                        </div>
                    </div>

                    <p class="text-gray-300 text-sm leading-relaxed mb-6">
                        "Saya gaptek edit video canggih. Tapi di ClipSquad cuma modal HP, potong-potong video podcast,
                        kasih caption otomatis. Sambil jaga anak di rumah, minggu ini cair <strong>Rp 800rb</strong> via
                        GoPay."
                    </p>

                    <div class="border-t border-white/10 pt-4 mt-auto">
                        <p class="text-[10px] text-gray-500 uppercase mb-1">Weekly Payout</p>
                        <p class="text-2xl font-mono font-bold text-orange-400">Rp 850.000</p>
                    </div>
                </div>

            </div>

            {{-- LOGO PARTNER (TETAP GRAYSCALE) --}}
            <div class="mt-16 text-center border-t border-white/5 pt-8" data-aos="fade-in">
                <p class="text-xs text-gray-500 uppercase tracking-widest mb-6">Trusted by Innovative Teams</p>
                <div class="flex flex-wrap justify-center gap-8 md:gap-16 opacity-40 grayscale">
                    <span class="text-xl font-black text-white font-serif">VOGUE</span>
                    <span class="text-xl font-black text-white font-sans">TechDaily</span>
                    <span class="text-xl font-black text-white font-mono">CODE_ID</span>
                    <span class="text-xl font-black text-white font-sans italic">Foodies.</span>
                    <span class="text-xl font-black text-white font-serif">STARTUP+</span>
                </div>
            </div>

        </div>
    </section>

    <section id="contact" class="relative z-10 py-24 px-6 overflow-hidden">

        {{-- UPDATE: Glow Background Merah -> Neon --}}
        <div
            class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-neon/10 rounded-full blur-[120px] pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

                <div data-aos="fade-right">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-gray-700 bg-gray-800/50 mb-6">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">Support
                            Online</span>
                    </div>

                    <h2 class="text-5xl font-bold mb-6 text-white leading-tight">
                        Let's Start a <br>
                        {{-- UPDATE: Gradient Teks Merah -> Neon --}}
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-emerald-500">Conversation.</span>
                    </h2>

                    <p class="text-gray-400 text-lg mb-10 max-w-md leading-relaxed">
                        Punya pertanyaan tentang cara kerja bounty? Atau brand Anda ingin custom campaign? Tim kami siap
                        membantu 24/7.
                    </p>

                    <div class="space-y-4">
                        {{-- UPDATE: Hover Border Merah -> Neon --}}
                        <a href="mailto:support@clipsquad.id"
                            class="flex items-center gap-4 p-4 rounded-2xl border border-white/5 bg-white/5 hover:bg-white/10 hover:border-neon/30 transition-all duration-300 group">
                            <div
                                class="w-12 h-12 rounded-xl bg-[#0A0A0A] flex items-center justify-center text-white group-hover:text-neon transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-bold">Email Support</p>
                                <p class="text-white font-bold group-hover:text-neon transition-colors">
                                    support@clipkeo.id</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-600 ml-auto group-hover:translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <a href="https://discord.gg/link-kamu" target="_blank"
                            class="flex items-center gap-4 p-4 rounded-2xl border border-white/5 bg-white/5 hover:bg-[#5865F2]/10 hover:border-[#5865F2]/30 transition-all duration-300 group">
                            <div
                                class="w-12 h-12 rounded-xl bg-[#0A0A0A] flex items-center justify-center text-white group-hover:text-[#5865F2] transition-colors">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.5151.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.419-2.1568 2.419zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.419-2.1568 2.419z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-bold">Community Chat</p>
                                <p class="text-white font-bold group-hover:text-[#5865F2] transition-colors">Join
                                    Discord Server</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-600 ml-auto group-hover:translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>
                    </div>
                </div>

                <div data-aos="fade-left">
                    {{-- UPDATE: Border Atas Form Gradient Merah -> Neon --}}
                    <div class="glass-card p-8 md:p-10 rounded-3xl border-t border-white/10 relative overflow-hidden">
                        <div
                            class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-neon via-emerald-500 to-transparent">
                        </div>

                        <h3 class="text-2xl font-bold text-white mb-6">Send Message</h3>

                        @if (session('success'))
                            <div
                                class="mb-4 bg-green-500/20 border border-green-500 text-green-100 px-4 py-3 rounded-xl text-sm font-bold text-center">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('contact.store') }}" method="POST" class="space-y-5">
                            @csrf

                            <div class="grid grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label
                                        class="text-xs font-bold text-gray-500 uppercase tracking-wider">Name</label>
                                    {{-- UPDATE: Focus Ring Merah -> Neon --}}
                                    <input type="text" name="name" required placeholder="John Doe"
                                        class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-600 focus:ring-1 focus:ring-neon focus:border-neon outline-none transition">
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role</label>
                                    <div class="relative" x-data="{
                                        open: false,
                                        selected: '',
                                        label: 'Select Role'
                                    }">
                                        <input type="hidden" name="role" :value="selected" required>

                                        {{-- UPDATE: Focus/Active Ring Merah -> Neon --}}
                                        <button type="button" @click="open = !open" @click.outside="open = false"
                                            class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-left flex items-center justify-between transition-all duration-300 group hover:border-white/30 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon"
                                            :class="open ? 'border-neon ring-1 ring-neon' : ''">

                                            <span class="flex items-center gap-2"
                                                :class="selected ? 'text-white' : 'text-gray-500'">
                                                <template x-if="selected === 'Clipper'">
                                                    <svg class="w-4 h-4 text-green-500" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <template x-if="selected === 'Publisher'">
                                                    {{-- Icon Publisher tetap Neon karena sekarang tema full hijau --}}
                                                    <svg class="w-4 h-4 text-neon" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <template x-if="selected === 'Other'">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <span x-text="label"></span>
                                            </span>

                                            <svg class="w-4 h-4 text-gray-500 transition-transform duration-300"
                                                :class="open ? 'rotate-180 text-neon' : ''" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 translate-y-2"
                                            class="absolute z-50 w-full mt-2 bg-[#1a1a1a] border border-white/10 rounded-xl shadow-2xl overflow-hidden backdrop-blur-xl"
                                            style="display: none;">

                                            <ul class="py-1">
                                                <li @click="selected = 'Clipper'; label = 'I am a Clipper'; open = false"
                                                    class="px-4 py-3 hover:bg-white/5 cursor-pointer flex items-center gap-3 transition-colors group">
                                                    <span class="text-sm text-gray-300 group-hover:text-white">I am a
                                                        Clipper</span>
                                                </li>
                                                <li @click="selected = 'Publisher'; label = 'I am a Publisher'; open = false"
                                                    class="px-4 py-3 hover:bg-white/5 cursor-pointer flex items-center gap-3 transition-colors group">
                                                    <span class="text-sm text-gray-300 group-hover:text-white">I am a
                                                        Publisher</span>
                                                </li>
                                                <li @click="selected = 'Other'; label = 'Other / Visitor'; open = false"
                                                    class="px-4 py-3 hover:bg-white/5 cursor-pointer flex items-center gap-3 transition-colors group">
                                                    <span
                                                        class="text-sm text-gray-300 group-hover:text-white">Other</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Email</label>
                                <input type="email" name="email" required placeholder="john@example.com"
                                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-600 focus:ring-1 focus:ring-neon focus:border-neon outline-none transition">
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Message</label>
                                <textarea name="message" rows="4" required placeholder="Tell us what you need..."
                                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-600 focus:ring-1 focus:ring-neon focus:border-neon outline-none transition"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full py-4 bg-white text-black font-bold rounded-xl hover:bg-neon transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-lg flex items-center justify-center gap-2 group">
                                Send Request
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </form>

                        <p class="text-center text-[10px] text-gray-600 mt-6">
                            Protected by Clipkeo Shield. Your data is secure.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer class="relative z-20 border-t border-white/5 bg-[#050505] pt-20 pb-10 overflow-hidden">

        {{-- Big Watermark Text --}}
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full text-center pointer-events-none select-none">
            <h1 class="text-[15vw] font-black text-white/[0.02] leading-none tracking-tighter">CLIPKEO</h1>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 mb-16">

                <div class="md:col-span-4 space-y-6">
                    <div class="flex items-center gap-3">
                        {{-- LOGO IMAGE --}}
                        {{-- Pastikan file ada di folder: public/images/logo.png --}}
                        <img src="{{ asset('images/logo.png') }}" alt="Clipkeo Logo"
                            class="w-20 h-20 object-contain drop-shadow-[0_0_15px_rgba(216,255,21,0.5)]">
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                        Platform ekosistem konten kreator #1 di Indonesia. Kami menghubungkan brand dengan ribuan
                        kreator berbakat untuk menciptakan viralitas yang terukur.
                    </p>

                    <div class="flex items-center gap-4">
                        {{-- UPDATE: Hover Social Media Merah -> Neon --}}
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/5 border border-white/5 flex items-center justify-center text-gray-400 hover:bg-neon hover:text-black hover:scale-110 transition-all duration-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/5 border border-white/5 flex items-center justify-center text-gray-400 hover:bg-[#5865F2] hover:text-white hover:scale-110 transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.5151.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.419-2.1568 2.419zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.419-2.1568 2.419z" />
                            </svg>
                        </a>
                        <a href="#"
                            class="w-10 h-10 rounded-full bg-white/5 border border-white/5 flex items-center justify-center text-gray-400 hover:bg-black hover:text-white hover:border-white hover:scale-110 transition-all duration-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <h4 class="text-white font-bold mb-6">Platform</h4>
                    <ul class="space-y-4 text-sm text-gray-400">
                        {{-- UPDATE: Hover Text Merah -> Neon --}}
                        <li><a href="#" class="hover:text-neon transition-colors flex items-center gap-1">For
                                Clippers <span class="text-[10px] bg-white/10 px-1 rounded ml-1">HOT</span></a></li>
                        <li><a href="#" class="hover:text-neon transition-colors">For Publishers</a></li>
                        <li><a href="#" class="hover:text-neon transition-colors">Pricing</a></li>
                        <li><a href="#" class="hover:text-neon transition-colors">Success Stories</a></li>
                    </ul>
                </div>

                <div class="md:col-span-2">
                    <h4 class="text-white font-bold mb-6">Company</h4>
                    <ul class="space-y-4 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-neon transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-neon transition-colors">Careers</a></li>
                        <li><a href="#" class="hover:text-neon transition-colors">Blog</a></li>
                        <li><a href="#" class="hover:text-neon transition-colors">Contact</a></li>
                    </ul>
                </div>

                <div class="md:col-span-4 bg-white/5 rounded-3xl p-6 border border-white/5">
                    <h4 class="text-white font-bold mb-2">Stay Updated</h4>
                    <p class="text-xs text-gray-400 mb-4">Dapatkan info misi viral terbaru langsung ke inboxmu.</p>

                    <form class="flex gap-2 mb-4">
                        {{-- UPDATE: Focus Border Merah -> Neon --}}
                        <input type="email" placeholder="Enter your email"
                            class="w-full bg-black/50 border border-white/10 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon transition">

                        {{-- UPDATE: Button Merah -> Neon (Text Black) --}}
                        <button
                            class="bg-neon hover:bg-neon-dark text-black px-4 py-2 rounded-xl font-bold transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </form>

                    <div class="flex items-center gap-2 pt-4 border-t border-white/5">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-mono text-gray-500 uppercase tracking-widest">All Systems
                            Normal</span>
                    </div>
                </div>

            </div>

            <div
                class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-white/5 text-xs text-gray-600">
                <p>&copy; 2026 Clipkeo Inc. Based in Indonesia.</p>
                <div class="flex gap-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition">Terms of Service</a>
                    <a href="#" class="hover:text-white transition">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });
    </script>

    {{-- ======================================================================= --}}
    {{-- PREMIUM LOGIN MODAL (UPDATED: NEON THEME)                               --}}
    {{-- ======================================================================= --}}
    <div x-show="activeModal === 'login'" style="display: none;"
        class="fixed inset-0 z-[999] flex items-center justify-center px-4"
        x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- 1. Backdrop Blur Gelap --}}
        <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="activeModal = null"></div>

        {{-- 2. Card Modal --}}
        <div class="relative w-full max-w-md bg-[#050505] border border-white/10 rounded-[2.5rem] shadow-[0_0_50px_rgba(216,255,21,0.15)] overflow-hidden"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-20 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-20 scale-95">

            {{-- UPDATE: Glow Merah -> Neon --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-neon/10 rounded-full blur-[80px] pointer-events-none">
            </div>

            {{-- Tombol Close --}}
            <button @click="activeModal = null"
                class="absolute top-6 right-6 text-gray-500 hover:text-white transition z-20 bg-white/5 hover:bg-white/10 p-2 rounded-full backdrop-blur-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <div class="p-8 md:p-10 relative z-10">

                {{-- Header --}}
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-black text-white tracking-tight mb-2">Welcome Back.</h2>
                    <p class="text-sm text-gray-400">Masuk untuk mengelola misi dan earnings.</p>
                </div>

                {{-- TOMBOL GOOGLE --}}
                <a href="{{ route('auth.google') }}"
                    class="flex items-center justify-center gap-3 w-full bg-white text-black font-bold py-3.5 rounded-full hover:bg-gray-200 transition-all transform hover:scale-[1.02] mb-6">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M23.766 12.2764C23.766 11.4607 23.6999 10.6406 23.5588 9.83807H12.24V14.4591H18.7217C18.4528 15.9494 17.5885 17.2678 16.323 18.1056V21.1039H20.19C22.4608 19.0139 23.766 15.9274 23.766 12.2764Z"
                            fill="#4285F4" />
                        <path
                            d="M12.2401 24.0008C15.4766 24.0008 18.2059 22.9382 20.1945 21.1039L16.3275 18.1055C15.2517 18.8375 13.8627 19.252 12.2445 19.252C9.11388 19.252 6.45946 17.1399 5.50705 14.3003H1.5166V17.3912C3.55371 21.4434 7.7029 24.0008 12.2401 24.0008Z"
                            fill="#34A853" />
                        <path
                            d="M5.50253 14.3003C5.00236 12.8099 5.00236 11.1961 5.50253 9.70575V6.61481H1.51649C-0.18551 10.0056 -0.18551 14.0004 1.51649 17.3912L5.50253 14.3003Z"
                            fill="#FBBC05" />
                        <path
                            d="M12.2401 4.74966C13.9509 4.7232 15.6044 5.36697 16.8434 6.54867L20.2695 3.12262C18.1001 1.0855 15.2208 -0.034466 12.2401 0.000808666C7.7029 0.000808666 3.55371 2.55822 1.5166 6.61481L5.50264 9.70575C6.45064 6.86173 9.10947 4.74966 12.2401 4.74966Z"
                            fill="#EA4335" />
                    </svg>
                    Continue with Google
                </a>

                <div class="relative flex py-2 items-center mb-6">
                    <div class="flex-grow border-t border-white/10"></div>
                    <span class="flex-shrink-0 mx-4 text-xs font-bold text-gray-600 uppercase tracking-widest">Or
                        Email</span>
                    <div class="flex-grow border-t border-white/10"></div>
                </div>

                {{-- FORM LOGIN --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Email
                            Address</label>
                        {{-- UPDATE: Focus Ring Merah -> Neon --}}
                        <input type="email" name="email" required autofocus
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-600 focus:ring-1 focus:ring-neon focus:border-neon outline-none transition"
                            placeholder="name@example.com" value="{{ old('email') }}">
                        @error('email')
                            <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div x-data="{ show: false }">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <button type="button" @click="activeModal = 'forgot'"
                                    class="text-xs font-bold text-neon hover:text-white transition">
                                    Lupa Password
                                </button>
                            @endif
                        </div>

                        <div class="relative group">
                            <input :type="show ? 'text' : 'password'" name="password" required
                                class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 pr-12 text-white placeholder-gray-600 focus:ring-1 focus:ring-neon focus:border-neon outline-none transition"
                                placeholder="••••••••">

                            <button type="button" @click="show = !show"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-neon transition-colors focus:outline-none">

                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>

                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.822 7.822L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" name="remember" id="remember"
                            class="rounded bg-dark/10 border-white/10 text-neon focus:ring-neon focus:ring-offset-dark-900 cursor-pointer">
                        <label for="remember"
                            class="text-xs text-gray-400 font-medium cursor-pointer group-hover:text-white transition">Keep
                            me logged in</label>
                    </div>

                    {{-- Submit Button --}}
                    {{-- UPDATE: Gradient Merah -> Neon --}}
                    <button type="submit"
                        class="w-full bg-neon hover:bg-neon-dark text-black font-bold py-3.5 rounded-full shadow-[0_10px_30px_-10px_rgba(216,255,21,0.5)] transition-all transform hover:scale-[1.02] active:scale-[0.98]">
                        Masuk Sekarang
                    </button>
                </form>

                <div class="mt-8 text-center border-t border-white/5 pt-6">
                    <p class="text-xs text-gray-500">Belum punya akun? <button @click="activeModal = 'register'"
                            class="font-bold text-white hover:text-neon transition">Daftar Sekarang</button></p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- PREMIUM FORGOT PASSWORD MODAL (OTP VERSION)                             --}}
    {{-- ======================================================================= --}}
    <div x-data="{
        step: 'email',
        email: '',
        otp: '',
        newPassword: '',
        newPassword_confirmation: '',
        loading: false,
        message: '',
        status: '', // 'success' atau 'error'
    
        // TAHAP 1: Kirim OTP ke Email
        async sendOtp() {
            if (!this.email) return;
            this.loading = true;
            this.message = '';
    
            try {
                const response = await fetch('{{ route('password.otp.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: this.email })
                });
                const result = await response.json();
    
                if (result.success) {
                    this.step = 'otp';
                    this.status = 'success';
                    this.message = result.message;
                } else {
                    this.status = 'error';
                    this.message = result.message;
                }
            } catch (e) {
                this.status = 'error';
                this.message = 'Terjadi kesalahan sistem. Coba lagi nanti.';
            } finally { this.loading = false; }
        },
    
        // TAHAP 2: Verifikasi OTP & Reset Password
        async resetPassword() {
            this.loading = true;
            this.message = '';
    
            try {
                const response = await fetch('{{ route('password.otp.reset') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: this.email,
                        otp: this.otp,
                        password: this.newPassword,
                        password_confirmation: this.newPassword_confirmation
                    })
                });
                const result = await response.json();
    
                if (result.success) {
                    // Berhasil! Kasih notifikasi lalu reload atau pindah ke login
                    alert('Password berhasil diupdate! Silakan login kembali.');
                    window.location.reload();
                } else {
                    this.status = 'error';
                    this.message = result.message;
                }
            } catch (e) {
                this.status = 'error';
                this.message = 'Gagal memproses reset password.';
            } finally { this.loading = false; }
        }
    }" x-show="activeModal === 'forgot'" style="display: none;"
        class="fixed inset-0 z-[999] flex items-center justify-center px-4"
        x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="activeModal = null"></div>

        {{-- Card Modal --}}
        <div
            class="relative w-full max-w-md bg-[#050505] border border-white/10 rounded-[2.5rem] shadow-[0_0_50px_rgba(59,130,246,0.15)] overflow-hidden">

            {{-- Glow Effect --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-neon/10 rounded-full blur-[80px] pointer-events-none">
            </div>

            {{-- Close Button --}}
            <button @click="activeModal = null"
                class="absolute top-6 right-6 text-gray-500 hover:text-white transition z-20 bg-white/5 hover:bg-white/10 p-2 rounded-full backdrop-blur-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="p-8 md:p-10 relative z-10">
                {{-- Icon --}}
                <div class="flex justify-center mb-6">
                    <div
                        class="w-16 h-16 rounded-2xl bg-neon/10 border border-neon flex items-center justify-center text-neon shadow-lg shadow-neon/10">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z">
                            </path>
                        </svg>
                    </div>
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-3xl font-black text-white tracking-tight mb-2">Reset Password</h2>
                    <p class="text-sm text-gray-400"
                        x-text="step === 'email' ? 'Masukkan email untuk kode OTP.' : 'Masukkan kode OTP dan password baru.'">
                    </p>
                </div>

                {{-- Status Messages --}}
                <div x-show="message" x-transition
                    :class="status === 'success' ? 'bg-green-500/20 border-green-500/50 text-green-200' :
                        'bg-red-500/20 border-red-500/50 text-red-200'"
                    class="mb-6 border px-4 py-3 rounded-xl text-xs font-bold text-center flex items-center justify-center gap-2">
                    <span x-text="message"></span>
                </div>

                {{-- TAHAP 1: INPUT EMAIL --}}
                <div x-show="step === 'email'" class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">Email
                            Address</label>
                        <input type="email" x-model="email" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white placeholder-gray-600 focus:ring-1 focus:ring-neon focus:border-neon outline-none transition"
                            placeholder="name@example.com">
                    </div>

                    <button @click="sendOtp()" :disabled="loading || !email"
                        class="w-full bg-neon hover:bg-neon-500 text-gray-800 font-bold py-3.5 rounded-full shadow-neon transition-all transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">Kirim Kode OTP</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Mengirim...
                        </span>
                    </button>
                </div>

                <div x-show="step === 'otp'" x-cloak class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-neon uppercase tracking-widest mb-2 ml-1">
                            6 Digit OTP
                        </label>
                        <input type="text" x-model="otp" maxlength="6"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white text-center text-xl font-black tracking-[0.5em] focus:ring-1 focus:ring-neon outline-none transition"
                            placeholder="******">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">
                            Password Baru
                        </label>
                        <input type="password" x-model="newPassword"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white focus:ring-1 focus:ring-neon outline-none transition"
                            placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 ml-1">
                            Konfirmasi Password
                        </label>
                        <input type="password" x-model="newPassword_confirmation"
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3.5 text-white focus:ring-1 focus:ring-neon outline-none transition"
                            placeholder="••••••••">
                    </div>

                    <button @click="resetPassword()" :disabled="loading || otp.length < 6 || newPassword.length < 8"
                        class="w-full bg-neon hover:shadow-[0_0_20px_rgba(57,255,20,0.5)] text-black font-black py-3.5 rounded-full shadow-lg transition-all transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-30 disabled:grayscale">
                        <span x-show="!loading">UPDATE PASSWORD</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-black" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            MEMPROSES...
                        </span>
                    </button>

                    <button @click="step = 'email'; message = ''"
                        class="w-full text-center text-[10px] font-bold text-gray-500 hover:text-white uppercase tracking-widest transition mt-2">
                        Ganti Email?
                    </button>
                </div>

                <div class="mt-8 text-center border-t border-white/5 pt-6">
                    <p class="text-xs text-gray-500">Ingat password Anda?
                        <button @click="activeModal = 'login'"
                            class="font-bold text-white hover:text-neon transition ml-1">Masuk Kembali</button>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- PREMIUM REGISTER MODAL (REFINED LANDSCAPE EDITION)                      --}}
    {{-- ======================================================================= --}}
    <div x-show="activeModal === 'register'" style="display: none;"
        class="fixed inset-0 z-[999] flex items-center justify-center px-4"
        x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="activeModal = null"></div>

        {{-- Modal Container --}}
        <div class="relative w-full max-w-4xl bg-[#09090b] border border-white/10 rounded-[2rem] shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-10 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-10 scale-95">

            {{-- Ambient Glow --}}
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-neon/10 rounded-full blur-[120px] pointer-events-none transition-colors duration-500"
                :class="role === 'clipper' ? 'bg-green-500/10' : 'bg-neon/10'"></div>

            {{-- Close Button --}}
            <button @click="activeModal = null"
                class="absolute top-6 right-6 text-gray-500 hover:text-white transition z-20 bg-white/5 hover:bg-white/10 p-2.5 rounded-full backdrop-blur-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="p-8 md:p-10 relative z-10" x-data="{
                role: 'clipper',
            
                // State untuk API Wilayah
                provinces: [],
                cities: [],
                selectedProvince: '',
                selectedCity: '',
                loadingCities: false,
            
                // State untuk Password Toggle
                showPassword: false,
                showConfirm: false,
            
                // Fungsi Ambil Data Provinsi
                async fetchProvinces() {
                    try {
                        const response = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
                        this.provinces = await response.json();
                    } catch (e) { console.error('Gagal ambil provinsi'); }
                },
            
                // Fungsi Ambil Data Kota
                async fetchCities() {
                    if (!this.selectedProvince) return;
                    this.loadingCities = true;
                    try {
                        const response = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${this.selectedProvince}.json`);
                        this.cities = await response.json();
                    } catch (e) { console.error('Gagal ambil kota'); }
                    this.loadingCities = false;
                }
            }" x-init="fetchProvinces()">

                {{-- Header Section --}}
                <div class="text-center md:text-left mb-8">
                    <h2 class="text-3xl font-black text-white tracking-tight mb-2">Join Clipkeo Squad.</h2>
                    <p class="text-sm text-gray-400">Pilih role kamu dan lengkapi data di bawah ini.</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="hidden" name="role" :value="role">

                    {{-- 1. ROLE SWITCHER (Dipindah ke atas, gaya Segmented Control) --}}
                    <div
                        class="bg-[#121214] border border-white/5 p-1.5 rounded-2xl flex items-center mb-8 relative z-20">
                        <button type="button" @click="role = 'clipper'"
                            class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl transition-all duration-300 font-bold text-sm"
                            :class="role === 'clipper' ? 'bg-white/10 text-neon shadow-sm' :
                                'text-gray-500 hover:text-gray-300'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                            Saya seorang Clipper
                        </button>
                        <button type="button" @click="role = 'publisher'"
                            class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl transition-all duration-300 font-bold text-sm"
                            :class="role === 'publisher' ? 'bg-white/10 text-green-500 shadow-sm' :
                                'text-gray-500 hover:text-gray-300'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            Saya seorang Publisher
                        </button>
                    </div>

                    {{-- DYNAMIC CONTENT WRAPPER --}}
                    <div class="relative min-h-[420px]">

                        {{-- ========================================== --}}
                        {{-- VIEW 1: CLIPPER FORM --}}
                        {{-- ========================================== --}}
                        <div x-show="role === 'clipper'"
                            x-transition:enter="transition ease-out duration-500 delay-100"
                            x-transition:enter-start="opacity-0 translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300 absolute inset-0"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 -translate-x-4" class="w-full">

                            {{-- 2. FORM GRID (Dengan Input Box Gelap Pekat / Pure Black) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8 mb-8">

                                {{-- KIRI: Informasi Personal & Kontak --}}
                                <div class="space-y-6">
                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-2.5 ml-1">Nama
                                            Lengkap</label>
                                        <input type="text" name="name"
                                            class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 text-sm text-white placeholder-gray-700 outline-none transition-all duration-300 hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon"
                                            placeholder="Nama Lengkap sesuai KTP">
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-2.5 ml-1">Email</label>
                                        <input type="email" name="email"
                                            class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 text-sm text-white placeholder-gray-700 outline-none transition-all duration-300 hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon"
                                            placeholder="name@example.com">
                                    </div>

                                    <div>
                                        <label
                                            class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-2.5 ml-1">Nomor
                                            WhatsApp</label>
                                        <div class="relative group">
                                            <span
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 font-bold text-sm pointer-events-none transition-colors duration-300 group-focus-within:text-white">+62</span>
                                            <input type="tel" name="phone_number"
                                                class="w-full bg-black border border-white/5 rounded-xl pl-12 pr-4 py-3.5 text-sm text-white placeholder-gray-700 outline-none transition-all duration-300 hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon"
                                                placeholder="81234567890">
                                        </div>
                                    </div>
                                </div>

                                {{-- KANAN: Data Demografi & Keamanan --}}
                                <div class="space-y-6">

                                    {{-- Baris 1: Gender & Birthday --}}
                                    <div class="grid grid-cols-2 gap-5">
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-2.5 ml-1">Jenis
                                                Kelamin</label>
                                            <select name="gender"
                                                class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 text-sm text-white outline-none transition-all duration-300 cursor-pointer appearance-none hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon">
                                                <option value="" disabled selected>Pilih Gender</option>
                                                <option value="male">Laki-laki</option>
                                                <option value="female">Perempuan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-2.5 ml-1">
                                                Tanggal Lahir
                                            </label>
                                            <div class="relative group">
                                                <input type="date" name="birthday"
                                                    class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 text-sm text-gray-300 outline-none transition-all duration-300 cursor-text hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon appearance-none selection:bg-green-500/30 [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:cursor-pointer">

                                                {{-- Icon Calendar --}}
                                                <div
                                                    class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 group-focus-within:text-green-500 transition-colors duration-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Baris 2: Provinsi & Kota --}}
                                    <div class="grid grid-cols-2 gap-5">
                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-400 uppercase mb-2.5 ml-1">Provinsi</label>
                                            <select name="province" x-model="selectedProvince"
                                                @change="fetchCities()"
                                                class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 text-sm text-white outline-none transition-all duration-300 cursor-pointer appearance-none hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon">
                                                <option value="" disabled selected>Pilih Provinsi</option>
                                                <template x-for="province in provinces" :key="province.id">
                                                    <option :value="province.id" x-text="province.name"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-[11px] font-bold text-gray-400 uppercase mb-2.5 ml-1">
                                                Kota/Kabupaten
                                            </label>
                                            <select name="city" x-model="selectedCity"
                                                :disabled="!selectedProvince || loadingCities"
                                                class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 text-sm text-white outline-none transition-all duration-300 cursor-pointer appearance-none hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon">
                                                <option value="" disabled selected
                                                    x-text="loadingCities ? 'Memuat...' : 'Pilih Kota'"></option>
                                                <template x-for="city in cities" :key="city.id">
                                                    <option :value="city.id" x-text="city.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Baris 3: Password & Confirm --}}
                                    <div class="grid grid-cols-2 gap-5">
                                        <div x-data="{ showPassword: false }">
                                            <label
                                                class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-2.5 ml-1">
                                                Password
                                            </label>
                                            <div class="relative group">
                                                <input :type="showPassword ? 'text' : 'password'" name="password"
                                                    required
                                                    class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 pr-12 text-sm text-white placeholder-gray-700 outline-none transition-all duration-300 hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon"
                                                    :class="role === 'clipper' ?
                                                        'focus:ring-green-500 focus:border-green-500' :
                                                        'focus:ring-neon focus:border-neon'"
                                                    placeholder="••••••••">

                                                <button type="button" @click="showPassword = !showPassword"
                                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-400 transition-colors focus:outline-none">

                                                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg"
                                                        class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>

                                                    <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg"
                                                        class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" style="display: none;">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.822 7.822L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div x-data="{ showConfirm: false }">
                                            <label
                                                class="block text-[11px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-2.5 ml-1">
                                                Konfirmasi Password
                                            </label>
                                            <div class="relative group">
                                                <input :type="showConfirm ? 'text' : 'password'"
                                                    name="password_confirmation" required
                                                    class="w-full bg-black border border-white/5 rounded-xl px-4 py-3.5 pr-12 text-sm text-white placeholder-gray-700 outline-none transition-all duration-300 hover:border-white/10 focus:ring-1 focus:ring-neon focus:border-neon"
                                                    :class="role === 'clipper' ?
                                                        'focus:ring-green-500 focus:border-green-500' :
                                                        'focus:ring-neon focus:border-neon'"
                                                    placeholder="••••••••">

                                                <button type="button" @click="showConfirm = !showConfirm"
                                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-400 transition-colors focus:outline-none z-10">
                                                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg"
                                                        class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>

                                                    <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg"
                                                        class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" style="display: none;">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.822 7.822L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. ACTION AREA --}}
                            <div class="pt-6 border-t border-white/5">
                                {{-- Container Flex untuk tombol bersebelahan --}}
                                <div class="flex flex-col md:flex-row items-center gap-4 mb-4">

                                    {{-- 1. Tombol Daftar Sekarang --}}
                                    <button type="submit"
                                        class="flex-1 w-full font-black text-lg py-4 rounded-2xl transition-all duration-300 transform hover:-translate-y-1 active:scale-[0.98] flex items-center justify-center gap-2 bg-neon hover:bg-neon-400 text-black shadow-[0_0_20px_rgba(34,197,94,0.3)] hover:shadow-[0_0_30px_rgba(34,197,94,0.5)]">
                                        Gabung Sekarang
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                    </button>

                                    {{-- 2. Separator "Atau" --}}
                                    <div class="flex md:flex-col items-center gap-2 md:gap-1 px-2">

                                        <span
                                            class="text-[9px] font-bold text-gray-500 uppercase tracking-widest">Atau</span>

                                    </div>

                                    {{-- 3. Tombol Google --}}
                                    <a href="{{ route('auth.google') }}"
                                        class="flex-1 w-full flex items-center justify-center gap-3 bg-white border border-white/5 hover:border-white/10 text-black font-bold py-4 rounded-2xl transition-all duration-300 transform hover:-translate-y-1">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                                            <path
                                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                                fill="#4285F4" />
                                            <path
                                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                                fill="#34A853" />
                                            <path
                                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"
                                                fill="#FBBC05" />
                                            <path
                                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 12-4.53z"
                                                fill="#EA4335" />
                                        </svg>
                                        Sign Up with Google
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- ========================================== --}}
                        {{-- VIEW 2: PUBLISHER CONTACT --}}
                        {{-- ========================================== --}}
                        <div x-show="role === 'publisher'" style="display: none;"
                            x-transition:enter="transition ease-out duration-500 delay-100"
                            x-transition:enter-start="opacity-0 -translate-x-4"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            x-transition:leave="transition ease-in duration-300 absolute inset-0"
                            x-transition:leave-start="opacity-100 translate-x-0"
                            x-transition:leave-end="opacity-0 translate-x-4"
                            class="w-full flex flex-col items-center justify-center text-center py-10">

                            <div
                                class="w-20 h-20 bg-neon/10 rounded-full flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(216,255,21,0.2)]">
                                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>

                            <h3 class="text-2xl font-black text-white mb-3">Kemitraan Brand/Publisher</h3>
                            <p class="text-gray-400 text-sm max-w-md mx-auto mb-8 leading-relaxed">
                                Untuk menjaga kualitas ekosistem dan hak cipta, pendaftaran akun Brand/Publisher Clipkeo
                                dilakukan melalui verifikasi manual. Silakan hubungi tim kemitraan kami.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4 w-full max-w-md">
                                <a href="https://wa.me/6281234567890?text=Halo%20Tim%20Clipkeo,%20saya%20tertarik%20menjadi%20Publisher."
                                    target="_blank"
                                    class="flex-1 bg-green-500 text-black font-bold py-3.5 rounded-xl transition-all duration-300 transform hover:-translate-y-1 shadow-[0_0_20px_rgba(216,255,21,0.2)] flex items-center justify-center gap-2">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 175.216 175.552">
                                        <defs>
                                            <linearGradient id="b" x1="85.915" x2="86.535"
                                                y1="32.567" y2="137.092" gradientUnits="userSpaceOnUse">
                                                <stop offset="0" stop-color="#57d163" />
                                                <stop offset="1" stop-color="#23b33a" />
                                            </linearGradient>
                                            <filter id="a" width="1.115" height="1.114" x="-.057"
                                                y="-.057" color-interpolation-filters="sRGB">
                                                <feGaussianBlur stdDeviation="3.531" />
                                            </filter>
                                        </defs>
                                        <path fill="#b3b3b3"
                                            d="m54.532 138.45 2.235 1.324c9.387 5.571 20.15 8.518 31.126 8.523h.023c33.707 0 61.139-27.426 61.153-61.135.006-16.335-6.349-31.696-17.895-43.251A60.75 60.75 0 0 0 87.94 25.983c-33.733 0-61.166 27.423-61.178 61.13a60.98 60.98 0 0 0 9.349 32.535l1.455 2.312-6.179 22.558zm-40.811 23.544L24.16 123.88c-6.438-11.154-9.825-23.808-9.821-36.772.017-40.556 33.021-73.55 73.578-73.55 19.681.01 38.154 7.669 52.047 21.572s21.537 32.383 21.53 52.037c-.018 40.553-33.027 73.553-73.578 73.553h-.032c-12.313-.005-24.412-3.094-35.159-8.954zm0 0"
                                            filter="url(#a)" />
                                        <path fill="#fff"
                                            d="m12.966 161.238 10.439-38.114a73.42 73.42 0 0 1-9.821-36.772c.017-40.556 33.021-73.55 73.578-73.55 19.681.01 38.154 7.669 52.047 21.572s21.537 32.383 21.53 52.037c-.018 40.553-33.027 73.553-73.578 73.553h-.032c-12.313-.005-24.412-3.094-35.159-8.954z" />
                                        <path fill="url(#linearGradient1780)"
                                            d="M87.184 25.227c-33.733 0-61.166 27.423-61.178 61.13a60.98 60.98 0 0 0 9.349 32.535l1.455 2.312-6.179 22.559 23.146-6.069 2.235 1.324c9.387 5.571 20.15 8.518 31.126 8.524h.023c33.707 0 61.14-27.426 61.153-61.135a60.75 60.75 0 0 0-17.895-43.251 60.75 60.75 0 0 0-43.235-17.929z" />
                                        <path fill="url(#b)"
                                            d="M87.184 25.227c-33.733 0-61.166 27.423-61.178 61.13a60.98 60.98 0 0 0 9.349 32.535l1.455 2.313-6.179 22.558 23.146-6.069 2.235 1.324c9.387 5.571 20.15 8.517 31.126 8.523h.023c33.707 0 61.14-27.426 61.153-61.135a60.75 60.75 0 0 0-17.895-43.251 60.75 60.75 0 0 0-43.235-17.928z" />
                                        <path fill="#fff" fill-rule="evenodd"
                                            d="M68.772 55.603c-1.378-3.061-2.828-3.123-4.137-3.176l-3.524-.043c-1.226 0-3.218.46-4.902 2.3s-6.435 6.287-6.435 15.332 6.588 17.785 7.506 19.013 12.718 20.381 31.405 27.75c15.529 6.124 18.689 4.906 22.061 4.6s10.877-4.447 12.408-8.74 1.532-7.971 1.073-8.74-1.685-1.226-3.525-2.146-10.877-5.367-12.562-5.981-2.91-.919-4.137.921-4.746 5.979-5.819 7.206-2.144 1.381-3.984.462-7.76-2.861-14.784-9.124c-5.465-4.873-9.154-10.891-10.228-12.73s-.114-2.835.808-3.751c.825-.824 1.838-2.147 2.759-3.22s1.224-1.84 1.836-3.065.307-2.301-.153-3.22-4.032-10.011-5.666-13.647" />
                                    </svg>
                                    Chat WhatsApp
                                </a>
                                <a href="#" target="_blank"
                                    class="flex-1 bg-black border border-white/20 hover:border-white/50 text-white font-bold py-3.5 rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Isi Formulir
                                </a>
                            </div>
                        </div>

                    </div> {{-- END DYNAMIC CONTENT WRAPPER --}}

                    {{-- Footer Login Link (Tetap tampil di bawah apa pun rolenya) --}}
                    <div class="mt-6 pt-6 border-t border-white/5 text-center relative z-20">
                        <p class="text-xs text-gray-500">Sudah punya akun?
                            <button type="button" @click="activeModal = 'login'"
                                class="font-bold text-white hover:text-neon transition">Masuk disini</button>
                        </p>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>
