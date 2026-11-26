<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Role - ClipSquad</title>

    {{-- FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- TAILWIND & ALPINE --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Outfit', 'sans-serif'] } } }
        }
    </script>
    
    <style>
        body { background-color: #050505; color: white; overflow: hidden; }
        
        /* Background Pattern */
        .premium-bg {
            background-size: 50px 50px;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            mask-image: radial-gradient(circle at center top, black 40%, transparent 100%);
        }
        
        .spotlight {
            position: absolute;
            top: -200px;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            filter: blur(80px);
            z-index: 0;
        }
    </style>
</head>

<body class="antialiased selection:bg-red-500 selection:text-white relative h-screen w-full">

    {{-- ======================================================================= --}}
    {{-- LAYER 1: FAKE LANDING PAGE BACKGROUND (BLURRED)                         --}}
    {{-- ======================================================================= --}}
    <div class="absolute inset-0 z-0 pointer-events-none filter blur-sm opacity-60 scale-105">
        <div class="absolute inset-0 premium-bg opacity-50"></div>
        <div class="absolute inset-0 spotlight"></div>

        {{-- Fake Navbar --}}
        <div class="max-w-7xl mx-auto px-6 h-24 flex justify-between items-center opacity-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-800 rounded-xl flex items-center justify-center border border-red-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <span class="font-bold text-2xl tracking-tight text-white">Clipkeo</span>
            </div>
        </div>

        {{-- Fake Hero Content --}}
        <div class="flex flex-col items-center justify-center pt-20 text-center px-6">
            <h1 class="text-6xl md:text-9xl font-extrabold tracking-tighter mb-8 leading-none opacity-40">
                Make Content. <br>
                <span class="text-gray-600">Get Paid.</span>
            </h1>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- LAYER 2: DARK OVERLAY                                                   --}}
    {{-- ======================================================================= --}}
    <div class="absolute inset-0 bg-black/70 z-10"></div>

    {{-- ======================================================================= --}}
    {{-- LAYER 3: COMPACT MODAL                                                  --}}
    {{-- ======================================================================= --}}
    <div class="relative z-20 min-h-screen flex items-center justify-center p-4" x-data="{ selectedRole: null }">
        
        {{-- 
            SIZE CHANGE: 
            Login biasanya 'max-w-md' (sekitar 450px).
            Disini saya pakai 'max-w-2xl' (sekitar 670px) agar muat 2 kotak tapi tidak kegedean.
        --}}
        <div class="relative w-full max-w-2xl bg-[#050505] border border-white/10 rounded-[2rem] shadow-[0_0_60px_rgba(0,0,0,0.6)] overflow-hidden animate-fade-in-up">

            {{-- Dekorasi Glow Halus --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-red-600/5 rounded-full blur-[80px] pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-600/5 rounded-full blur-[80px] pointer-events-none"></div>

            <div class="p-8">
                
                {{-- Modal Header (Compact) --}}
                <div class="text-center mb-6">
                    <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight mb-2">Final Step: Who are you?</h2>
                    <p class="text-sm text-gray-400">Pilih peran utama Anda untuk menyesuaikan dashboard.</p>
                </div>

                <form method="POST" action="{{ route('role.save') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="role" :value="selectedRole">

                    {{-- GRID COMPACT (2 Kolom Rapat) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">

                        {{-- === OPTION 1: PUBLISHER (BRAND) === --}}
                        <div @click="selectedRole = 'publisher'"
                             class="group cursor-pointer relative p-5 rounded-2xl border transition-all duration-200 flex flex-col items-center text-center gap-3 hover:bg-white/[0.03]"
                             :class="selectedRole === 'publisher' 
                                ? 'bg-red-900/10 border-red-500 shadow-[inset_0_0_20px_rgba(220,38,38,0.2)]' 
                                : 'bg-transparent border-white/10 hover:border-white/30'">
                            
                            {{-- Checkmark Icon (Kecil di pojok) --}}
                            <div x-show="selectedRole === 'publisher'" class="absolute top-3 right-3 text-red-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            </div>

                            {{-- Icon --}}
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors duration-200"
                                 :class="selectedRole === 'publisher' ? 'bg-red-500 text-white' : 'bg-white/10 text-gray-400 group-hover:bg-white/20 group-hover:text-red-500'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-bold text-white leading-tight">I am a Publisher</h3>
                                <p class="text-[10px] font-bold text-red-500 uppercase tracking-widest mt-1">For Brands</p>
                            </div>
                            <p class="text-xs text-gray-400 leading-snug px-2">
                                Buat misi, promosi produk, dan bayar kreator.
                            </p>
                        </div>

                        {{-- === OPTION 2: CLIPPER (CREATOR) === --}}
                        <div @click="selectedRole = 'clipper'"
                             class="group cursor-pointer relative p-5 rounded-2xl border transition-all duration-200 flex flex-col items-center text-center gap-3 hover:bg-white/[0.03]"
                             :class="selectedRole === 'clipper' 
                                ? 'bg-green-900/10 border-green-500 shadow-[inset_0_0_20px_rgba(34,197,94,0.2)]' 
                                : 'bg-transparent border-white/10 hover:border-white/30'">
                            
                            {{-- Checkmark Icon --}}
                            <div x-show="selectedRole === 'clipper'" class="absolute top-3 right-3 text-green-500">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            </div>

                            {{-- Icon --}}
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors duration-200"
                                 :class="selectedRole === 'clipper' ? 'bg-green-500 text-white' : 'bg-white/10 text-gray-400 group-hover:bg-white/20 group-hover:text-green-500'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>

                            <div>
                                <h3 class="text-lg font-bold text-white leading-tight">I am a Clipper</h3>
                                <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest mt-1">For Creators</p>
                            </div>
                            <p class="text-xs text-gray-400 leading-snug px-2">
                                Edit video, ikuti misi viral, dan dapatkan uang.
                            </p>
                        </div>

                    </div>

                    {{-- Action Button --}}
                    <button type="submit" 
                            :disabled="!selectedRole"
                            :class="!selectedRole ? 'opacity-50 cursor-not-allowed bg-white/10 text-gray-500' : 'bg-white text-black hover:bg-gray-200 transform hover:scale-[1.01] shadow-lg'"
                            class="w-full py-3.5 rounded-xl font-black text-base transition-all duration-300">
                        <span x-text="!selectedRole ? 'Select a Role' : (selectedRole === 'publisher' ? 'Continue as Publisher' : 'Start Earning as Clipper')"></span>
                    </button>

                </form>
            </div>
        </div>
    </div>

    {{-- CSS Animasi --}}
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
    </style>

</body>
</html>