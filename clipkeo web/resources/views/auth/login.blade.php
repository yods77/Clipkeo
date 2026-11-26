<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - ClipSquad</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Outfit', 'sans-serif'] } } }
        }
    </script>
</head>
<body class="antialiased bg-gray-50 text-gray-900 selection:bg-indigo-500 selection:text-white">

    <div class="min-h-screen flex">
        
        {{-- KOLOM KIRI: DEKORASI & BRANDING (Gradient Indigo) --}}
        <div class="hidden lg:flex w-1/2 relative overflow-hidden bg-indigo-600 items-center justify-center">
            {{-- Background Pattern --}}
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-700"></div>
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>
            
            {{-- Dekorasi Abstrak --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl -ml-16 -mb-16"></div>

            <div class="relative z-10 text-center px-12">
                <div class="mb-8 inline-flex p-4 bg-white/10 backdrop-blur-lg rounded-[2rem] shadow-2xl border border-white/20">
                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h2 class="text-4xl font-black text-white mb-4 tracking-tight">Craft Your Viral.</h2>
                <p class="text-indigo-100 text-lg leading-relaxed font-light">
                    Platform #1 untuk Clipper & Streamer mengelola konten dan meraih cuan tanpa batas.
                </p>
                
                {{-- Floating Card Decoration --}}
                <div class="mt-12 bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl flex items-center gap-4 max-w-xs mx-auto transform rotate-3 hover:rotate-0 transition duration-500">
                    <div class="w-10 h-10 bg-green-400 rounded-full flex items-center justify-center text-green-900 font-bold shadow-lg">
                        $
                    </div>
                    <div class="text-left">
                        <p class="text-xs text-indigo-100 uppercase tracking-wider">New Earnings</p>
                        <p class="text-white font-bold">+ Rp 1.500.000</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: FORM LOGIN (White Clean) --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md space-y-8">
                
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Selamat Datang! 👋</h2>
                    <p class="text-gray-500 mt-2">Masuk untuk mengelola misi dan saldo Anda.</p>
                </div>

                {{-- TOMBOL GOOGLE LOGIN --}}
                {{-- Pastikan route 'auth.google' sudah dibuat di web.php --}}
                <a href="{{ route('auth.google') }}" class="flex items-center justify-center w-full px-4 py-3.5 border border-gray-200 rounded-2xl hover:bg-gray-50 hover:border-gray-300 transition duration-300 gap-3 group">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M23.766 12.2764C23.766 11.4607 23.6999 10.6406 23.5588 9.83807H12.24V14.4591H18.7217C18.4528 15.9494 17.5885 17.2678 16.323 18.1056V21.1039H20.19C22.4608 19.0139 23.766 15.9274 23.766 12.2764Z" fill="#4285F4"/>
                        <path d="M12.2401 24.0008C15.4766 24.0008 18.2059 22.9382 20.1945 21.1039L16.3276 18.1055C15.2517 18.8375 13.8627 19.252 12.2445 19.252C9.11388 19.252 6.45946 17.1399 5.50705 14.3003H1.5166V17.3912C3.55371 21.4434 7.7029 24.0008 12.2401 24.0008Z" fill="#34A853"/>
                        <path d="M5.50253 14.3003C5.00236 12.8099 5.00236 11.1961 5.50253 9.70575V6.61481H1.51649C-0.18551 10.0056 -0.18551 14.0004 1.51649 17.3912L5.50253 14.3003Z" fill="#FBBC05"/>
                        <path d="M12.2401 4.74966C13.9509 4.7232 15.6044 5.36697 16.8434 6.54867L20.2695 3.12262C18.1001 1.0855 15.2208 -0.034466 12.2401 0.000808666C7.7029 0.000808666 3.55371 2.55822 1.5166 6.61481L5.50264 9.70575C6.45064 6.86173 9.10947 4.74966 12.2401 4.74966Z" fill="#EA4335"/>
                    </svg>
                    <span class="text-sm font-bold text-gray-600 group-hover:text-gray-900">Masuk dengan Google</span>
                </a>

                {{-- DIVIDER --}}
                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="flex-shrink-0 mx-4 text-xs font-bold text-gray-400 uppercase tracking-widest">Atau via Email</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                {{-- ALERT ERROR --}}
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    {{-- Email Input --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Email Address</label>
                        <input id="email" name="email" type="email" required 
                            class="block w-full px-5 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition outline-none font-medium" 
                            placeholder="name@company.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Password Input --}}
                    <div>
                        <div class="flex justify-between mb-2 ml-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">Lupa Password?</a>
                            @endif
                        </div>
                        <input id="password" name="password" type="password" required 
                            class="block w-full px-5 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white transition outline-none font-medium" 
                            placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <label class="flex items-center cursor-pointer group">
                            <input id="remember_me" name="remember" type="checkbox" 
                                class="rounded-lg border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer w-5 h-5 transition">
                            <span class="ml-3 text-sm text-gray-500 font-medium group-hover:text-gray-700 transition">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="w-full py-4 rounded-2xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-300 shadow-xl shadow-indigo-200 transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2">
                        Masuk Dashboard
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-8">
                    Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline">Daftar Sekarang</a>
                </p>
                
            </div>
            
            <div class="absolute bottom-6 w-full text-center lg:text-left lg:pl-8 text-[10px] text-gray-400 uppercase tracking-widest">&copy; 2026 ClipSquad Inc.</div>
        </div>
    </div>
</body>
</html>