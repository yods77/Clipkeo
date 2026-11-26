<x-app-layout>
    <div class="pt-32 pb-20 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Header --}}
            <div class="text-center mb-16">
                <span class="text-indigo-600 font-bold tracking-wider uppercase text-xs bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">Pusat Bantuan</span>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 mt-4 mb-4 tracking-tight">Frequently Asked Questions</h1>
                <p class="text-gray-500 max-w-2xl mx-auto">Jawaban untuk pertanyaan yang paling sering diajukan oleh Clipper dan Brand.</p>
            </div>

            {{-- Accordion Container --}}
            <div class="space-y-4">
                @php
                    $faqs = [
                        [
                            'q' => 'Apa itu Clipkeo?',
                            'a' => 'Clipkeo adalah platform yang menghubungkan Brand (Publisher) dengan konten kreator (Clipper) untuk membuat video pendek viral di TikTok, Reels, dan Shorts.'
                        ],
                        [
                            'q' => 'Bagaimana cara mendapatkan uang sebagai Clipper?',
                            'a' => 'Daftar sebagai Clipper, cari misi yang aktif, download aset videonya, edit sekreatif mungkin, upload ke sosmed kamu, dan submit link videonya di sini. Jika views mencapai target, kamu akan dibayar.'
                        ],
                        [
                            'q' => 'Apakah ada biaya pendaftaran?',
                            'a' => 'Tidak ada! Pendaftaran 100% GRATIS untuk Clipper maupun Publisher.'
                        ],
                        [
                            'q' => 'Bagaimana sistem pembayaran bekerja?',
                            'a' => 'Publisher mendepositkan dana ke escrow Clipkeo. Setelah misi selesai dan diverifikasi, dana akan otomatis masuk ke dompet Clipper dan bisa ditarik ke Rekening Bank atau E-Wallet.'
                        ],
                        [
                            'q' => 'Apa syarat video agar diterima?',
                            'a' => 'Video harus original (milik sendiri), tidak menggunakan bot views, sesuai dengan brief brand, dan mencapai minimum views yang ditentukan dalam misi.'
                        ],
                    ];
                @endphp

                @foreach($faqs as $index => $faq)
                <div x-data="{ open: false }" class="bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition duration-300 overflow-hidden">
                    <button @click="open = !open" class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none">
                        <span class="font-bold text-gray-900 text-lg">{{ $faq['q'] }}</span>
                        <span :class="{'rotate-180': open}" class="transform transition-transform duration-300 text-indigo-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </button>
                    <div x-show="open" x-collapse style="display: none;">
                        <div class="px-6 pb-6 text-gray-500 leading-relaxed border-t border-gray-50 pt-4">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- CTA --}}
            <div class="mt-12 text-center bg-indigo-600 rounded-3xl p-8 sm:p-12 shadow-xl shadow-indigo-200 relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-2xl font-bold text-white mb-2">Masih punya pertanyaan?</h3>
                    <p class="text-indigo-100 mb-6">Tim support kami siap membantu anda 24/7.</p>
                    <a href="{{ route('pages.contact') }}" class="inline-block bg-white text-indigo-600 font-bold px-8 py-3 rounded-xl hover:bg-gray-50 transition shadow-lg">Hubungi Kami</a>
                </div>
                {{-- Decor --}}
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-black/10 rounded-full blur-3xl -ml-16 -mb-16 pointer-events-none"></div>
            </div>
        </div>
    </div>
</x-app-layout>