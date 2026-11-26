<x-app-layout>
    <div class="bg-gray-100 min-h-screen overflow-hidden">

        {{-- 
        |--------------------------------------------------------------------------
        | SECTION 1: HERO & VISION
        |--------------------------------------------------------------------------
        --}}
        <div class="pt-32 pb-20 relative">
            {{-- Animated Background Blobs --}}
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-purple-200/30 rounded-full blur-[120px] -mr-40 -mt-20 animate-pulse" style="animation-duration: 4s;"></div>
            <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-indigo-200/30 rounded-full blur-[100px] -ml-20 animate-pulse" style="animation-duration: 6s;"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
                <span class="inline-block py-1 px-3 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-bold uppercase tracking-widest mb-6 shadow-sm">
                    Our Story
                </span>
                <h1 class="text-5xl md:text-7xl font-black text-gray-900 tracking-tighter mb-6 leading-tight">
                    Membangun Ekosistem <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500">
                        Viral Masa Depan.
                    </span>
                </h1>
                <p class="text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed mb-10">
                    Clipkeo bukan sekadar platform. Kami adalah jembatan yang menghubungkan Brand visioner dengan ribuan Kreator berbakat untuk menciptakan gelombang viralitas yang tak terbendung.
                </p>

                {{-- Image Grid Hero --}}
                <div class="grid grid-cols-3 gap-4 max-w-4xl mx-auto mt-12 opacity-90">
                    <div class="space-y-4 translate-y-8">
                        <div class="h-40 bg-gray-200 rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-500">
                            <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=80&w=500" class="w-full h-full object-cover">
                        </div>
                        <div class="h-56 bg-gray-200 rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-500">
                            <img src="https://images.unsplash.com/photo-1542204165-65bf26472b9b?auto=format&fit=crop&q=80&w=500" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="h-56 bg-gray-200 rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-500">
                            <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=500" class="w-full h-full object-cover">
                        </div>
                        <div class="h-40 bg-gray-200 rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-500">
                            <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&q=80&w=500" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="space-y-4 translate-y-8">
                        <div class="h-40 bg-gray-200 rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-500">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=500" class="w-full h-full object-cover">
                        </div>
                        <div class="h-56 bg-gray-200 rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-500">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=500" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
        |--------------------------------------------------------------------------
        | SECTION 2: STATS (NUMBERS)
        |--------------------------------------------------------------------------
        --}}
        <div class="bg-white py-20 border-y border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-gray-100">
                    <div class="group">
                        <div class="text-4xl md:text-5xl font-black text-gray-900 mb-2 group-hover:text-indigo-600 transition duration-300">10K+</div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Clipper Bergabung</div>
                    </div>
                    <div class="group">
                        <div class="text-4xl md:text-5xl font-black text-gray-900 mb-2 group-hover:text-indigo-600 transition duration-300">500+</div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Brand Partner</div>
                    </div>
                    <div class="group">
                        <div class="text-4xl md:text-5xl font-black text-gray-900 mb-2 group-hover:text-indigo-600 transition duration-300">Rp 2M+</div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Total Payout</div>
                    </div>
                    <div class="group">
                        <div class="text-4xl md:text-5xl font-black text-gray-900 mb-2 group-hover:text-indigo-600 transition duration-300">50M+</div>
                        <div class="text-sm text-gray-500 font-bold uppercase tracking-wider">Total Views</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
        |--------------------------------------------------------------------------
        | SECTION 3: CORE VALUES (CARDS)
        |--------------------------------------------------------------------------
        --}}
        <div class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-black text-gray-900 mb-4">DNA Clipkeo</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Nilai-nilai inti yang menggerakkan kami untuk terus berinovasi.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Card 1 --}}
                <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-100 border border-gray-100 group hover:-translate-y-2 transition duration-300">
                    <div class="w-14 h-14 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-600 mb-6 group-hover:scale-110 transition duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Kecepatan & Dampak</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Kami percaya bahwa di era digital, momentum adalah segalanya. Sistem kami dirancang untuk memviralkan konten dalam hitungan jam, bukan hari.
                    </p>
                </div>

                {{-- Card 2 --}}
                <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-100 border border-gray-100 group hover:-translate-y-2 transition duration-300 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full blur-2xl -mr-16 -mt-16"></div>
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition duration-300 relative z-10">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 relative z-10">Komunitas Kreator</h3>
                    <p class="text-gray-500 text-sm leading-relaxed relative z-10">
                        Clipper adalah jantung platform kami. Kami berkomitmen untuk memberikan reward yang adil dan transparan bagi setiap karya kreatif.
                    </p>
                </div>

                {{-- Card 3 --}}
                <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-gray-100 border border-gray-100 group hover:-translate-y-2 transition duration-300">
                    <div class="w-14 h-14 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 mb-6 group-hover:scale-110 transition duration-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Transparansi Data</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">
                        Tidak ada angka tersembunyi. Brand mendapatkan analitik real-time, Clipper mendapatkan bukti bayar yang jelas. Trust is our currency.
                    </p>
                </div>
            </div>
        </div>

        {{-- 
        |--------------------------------------------------------------------------
        | SECTION 4: CTA (JOIN THE MOVEMENT)
        |--------------------------------------------------------------------------
        --}}
        <div class="pb-20 px-4">
            <div class="max-w-5xl mx-auto bg-[#09090b] rounded-[3rem] p-12 md:p-20 text-center relative overflow-hidden group">
                {{-- Background Effects --}}
                <div class="absolute top-0 left-0 w-full h-full opacity-30 group-hover:scale-110 transition duration-[2s]">
                    <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-indigo-600/40 blur-[100px] rounded-full"></div>
                    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-purple-600/40 blur-[100px] rounded-full"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="text-3xl md:text-5xl font-black text-white mb-6 tracking-tight">Siap Bergabung dengan <br> Revolusi Ini?</h2>
                    <p class="text-gray-400 mb-10 max-w-xl mx-auto">
                        Jangan hanya jadi penonton. Jadilah bagian dari ekosistem konten kreator terbesar di Indonesia hari ini.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('register') }}" class="px-8 py-4 bg-white text-black font-bold rounded-xl hover:bg-indigo-50 transition transform hover:scale-105 shadow-lg shadow-white/10">
                            Daftar Sebagai Clipper
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 bg-transparent border border-gray-700 text-white font-bold rounded-xl hover:bg-white/5 transition">
                            Untuk Brand / Publisher
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>