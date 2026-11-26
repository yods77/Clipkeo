<x-app-layout>
    {{-- 
    |--------------------------------------------------------------------------
    | HEADER PAGE (CLEAN STYLE)
    |--------------------------------------------------------------------------
    | Bagian header halaman dengan judul dan tombol kembali.
    --}}

    <div class="pt-32 pb-12 bg-#f3f4f6 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- 
            |--------------------------------------------------------------------------
            | NAVIGATION BAR (TITLE & BACK BUTTON)
            |--------------------------------------------------------------------------
            --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Buat Campaign</h2>
                    <p class="text-sm text-gray-500 mt-1">Mulai kampanye baru dan temukan kreator terbaik.</p>
                </div>

                <a href="{{ route('dashboard') }}"
                    class="btn-neumorph-primary px-5 py-2.5 bg-white rounded-2xl text-sm font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | FORM CONTAINER
            |--------------------------------------------------------------------------
            --}}
            <div class="card-inset-panel overflow-hidden">

                <div class="p-8 md:p-10">

                    {{-- SECTION TITLE --}}
                    <div class="mb-10 pb-6 border-b border-gray-100">
                        <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                            </span>
                            Detail Campaign
                        </h3>
                        <p class="text-gray-500 mt-2 text-sm ml-12">Isi informasi campaign dengan lengkap dan menarik.
                        </p>
                    </div>

                    <form action="{{ route('bounties.store') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-8">
                        @csrf

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 1. COVER IMAGE UPLOAD (ALPINE JS PREVIEW)
                        |--------------------------------------------------------------------------
                        | Menangani upload gambar dengan preview otomatis menggunakan Alpine.js.
                        --}}
                        <div x-data="{ photoName: null, photoPreview: null }" class="relative">
                            <label
                                class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-3 ml-2">
                                Cover Sayembara (Thumbnail)
                            </label>

                            {{-- Hidden Input --}}
                            <input type="file" name="image" id="photo" class="hidden" x-ref="photo"
                                x-on:change="
            photoName = $refs.photo.files[0].name;
            const reader = new FileReader();
            reader.onload = (e) => { photoPreview = e.target.result; };
            reader.readAsDataURL($refs.photo.files[0]);
        ">

                            {{-- Dropzone Area: Inset Effect (Cekung) --}}
                            <div class="input-neumorph-inset p-4 min-h-[300px] flex items-center justify-center relative group cursor-pointer overflow-hidden"
                                @click="$refs.photo.click()">

                                {{-- Border Dashed Overlay (Tetap dipertahankan untuk UX upload) --}}
                                <div
                                    class="absolute inset-4 border-2 border-dashed border-gray-300/50 rounded-2xl group-hover:border-emerald-300 transition-colors duration-300">
                                </div>

                                <div class="relative z-10 w-full">
                                    {{-- State 1: Belum Ada Gambar --}}
                                    <div x-show="! photoPreview" class="space-y-4 text-center py-10">
                                        <div
                                            class="progress-track mx-auto h-20 w-20 rounded-2xl flex items-center justify-center mb-6 shadow-inner">
                                            <svg class="w-10 h-10 text-gray-300 group-hover:text-emerald-400 transition-colors duration-500"
                                                stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                <path
                                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>

                                        <div class="space-y-1">
                                            <div
                                                class="flex text-sm text-gray-500 justify-center font-bold tracking-tight">
                                                <span
                                                    class="text-emerald-600 group-hover:text-emerald-500 transition-colors">Klik
                                                    untuk upload</span>
                                                <p class="pl-1 text-gray-400">atau drag & drop</p>
                                            </div>
                                            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">
                                                PNG, JPG, GIF (Maks. 2MB)</p>
                                        </div>
                                    </div>

                                    {{-- State 2: Preview Gambar --}}
                                    <div x-show="photoPreview" style="display: none;" class="p-2">
                                        <div class="card-timbul p-3 !rounded-[2rem] bg-white/50">
                                            <div class="w-full h-64 rounded-2xl bg-cover bg-center bg-no-repeat shadow-inner border border-white/20"
                                                x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                                            </div>

                                            <div class="mt-4 flex items-center justify-center gap-3 py-2">
                                                <div class="progress-track p-1.5 rounded-full">
                                                    <div
                                                        class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]">
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-600 font-black tracking-tight truncate max-w-[200px]"
                                                    x-text="photoName"></p>

                                                {{-- Tombol Ganti --}}
                                                <button type="button"
                                                    class="ml-2 btn-neumorph-primary !py-1 !px-3 !rounded-lg !text-[10px] font-black uppercase text-indigo-600"
                                                    @click.stop="$refs.photo.click()">
                                                    Ganti
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 2. BASIC INFO (TITLE & CATEGORY)
                        |--------------------------------------------------------------------------
                        | Input judul dan dropdown kategori custom dengan Alpine.js.
                        --}}
                        <div>
                            <label
                                class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">Judul
                                Sayembara</label>
                            <input type="text" name="title" required
                                placeholder="Contoh: Edit Momen Kocak Livestream Mobile Legends (10 Menit)"
                                class="input-neumorph-inset w-full rounded-2xl px-5 py-4 text-gray-900 font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition shadow-sm placeholder-gray-300 text-lg">
                        </div>

                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2 ml-2">
                                Kategori Campaign
                            </label>

                            <div x-data="{
                                open: false,
                                selected: '',
                                categories: ['Music Clipping', 'Podcast Clipping', 'Stream Clipping', 'Film Clipping', 'Whale Clipping', 'UGC Campaign', 'Brand Campaign', 'Miscellaneous']
                            }" @click.outside="open = false" class="relative">

                                <input type="hidden" name="category" :value="selected">

                                {{-- Trigger Button: Inset Effect (Cekung) --}}
                                <button type="button" @click="open = !open"
                                    class="input-neumorph-inset w-full px-5 py-4 text-left font-black flex justify-between items-center outline-none border-none transition-all duration-300"
                                    :class="{ 'text-gray-400': !selected, 'text-gray-800': selected }">

                                    <div class="flex items-center gap-3">
                                        <div class="progress-track p-1.5 rounded-lg shadow-inner">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                                </path>
                                            </svg>
                                        </div>
                                        <span class="text-sm tracking-tight"
                                            x-text="selected ? selected : '-- Pilih Kategori --'"></span>
                                    </div>

                                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-500"
                                        :class="{ 'rotate-180 text-indigo-500': open }" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                {{-- Dropdown Menu: Convex Effect (Timbul) --}}
                                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                                    class="card-timbul absolute z-[100] mt-4 w-full p-2 !rounded-2xl border-none overflow-hidden"
                                    style="display: none;">

                                    <ul class="max-h-64 overflow-y-auto custom-scrollbar space-y-1">
                                        <template x-for="cat in categories" :key="cat">
                                            <li @click="selected = cat; open = false"
                                                class="hover-cekung px-5 py-3.5 rounded-xl cursor-pointer text-xs font-black uppercase tracking-widest flex items-center justify-between transition-all duration-200 group"
                                                :class="selected === cat ? 'nav-item-active text-emerald-600' :
                                                    'text-gray-500 hover:text-emerald-500 hover:bg-white/50'">

                                                <span x-text="cat"></span>

                                                <div x-show="selected === cat"
                                                    class="progress-track p-1 rounded-full">
                                                    <svg class="w-3 h-3 text-emerald-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 3. SOURCE LINK & TOTAL BUDGET
                        |--------------------------------------------------------------------------
                        | Upload link materi dan input total budget campaign.
                        --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- KOLOM KIRI: UPLOAD LINK MATERI --}}
                            <div class="space-y-2">
                                <label
                                    class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2 ml-2">
                                    Upload Link Bahan Materi
                                </label>

                                <div class="relative group">
                                    {{-- Icon Wrapper dengan efek Inset --}}
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                        <div class="p-1.5 rounded-lg">
                                            <svg class="w-4 h-4 text-gray-400 group-focus-within:text-emerald-500 transition-colors duration-300"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2.5"
                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>

                                    {{-- Input Field --}}
                                    <input type="url" name="source_link" required
                                        placeholder="https://drive.google.com/..."
                                        class="input-neumorph-inset w-full pl-16 py-4 text-gray-800 font-bold text-lg outline-none border-none transition-all placeholder-gray-300">
                                </div>

                                {{-- Info Box: Menggunakan Gaya Inset/Progress Track --}}
                                <div class="progress-track !rounded-2xl p-5 border-none mt-4">
                                    <div class="flex items-start gap-4">
                                        <div class="bg-emerald-100 p-2 rounded-xl shadow-inner shrink-0 mt-0.5">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                </path>
                                            </svg>
                                        </div>

                                        <div class="text-[11px] leading-relaxed">
                                            <p class="font-black text-gray-800 uppercase tracking-wider mb-2">Domain
                                                yang Diizinkan:</p>
                                            <ul class="space-y-1.5 text-gray-500 font-bold">
                                                <li class="flex items-center gap-2">
                                                    <span class="w-1 h-1 bg-indigo-400 rounded-full"></span>
                                                    Google Drive, Dropbox, OneDrive, WeTransfer
                                                </li>
                                                <li class="flex items-center gap-2">
                                                    <span class="w-1 h-1 bg-indigo-400 rounded-full"></span>
                                                    Video Sosmed (TikTok, Instagram, YouTube)
                                                </li>
                                            </ul>

                                            <div class="mt-4 pt-3 border-t border-gray-200/50">
                                                <p
                                                    class="text-red-500 font-black uppercase tracking-widest text-[9px] flex items-center gap-1.5">
                                                    <span class="flex h-2 w-2 rounded-full bg-red-500"></span>
                                                    Link download langsung (.exe / .zip) akan ditolak.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- KOLOM KANAN: TOTAL BUDGET --}}
                            <div x-data="{
                                displayAmount: '',
                                rawAmount: 0,
                                formatCurrency(val) {
                                    if (!val) return '';
                                    let number = val.replace(/[^0-9]/g, '');
                                    this.rawAmount = number;
                                    return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                }
                            }" class="space-y-2">

                                <label
                                    class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2 ml-2">
                                    Total Budget Campaign (IDR)
                                </label>

                                <div class="relative group">
                                    {{-- Ikon Mata Uang dengan efek Progress Track --}}
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                        <div class="px-2 py-1 rounded-lg">
                                            <span
                                                class="text-lg font-black text-emerald-600 tracking-tighter">Rp.</span>
                                        </div>
                                    </div>

                                    {{-- Hidden Input untuk dikirim ke Database (Nilai Murni) --}}
                                    <input type="hidden" name="reward_amount" :value="rawAmount">

                                    {{-- Input Tampilan (Formatted) --}}
                                    <input type="text" x-model="displayAmount"
                                        @input="displayAmount = formatCurrency($event.target.value)"
                                        placeholder="Contoh: 1.000.000"
                                        class="input-neumorph-inset w-full pl-16 py-4 text-gray-800 font-black text-xl outline-none border-none transition-all placeholder-gray-300">
                                </div>

                                {{-- Helper Text dengan gaya Neumorph --}}
                                <div class="flex items-start gap-2 mt-3 ml-2 opacity-70">
                                    <div class="progress-track p-1 rounded-md shrink-0">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p
                                        class="text-[10px] text-gray-500 font-bold uppercase tracking-wide leading-relaxed">
                                        Dana maksimal yang akan dikeluarkan. Campaign otomatis stop jika budget habis.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 4. STRATEGY & REWARD CONFIGURATION (UPDATED UI) 🚀
                        |--------------------------------------------------------------------------
                        | Update: Indikator seleksi (Circle) dipindah ke pojok kanan atas.
                        | Menggunakan logic AlpineJS untuk switch warna circle.
                        --}}
                        <div class="pt-10 space-y-8" x-data="{
                            strategy: 'performance_tiered',
                            tiers: [{ target_views: '', reward: '' }],
                        
                            // Fungsi Format: Mengubah angka jadi format titik (1.000.000)
                            formatNumber(val) {
                                if (!val) return '';
                                let num = val.toString().replace(/\D/g, '');
                                return num.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            },
                        
                            // Fungsi Raw: Mengambil angka murni untuk dikirim ke database
                            getRaw(val) {
                                return val.toString().replace(/\D/g, '');
                            }
                        }">

                            <label
                                class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-4 ml-2">
                                Sistem Pembagian Hadiah
                            </label>

                            {{-- 1. PILIHAN STRATEGI --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                                {{-- Option A: Performance Tiered --}}
                                <label class="cursor-pointer group relative block h-full">
                                    <input type="radio" name="distribution_strategy" value="performance_tiered"
                                        class="peer sr-only" x-model="strategy">
                                    <div :class="strategy === 'performance_tiered' ?
                                        'nav-item-active border-amber-400/30 scale-[0.98]' : 'card-timbul border-none'"
                                        class="p-8 rounded-[2.5rem] transition-all duration-300 h-full flex flex-col items-center text-center relative overflow-hidden">
                                        <div class="absolute top-6 right-6">
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300"
                                                :class="strategy === 'performance_tiered' ? 'progress-track shadow-inner' :
                                                    'card-timbul !p-0'">
                                                <div class="w-3 h-3 rounded-full"
                                                    :class="strategy === 'performance_tiered' ?
                                                        'bg-amber-500 shadow-[0_0_10px_#f59e0b]' : 'bg-gray-200'">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress-track p-4 rounded-2xl mb-5 shadow-inner">
                                            <span
                                                class="text-4xl block group-hover:scale-110 transition-transform">🎯</span>
                                        </div>
                                        <h4 class="font-black text-gray-800 mb-2 uppercase tracking-tight text-sm">
                                            Performance Tiered</h4>
                                        <p class="text-[11px] text-gray-500 font-bold leading-relaxed">Bayar mahal
                                            untuk video viral.<br>Tentukan target & hadiah sendiri.</p>
                                    </div>
                                </label>

                                {{-- Option B: CPM (Pay Per View) --}}
                                <label class="cursor-pointer group relative block h-full">
                                    <input type="radio" name="distribution_strategy" value="cpm"
                                        class="peer sr-only" x-model="strategy">
                                    <div :class="strategy === 'cpm' ? 'nav-item-active border-emerald-400/30 scale-[0.98]' :
                                        'card-timbul border-none'"
                                        class="p-8 rounded-[2.5rem] transition-all duration-300 h-full flex flex-col items-center text-center relative overflow-hidden">
                                        <div class="absolute top-6 right-6">
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-300"
                                                :class="strategy === 'cpm' ? 'progress-track shadow-inner' :
                                                    'card-timbul !p-0'">
                                                <div class="w-3 h-3 rounded-full"
                                                    :class="strategy === 'cpm' ?
                                                        'bg-emerald-500 shadow-[0_0_10px_#10b981]' : 'bg-gray-200'">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="progress-track p-4 rounded-2xl mb-5 shadow-inner">
                                            <span
                                                class="text-4xl block group-hover:scale-110 transition-transform">💰</span>
                                        </div>
                                        <h4 class="font-black text-gray-800 mb-2 uppercase tracking-tight text-sm">CPM
                                            / Pay Per View</h4>
                                        <p class="text-[11px] text-gray-500 font-bold leading-relaxed">Bayar receh per
                                            view.<br>Cocok untuk mass awareness.</p>
                                    </div>
                                </label>
                            </div>

                            {{-- 2. FORM DINAMIS: KONFIGURASI CPM --}}
                            <div x-show="strategy === 'cpm'" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="card-inset-panel !rounded-[2.5rem] p-8 space-y-8 bg-emerald-50/20 border-emerald-100/50">

                                <h4
                                    class="font-black text-emerald-700 flex items-center gap-3 text-base uppercase tracking-wider">
                                    <div class="progress-track p-2 rounded-xl"><svg class="w-5 h-5" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                            </path>
                                        </svg></div>
                                    Konfigurasi CPM
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    {{-- Rate Per View --}}
                                    <div>
                                        <label
                                            class="block text-[10px] font-black uppercase text-emerald-600 tracking-[0.2em] mb-3 ml-2">Harga
                                            Per 1 View (Rp)</label>
                                        <div class="relative group">
                                            <input type="number" name="rate_per_view" step="0.01"
                                                placeholder="Cth: 2.5" x-bind:disabled="strategy !== 'cpm'"
                                                class="input-neumorph-inset w-full pl-6 py-4 text-emerald-800 font-black text-lg outline-none border-none">
                                        </div>
                                        <p
                                            class="text-[10px] text-gray-400 mt-3 font-bold uppercase ml-2 italic tracking-tighter">
                                            * Harga per tayangan unik</p>
                                    </div>

                                    {{-- Min Views --}}
                                    <div x-data="{ minViewsDisplay: '' }">
                                        <label
                                            class="block text-[10px] font-black uppercase text-emerald-600 tracking-[0.2em] mb-3 ml-2">Minimal
                                            Views Claim</label>
                                        <div class="relative group">
                                            <input type="hidden" name="min_views" :value="getRaw(minViewsDisplay)">
                                            <input type="text" x-model="minViewsDisplay"
                                                @input="minViewsDisplay = formatNumber($event.target.value)"
                                                placeholder="Cth: 100.000"
                                                class="input-neumorph-inset w-full pl-6 py-4 text-emerald-800 font-black text-lg outline-none border-none">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. FORM DINAMIS: TIERED REPEATER --}}
                            <div x-show="strategy === 'performance_tiered'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 -translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="card-inset-panel !rounded-[3rem] p-8 space-y-8 bg-gray-50/50">

                                <div
                                    class="flex flex-col sm:flex-row justify-between items-center gap-4 pb-6 border-b border-gray-200/50">
                                    <div class="flex items-center gap-4">
                                        <div class="progress-track p-3 rounded-2xl"><svg
                                                class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg></div>
                                        <div>
                                            <h4 class="font-black text-gray-800 text-lg uppercase tracking-tight">Atur
                                                Tingkatan Hadiah</h4>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                                Tentukan Target & Reward per Level</p>
                                        </div>
                                    </div>

                                    <button type="button" @click="tiers.push({ target_views: '', reward: '' })"
                                        class="btn-neumorph-primary !py-3 !px-6 !rounded-2xl flex items-center gap-2 group text-amber-600">
                                        <span
                                            class="text-xl font-black group-hover:rotate-90 transition-transform duration-300">+</span>
                                        <span class="text-[11px] font-black uppercase tracking-widest">Baris
                                            Baru</span>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="(tier, index) in tiers" :key="index">
                                        <div
                                            class="card-timbul !p-2 !rounded-[1.5rem] border-none flex flex-col sm:flex-row items-center gap-4 animate-fade-in-down group">
                                            <div class="px-5 py-3 progress-track !rounded-xl hidden sm:block">
                                                <span class="text-xs font-black text-gray-400 font-mono"
                                                    x-text="'#' + (index + 1)"></span>
                                            </div>

                                            {{-- Target Views (FORMATTED) --}}
                                            <div class="flex-1 w-full relative" x-data="{ viewDisplay: formatNumber(tier.target_views) }">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none opacity-40">
                                                    <svg class="w-4 h-4 text-gray-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <input type="hidden" :name="'tiers[' + index + '][target_views]'"
                                                    :value="getRaw(viewDisplay)">
                                                <input type="text" x-model="viewDisplay"
                                                    @input="viewDisplay = formatNumber($event.target.value)"
                                                    placeholder="Target Views"
                                                    class="input-neumorph-inset w-full pl-12 pr-4 py-4 text-gray-800 font-black text-sm outline-none border-none">
                                            </div>

                                            <div class="hidden sm:block opacity-20"><svg class="w-5 h-5"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                                </svg></div>

                                            {{-- Reward Amount (FORMATTED) --}}
                                            <div class="flex-1 w-full relative" x-data="{ rewardDisplay: formatNumber(tier.reward) }">
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                    <span
                                                        class="text-amber-600 font-black text-xs tracking-tighter">Rp</span>
                                                </div>
                                                <input type="hidden" :name="'tiers[' + index + '][reward]'"
                                                    :value="getRaw(rewardDisplay)">
                                                <input type="text" x-model="rewardDisplay"
                                                    @input="rewardDisplay = formatNumber($event.target.value)"
                                                    placeholder="Nominal Hadiah"
                                                    class="input-neumorph-inset w-full pl-12 pr-4 py-4 text-gray-800 font-black text-sm outline-none border-none">
                                            </div>

                                            <button type="button"
                                                @click="tiers.length > 1 ? tiers.splice(index, 1) : alert('Minimal satu tier!')"
                                                class="p-4 text-gray-400 hover:text-red-500 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 5. HASHTAGS & MENTIONS
                        |--------------------------------------------------------------------------
                        --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div>
                                <label
                                    class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">
                                    Required Hashtags <span
                                        class="text-[10px] text-gray-400 font-normal ml-1">(Opsional)</span>
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-black text-lg">#</span>
                                    </div>
                                    <input type="text" name="required_hashtags"
                                        placeholder="ClipSquad, MobileLegends, Funny"
                                        class="input-neumorph-inset w-full pl-10 py-4 text-gray-800 font-black text-[16px] outline-none border-none transition-all placeholder-gray-300">
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">
                                    Required Mentions <span
                                        class="text-[10px] text-gray-400 font-normal ml-1">(Opsional)</span>
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-black text-lg">@</span>
                                    </div>
                                    <input type="text" name="required_mentions"
                                        placeholder="windahbasudara, clipsquad.id"
                                        class="input-neumorph-inset w-full pl-10 py-4 text-gray-800 font-black text-[16px] outline-none border-none transition-all placeholder-gray-300">
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 6. PLATFORM
                        |--------------------------------------------------------------------------
                        --}}
                        <div class="space-y-4 pt-4" x-data="{ selectedPlatforms: [] }">
                            <label
                                class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-4 ml-2">
                                Platform Media Sosial
                            </label>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                                {{-- Platform: TikTok --}}
                                <label class="cursor-pointer group relative">
                                    <input type="checkbox" name="platform[]" value="tiktok" class="sr-only"
                                        x-model="selectedPlatforms">

                                    <div :class="selectedPlatforms.includes('tiktok') ?
                                        'nav-item-active scale-[0.97] border-indigo-200/50' :
                                        'card-timbul border-none hover:scale-[1.02]'"
                                        class="flex flex-col items-center justify-center p-4 rounded-[1rem] transition-all duration-300 gap-4 text-center">

                                        <span class="text-xs font-black uppercase tracking-widest"
                                            :class="selectedPlatforms.includes('tiktok') ? 'text-gray-600' : 'text-gray-600'">
                                            TikTok
                                        </span>

                                        {{-- Indikator Centang Neumorph --}}
                                        <div class="absolute right-2">
                                            <div class="w-6 h-6 rounded-lg flex items-center justify-center transition-all"
                                                :class="selectedPlatforms.includes('tiktok') ? 'progress-track' : 'opacity-0'">
                                                <svg class="w-3 h-3 text-gray-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="4" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                {{-- Platform: YouTube Shorts --}}
                                <label class="cursor-pointer group relative">
                                    <input type="checkbox" name="platform[]" value="youtube" class="sr-only"
                                        x-model="selectedPlatforms">

                                    <div :class="selectedPlatforms.includes('youtube') ?
                                        'nav-item-active scale-[0.97] border-red-200/50' :
                                        'card-timbul border-none hover:scale-[1.02]'"
                                        class="flex flex-col items-center justify-center p-4 rounded-[1rem] transition-all duration-300 gap-4 text-center">
                                        <span class="text-xs font-black uppercase tracking-widest"
                                            :class="selectedPlatforms.includes('youtube') ? 'text-red-600' :
                                                'text-gray-600'">
                                            YouTube
                                        </span>

                                        <div class="absolute right-2">
                                            <div class="w-6 h-6 rounded-lg flex items-center justify-center transition-all"
                                                :class="selectedPlatforms.includes('youtube') ? 'progress-track' :
                                                    'opacity-0'">
                                                <svg class="w-3 h-3 text-red-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="4" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                {{-- Platform: Instagram Reels --}}
                                <label class="cursor-pointer group relative">
                                    <input type="checkbox" name="platform[]" value="instagram" class="sr-only"
                                        x-model="selectedPlatforms">

                                    <div :class="selectedPlatforms.includes('instagram') ?
                                        'nav-item-active scale-[0.97] border-pink-200/50' :
                                        'card-timbul border-none hover:scale-[1.02]'"
                                        class="flex flex-col items-center justify-center p-4 rounded-[1rem] transition-all duration-300 gap-4 text-center">
                                        <span class="text-xs font-black uppercase tracking-widest"
                                            :class="selectedPlatforms.includes('instagram') ? 'text-pink-600' :
                                                'text-gray-600'">
                                            Instagram
                                        </span>

                                        <div class="absolute right-2">
                                            <div class="w-6 h-6 rounded-lg flex items-center justify-center transition-all"
                                                :class="selectedPlatforms.includes('instagram') ? 'progress-track' :
                                                    'opacity-0'">
                                                <svg class="w-3 h-3 text-pink-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="4" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 7. DESCRIPTION & DEADLINE
                        |--------------------------------------------------------------------------
                        --}}
                        <div class="space-y-10 pt-6">
                            {{-- 1. INSTRUKSI & ATURAN MAIN --}}
                            <div class="space-y-4">
                                <div
                                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 ml-2">
                                    <label
                                        class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em]">
                                        Instruksi & Aturan Main
                                    </label>
                                    <button type="button" onclick="loadTemplate()"
                                        class="btn-neumorph-primary !py-2 !px-4 !text-[10px] font-black rounded-lg !text-gray-600 uppercase tracking-widest flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                        Pakai Template Standar
                                    </button>
                                </div>

                                <textarea name="description" id="descInput" rows="12" required
                                    class="input-neumorph-inset w-full !rounded-[2rem] p-6 text-gray-700 text-sm font-bold leading-relaxed outline-none border-none transition-all placeholder-gray-300 resize-y min-h-[200px]"
                                    placeholder="Tulis detail instruksi di sini..."></textarea>
                            </div>

                            {{-- 2. DEADLINE & KALENDER --}}
                            <div class="space-y-4">
                                <label
                                    class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] ml-2">
                                    Batas Waktu (Deadline)
                                </label>

                                <div class="relative group w-full md:w-1/2">
                                    {{-- Icon Wrapper Inset --}}
                                    <div
                                        class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none z-10">
                                        <div class="progress-track p-2 rounded-xl">
                                            <svg class="w-5 h-5 text-gray-500 group-focus-within:text-emerald-600 transition-colors"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="2.5"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>
                                    </div>

                                    <input type="date" name="deadline_at" required
                                        class="input-neumorph-inset pl-16 w-full text-gray-800 font-black rounded-2xl px-6 py-4 outline-none border-none transition cursor-pointer placeholder-gray-300 appearance-none text-sm"
                                        onclick="this.showPicker()">
                                </div>
                                <p
                                    class="text-[10px] text-gray-400 font-bold uppercase tracking-widest ml-3 opacity-60">
                                    * Klik kolom untuk menentukan akhir campaign
                                </p>
                            </div>

                            {{-- 3. DISCLAIMER & SUBMIT --}}
                            <div class="space-y-8 pt-6">
                                <div
                                    class="progress-track !rounded-[2rem] p-6 flex gap-4 bg-amber-50/30 border-none relative overflow-hidden">
                                    {{-- Accent --}}
                                    <div class="absolute top-0 left-0 w-1 h-full bg-amber-400 opacity-50"></div>

                                    <div class="bg-amber-100 p-2.5 rounded-2xl shadow-inner shrink-0 self-start">
                                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </div>

                                    <div class="text-[11px] leading-relaxed text-gray-700">
                                        <strong
                                            class="text-gray-900 font-black uppercase tracking-wider block mb-1 text-xs">Kebijakan
                                            Review Otomatis</strong>
                                        <p class="font-bold opacity-80">
                                            Anda wajib mereview video Clipper maksimal <span
                                                class="text-amber-700 underline decoration-2 decoration-amber-300/50">1x24
                                                Jam</span>.
                                            Jika melewati batas tersebut, <span class="text-gray-900 font-black">Admin
                                                Clipkeo berhak melakukan Take Over</span> untuk menyetujui pembayaran
                                            demi kepuasan Clipper.
                                        </p>
                                    </div>
                                </div>

                                {{-- Final CTA --}}
                                <div class="flex justify-end pt-4">
                                    <button type="submit" class="btn-neumorph-primary !py-4 !px-6 !rounded-[1rem]">
                                        <div class="flex items-center gap-3">
                                            <span class="text-gray-600 font-black text-sm uppercase">Terbitkan
                                                Sayembara</span>
                                            <span
                                                class="group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform duration-300">🚀</span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script loadTemplate yang sama (Dipertahankan) --}}
    <script>
        function loadTemplate() {
            const template = `NAMA CAMPAIGN : [Isi Nama Campaign]
Kami mencari kreator kreatif untuk mempromosikan [NAMA BRAND/PRODUK]. 
Fokus konten: Promosi / Edukasi / Hiburan / Review Jujur.

Rate: Rp [Isi Nominal] per [Isi Nominal] views
Minimum Views untuk Claim: [Isi Nominal] views
Maximum Reward per Claim: Rp [Isi Nominal]

SUMBER VIDEO
Silakan ambil footage resmi dari sini:
Link: [MASUKKAN LINK GOOGLE DRIVE / IG / TIKTOK DISINI]

Hashtag & Mentions
I. Wajib Mentions:
    1. Instagram :
    2. Tiktok :
    3. YouTube :  

II. Wajib Hashtag : 

⚠️ RULES (WAJIB DIPATUHI)
1. Akun harus berhubungan dengan niche (Tidak boleh akun random/spam).
2. Video wajib original edit sendiri, dilarang re-upload mentah.
3. Durasi video minimal 15 detik.
4. Dilarang menghapus video selama masa kampanye.
5. Dilarang menggunakan Bot Views / Suntik Views / Ads Berbayar (Wajib Organik).
6. Dilarang clickbait berlebihan atau konteks yang menyesatkan.
7. Tidak boleh menggunakan AI Clip Maker instan (Opus, Vizard, dll).

✅ CARA CLAIM REWARD
1. Pastikan views sudah mencapai target minimum (10.000 Views).
2. Setelah konten nya di upload dan sudah menyentuh views yang diinginkan, submit konten nya ke clipsquad.com dengan cara klik “Claim” di kampanye yang kamu ikut. Dilarang “Claim” disaat views masih dibawah angka views minimum.
3. Isi link video, caption video, dan jumlah views sekarang.
4. Jangan lupa untuk masukan semua hashtag, mentions, dan kode verifikasi yang diperlukan di caption kamu.
5. Sistem kami akan verifikasi submisi nya.
6. Jika disetujui, nominal akan cair ke saldo kamu.
7. Batas waktu claim: Konten hanya bisa di claim maksimum H+7 dari tanggal upload. Jika lebih dari itu dan baru di submit, akan kita reject.

🚫 Sistem kami dapat mendeteksi aktivitas mencurigakan. Pelanggar akan di-blacklist secara permanen.

💡 TIPS KONTEN
- Gunakan hook menarik di 3 detik pertama.
- Jangan ubah konteks pesan utama brand.
- Gunakan sound yang sedang trending.`;

            const textArea = document.getElementById('descInput');

            if (textArea.value.trim() !== "") {
                if (confirm("Form sudah terisi. Apakah Anda yakin ingin menimpa dengan template standar?")) {
                    textArea.value = template;
                }
            } else {
                textArea.value = template;
            }
        }
    </script>
</x-app-layout>
