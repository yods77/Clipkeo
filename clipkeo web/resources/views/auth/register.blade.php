<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - ClipSquad</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #020617;
            color: white;
        }

        .premium-bg {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }

        .glass-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            transition: all 0.3s ease;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: #ef4444;
            outline: none;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.1);
        }

        /* Custom Radio Card */
        .role-radio:checked+div {
            border-color: #ef4444;
            background: linear-gradient(to bottom right, rgba(239, 68, 68, 0.1), transparent);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }

        .role-radio:checked+div .check-icon {
            display: block;
        }

        .role-radio:checked+div svg.icon-role {
            color: #ef4444;
        }

        /* Glass Card for Visuals */
        .glass-card {
            background: rgba(20, 20, 20, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        /* Animations */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float 5s ease-in-out infinite;
            animation-delay: 2s;
        }
    </style>
</head>

<body class="antialiased selection:bg-red-500 selection:text-white">

    <div class="min-h-screen flex flex-row-reverse">

        <div class="hidden lg:flex w-1/2 relative overflow-hidden items-center justify-center bg-[#050505]">
    
    {{-- Background Effects --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-orange-600/20 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute inset-0 premium-bg opacity-30"></div>

    {{-- WRAPPER LAPTOP (Container Utama) --}}
    <div class="relative z-10 transform scale-90">
        
        {{-- 1. LAYAR LAPTOP --}}
        <div class="relative mx-auto w-[520px] h-[320px] bg-gray-900 rounded-t-xl border-[6px] border-gray-800 shadow-2xl overflow-hidden transform -rotate-1 hover:rotate-0 transition-transform duration-700 group">
            
            {{-- Kamera Webcam --}}
            <div class="absolute top-2 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-gray-700 rounded-full z-50"></div>

            {{-- ISI LAYAR (DASHBOARD MOCKUP) --}}
            <div class="w-full h-full bg-[#0F172A] flex text-white overflow-hidden">
                
                {{-- Sidebar Mockup --}}
                <div class="w-16 bg-gray-900/80 border-r border-white/5 flex flex-col items-center py-5 gap-4">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-600 to-orange-600 mb-2"></div>
                    <div class="w-6 h-6 rounded bg-white/10"></div>
                    <div class="w-6 h-6 rounded bg-white/10"></div>
                    <div class="w-6 h-6 rounded bg-white/10"></div>
                    <div class="mt-auto w-6 h-6 rounded-full bg-gray-700"></div>
                </div>

                {{-- Main Content Mockup --}}
                <div class="flex-1 p-5">
                    {{-- Header --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <div class="h-2 w-24 bg-white/20 rounded mb-1"></div>
                            <div class="h-4 w-32 bg-white/10 rounded"></div>
                        </div>
                        <div class="flex gap-2">
                            <div class="h-8 w-24 rounded bg-red-500/20 border border-red-500/30 flex items-center justify-center">
                                <div class="h-2 w-12 bg-red-500/50 rounded"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Cards Grid --}}
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Card 1: Earnings --}}
                        <div class="h-28 rounded-xl bg-white/5 border border-white/5 p-4 relative overflow-hidden">
                            <div class="flex justify-between items-start mb-2">
                                <div class="h-2 w-16 bg-white/20 rounded"></div>
                                <div class="h-6 w-6 rounded bg-green-500/20 flex items-center justify-center text-green-500 text-xs">$</div>
                            </div>
                            <div class="text-2xl font-bold text-white mb-1">Rp 12.5M</div>
                            <div class="text-[10px] text-green-400">+15% this month</div>
                            {{-- Chart Decoration --}}
                            <div class="absolute bottom-0 left-0 right-0 h-10 bg-gradient-to-t from-green-500/10 to-transparent">
                                <svg class="w-full h-full text-green-500/30" fill="none" stroke="currentColor" viewBox="0 0 100 20" preserveAspectRatio="none">
                                    <path d="M0 20 L20 15 L40 18 L60 5 L80 12 L100 0 V20 H0 Z" fill="currentColor" />
                                </svg>
                            </div>
                        </div>

                        {{-- Card 2: Stats --}}
                        <div class="space-y-3">
                            <div class="h-12 rounded-xl bg-white/5 border border-white/5 flex items-center px-3 gap-3">
                                <div class="w-8 h-8 rounded bg-orange-500/20"></div>
                                <div class="flex-1">
                                    <div class="h-1.5 w-12 bg-white/20 rounded mb-1"></div>
                                    <div class="h-1.5 w-8 bg-white/10 rounded"></div>
                                </div>
                            </div>
                            <div class="h-12 rounded-xl bg-white/5 border border-white/5 flex items-center px-3 gap-3">
                                <div class="w-8 h-8 rounded bg-blue-500/20"></div>
                                <div class="flex-1">
                                    <div class="h-1.5 w-12 bg-white/20 rounded mb-1"></div>
                                    <div class="h-1.5 w-8 bg-white/10 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. BODY LAPTOP (KEYBOARD BASE) --}}
        <div class="relative mx-auto w-[600px] h-[24px] bg-gray-800 rounded-b-2xl shadow-2xl -mt-1 transform -rotate-1 group-hover:rotate-0 transition-transform duration-700 border-t border-gray-700">
            {{-- Lekukan trackpad --}}
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-3 bg-gray-700/50 rounded-b-lg"></div>
        </div>

        {{-- Floating Elements (Diatur ulang posisinya) --}}
        <div class="absolute bottom-10 -right-8 glass-card p-3 rounded-xl w-48 animate-float-delayed z-20">
            <div class="flex justify-between items-center mb-2">
                <span class="text-[10px] text-gray-400 font-mono">Video Render</span>
                <span class="text-[10px] text-green-400 font-bold">98%</span>
            </div>
            <div class="w-full h-1.5 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-green-500 w-[98%]"></div>
            </div>
        </div>

        <div class="absolute -top-6 -left-8 glass-card px-4 py-3 rounded-xl flex items-center gap-3 animate-float z-20">
            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-xs">
                CS
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide">New Publisher</p>
                <p class="text-sm font-bold text-white">ClipSquad Official</p>
            </div>
        </div>

    </div>
    
    <div class="absolute bottom-10 text-center">
        <h2 class="text-2xl font-bold text-white mb-1">Pro Dashboard.</h2>
        <p class="text-xs text-gray-500">Pantau performa konten & penghasilanmu.</p>
    </div>
</div>

        <div class="w-full lg:w-1/2 flex items-center justify-center relative premium-bg bg-[#020617]">
            <div
                class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-orange-600/10 rounded-full blur-[120px] pointer-events-none">
            </div>

            <div class="w-full max-w-md px-8 py-12 relative z-10">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">Create Account</h2>
                    <p class="text-sm text-gray-400">Pilih peran dan mulai perjalananmu.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="role" value="clipper" class="role-radio sr-only" checked>
                            <div
                                class="border border-white/10 bg-white/5 rounded-2xl p-4 text-center hover:bg-white/10 transition-all h-full">
                                <div
                                    class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center mb-3 mx-auto">
                                    <svg class="w-5 h-5 text-gray-400 icon-role" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-white text-sm">Clipper</h3>
                                <p class="text-[10px] text-gray-500 mt-1">Cari Cuan dari Video</p>
                                <div class="check-icon hidden absolute top-3 right-3 text-red-500"><svg class="w-4 h-4"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg></div>
                            </div>
                        </label>
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="role" value="publisher" class="role-radio sr-only">
                            <div
                                class="border border-white/10 bg-white/5 rounded-2xl p-4 text-center hover:bg-white/10 transition-all h-full">
                                <div
                                    class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center mb-3 mx-auto">
                                    <svg class="w-5 h-5 text-gray-400 icon-role" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                                        </path>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-white text-sm">Publisher</h3>
                                <p class="text-[10px] text-gray-500 mt-1">Buat Campaign Viral</p>
                                <div class="check-icon hidden absolute top-3 right-3 text-red-500"><svg
                                        class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg></div>
                            </div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama
                            Lengkap</label>
                        <input id="name" name="name" type="text" required
                            class="glass-input block w-full px-4 py-3 rounded-xl" placeholder="Full Name">
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Email</label>
                        <input id="email" name="email" type="email" required
                            class="glass-input block w-full px-4 py-3 rounded-xl" placeholder="email@example.com">
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Password</label>
                            <input id="password" name="password" type="password" required
                                class="glass-input block w-full px-4 py-3 rounded-xl" placeholder="••••••">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Confirm</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="glass-input block w-full px-4 py-3 rounded-xl" placeholder="••••••">
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2 col-span-2" />
                    </div>

                    <button type="submit"
                        class="w-full py-4 rounded-xl font-bold text-white bg-gradient-to-r from-red-600 to-orange-600 hover:scale-[1.02] transition-transform shadow-[0_0_20px_rgba(220,38,38,0.3)] mt-4">
                        Buat Akun
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-8">
                    Sudah punya akun? <a href="{{ route('login') }}"
                        class="font-bold text-red-500 hover:text-red-400">Login</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>
