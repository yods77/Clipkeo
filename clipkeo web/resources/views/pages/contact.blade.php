<x-app-layout>
    <div class="pt-32 pb-20 bg-gray-100 min-h-screen relative overflow-hidden">
        
        {{-- Background Blobs --}}
        <div class="absolute top-20 left-[-100px] w-[500px] h-[500px] bg-purple-200/40 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-20 right-[-100px] w-[500px] h-[500px] bg-indigo-200/40 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                
                {{-- Kolom Kiri: Info Text --}}
                <div class="lg:sticky lg:top-32">
                    <span class="text-indigo-600 font-bold tracking-wider uppercase text-xs bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Hubungi Kami</span>
                    <h1 class="text-4xl md:text-5xl font-black text-gray-900 mt-4 mb-6 tracking-tight leading-tight">
                        Mari Berdiskusi Tentang <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Ide Anda.</span>
                    </h1>
                    <p class="text-gray-500 text-lg mb-8 leading-relaxed">
                        Punya pertanyaan teknis? Ingin bermitra? Atau sekadar ingin menyapa? Tim kami siap mendengarkan.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center justify-center text-indigo-600 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Email</h4>
                                <p class="text-gray-500 text-sm">support@clipkeo.com</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center justify-center text-indigo-600 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Kantor</h4>
                                <p class="text-gray-500 text-sm">Jakarta Selatan, Indonesia</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Form (Menggunakan Controller yang sudah ada) --}}
                <div class="bg-white rounded-[2rem] p-8 shadow-2xl shadow-gray-200 border border-white/50 relative overflow-hidden">
                    
                    {{-- Alert Success --}}
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-5 relative z-10">
                        @csrf
                        <div class="grid grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Nama</label>
                                <input type="text" name="name" required placeholder="Nama Anda"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role</label>
                                <select name="role" required
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer">
                                    <option value="Clipper">Saya Clipper</option>
                                    <option value="Publisher">Saya Publisher</option>
                                    <option value="Other">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Email</label>
                            <input type="email" name="email" required placeholder="email@anda.com"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400">
                        </div>
                    
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Pesan</label>
                            <textarea name="message" rows="5" required placeholder="Tulis pesan anda di sini..."
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder-gray-400 resize-none"></textarea>
                        </div>
                    
                        <button type="submit"
                            class="w-full py-4 bg-gray-900 text-white font-bold rounded-xl hover:bg-indigo-600 transition-all transform hover:scale-[1.01] shadow-lg flex items-center justify-center gap-2 group">
                            Kirim Pesan
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>