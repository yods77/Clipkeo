<x-app-layout>
    {{-- 
    |--------------------------------------------------------------------------
    | HEADER PAGE (CLEAN STYLE)
    |--------------------------------------------------------------------------
    --}}
    
    <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">
            
            {{-- 
            |--------------------------------------------------------------------------
            | NAVIGATION BAR
            |--------------------------------------------------------------------------
            --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Edit Sayembara</h2>
                    <p class="text-sm text-gray-500 mt-1">Perbarui detail misi kampanye Anda.</p>
                </div>
                
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-white rounded-2xl shadow-lg shadow-gray-500/50 text-sm font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Batal & Kembali
                </a>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | FORM CONTAINER
            |--------------------------------------------------------------------------
            --}}
            <div class="bg-white overflow-hidden shadow-lg shadow-gray-500/50 sm:rounded-[2.5rem] border border-gray-100">
                
                {{-- DECORATION BAR --}}
                <div class="h-2 bg-gradient-to-r from-amber-400 via-orange-500 to-red-500"></div>

                <div class="p-8 md:p-10">
                    <form action="{{ route('bounties.update', $bounty->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8"
                          {{-- Inisialisasi Data dari Database ke Alpine JS --}}
                          x-data="{ 
                              strategy: '{{ $bounty->distribution_strategy }}',
                              tiers: {{ isset($bounty->rules['tiers']) ? json_encode($bounty->rules['tiers']) : '[]' }}
                          }">
                        @csrf
                        @method('PUT')

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 1. COVER IMAGE (EDIT MODE)
                        |--------------------------------------------------------------------------
                        --}}
                        <div x-data="{ photoName: null, photoPreview: null }">
                            <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-2 ml-1">Cover Sayembara</label>
                            
                            <input type="file" name="image" id="photo" class="hidden" x-ref="photo"
                                x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => { photoPreview = e.target.result; };
                                    reader.readAsDataURL($refs.photo.files[0]);
                                ">
                            
                            <div class="mt-2 flex justify-center rounded-3xl border-2 border-dashed border-gray-200 px-6 pt-10 pb-10 hover:border-indigo-400 hover:bg-indigo-50/30 transition cursor-pointer group bg-gray-50 relative overflow-hidden"
                                @click="$refs.photo.click()">
                                
                                {{-- Preview Gambar Lama --}}
                                @if ($bounty->image_path)
                                    <div x-show="!photoPreview" class="absolute inset-0 w-full h-full bg-cover bg-center opacity-50 group-hover:opacity-30 transition"
                                         style="background-image: url('{{ asset('storage/' . $bounty->image_path) }}');">
                                    </div>
                                @endif

                                <div class="space-y-2 text-center relative z-10">
                                    <div x-show="!photoPreview">
                                        <div class="mx-auto h-16 w-16 text-gray-400 group-hover:text-indigo-500 transition mb-4 bg-white/80 p-3 rounded-full shadow-sm backdrop-blur-sm">
                                            <svg class="w-full h-full" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        </div>
                                        <div class="flex text-sm text-gray-600 justify-center bg-white/60 px-4 py-1 rounded-full inline-block">
                                            <span class="relative cursor-pointer rounded-md font-bold text-indigo-600 hover:text-indigo-500">Ganti gambar</span>
                                        </div>
                                    </div>
                                    
                                    <div x-show="photoPreview" style="display: none;" class="relative">
                                        <span class="block w-64 h-40 rounded-2xl bg-cover bg-center bg-no-repeat shadow-md mx-auto border-4 border-white"
                                              x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                                        </span>
                                        <p class="text-sm text-green-600 font-bold mt-2 bg-green-50 px-3 py-1 rounded-full inline-block" x-text="'File baru: ' + photoName"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 2. BASIC INFO
                        |--------------------------------------------------------------------------
                        --}}
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">Judul Misi</label>
                            <input type="text" name="title" value="{{ old('title', $bounty->title) }}" required
                                class="w-full bg-white border border-gray-200 rounded-2xl px-5 py-4 text-gray-900 font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition shadow-sm placeholder-gray-300 text-lg">
                        </div>

                        {{-- Kategori (Dropdown Alpine) --}}
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">Kategori Campaign</label>
                            <div x-data="{ 
                                    open: false, 
                                    selected: '{{ old('category', $bounty->category) }}', 
                                    categories: ['Music Clipping', 'Podcast Clipping', 'Stream Clipping', 'Film Clipping', 'Whale Clipping', 'UGC Campaign', 'Brand Campaign', 'Miscellaneous']
                                }" 
                                @click.outside="open = false" 
                                class="relative">

                                <input type="hidden" name="category" :value="selected">

                                <button type="button" @click="open = !open"
                                    class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-4 text-left font-bold flex justify-between items-center focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition shadow-sm hover:bg-gray-50"
                                    :class="{'text-gray-400': !selected, 'text-gray-900': selected}">
                                    <div class="flex items-center gap-3">
                                        <div class="text-indigo-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg></div>
                                        <span x-text="selected ? selected : '-- Pilih Kategori --'"></span>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <div x-show="open" class="absolute z-50 mt-2 w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden" style="display: none;">
                                    <ul class="max-h-60 overflow-y-auto custom-scrollbar py-2">
                                        <template x-for="cat in categories" :key="cat">
                                            <li @click="selected = cat; open = false" class="px-5 py-3 hover:bg-indigo-50 cursor-pointer text-sm font-bold text-gray-700 hover:text-indigo-600 transition group"><span x-text="cat"></span></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Link Sumber --}}
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">Link Sumber Video</label>
                            <input type="url" name="source_link" value="{{ old('source_link', $bounty->source_link) }}" required
                                class="w-full bg-white text-gray-900 border border-gray-200 rounded-2xl px-4 py-3.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition">
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 3. STRATEGY CONFIGURATION (READ ONLY)
                        |--------------------------------------------------------------------------
                        | Menampilkan strategi yang aktif. Kita mematikan (disable) inputnya
                        | agar user tahu bahwa strategi tidak bisa diubah saat Edit.
                        --}}
                        <div class="pt-6 border-t border-gray-100">
                            <div class="flex justify-between items-center mb-4">
                                <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest ml-1">Sistem Pembagian Hadiah (Terkunci)</label>
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 text-[10px] font-bold rounded-lg border border-gray-200">Mode Edit: Strategi tidak dapat diubah</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                {{-- Tiered Card --}}
                                <div class="relative p-6 bg-white border-2 rounded-3xl transition h-full flex flex-col items-center text-center"
                                     :class="strategy === 'performance_tiered' ? 'border-amber-400 bg-amber-50' : 'border-gray-100 opacity-50'">
                                    
                                    {{-- Indicator Top Right --}}
                                    <div class="absolute top-4 right-4" x-show="strategy === 'performance_tiered'">
                                        <div class="w-6 h-6 rounded-full bg-amber-500 border-2 border-amber-500 text-white flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>

                                    <div class="text-4xl mb-3">🎯</div>
                                    <h4 class="font-bold text-gray-900 mb-1">Performance Tiered</h4>
                                    <p class="text-xs text-gray-500">Bayar berdasarkan target views bertingkat.</p>
                                </div>

                                {{-- CPM Card --}}
                                <div class="relative p-6 bg-white border-2 rounded-3xl transition h-full flex flex-col items-center text-center"
                                     :class="strategy === 'cpm' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-100 opacity-50'">
                                    
                                    {{-- Indicator Top Right --}}
                                    <div class="absolute top-4 right-4" x-show="strategy === 'cpm'">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500 border-2 border-emerald-500 text-white flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>

                                    <div class="text-4xl mb-3">💰</div>
                                    <h4 class="font-bold text-gray-900 mb-1">CPM / Pay Per View</h4>
                                    <p class="text-xs text-gray-500">Bayar receh per view (akumulasi).</p>
                                </div>
                            </div>

                            {{-- DETAIL KONFIGURASI --}}
                            
                            {{-- A. Jika CPM --}}
                            <div x-show="strategy === 'cpm'" class="p-6 bg-emerald-50/50 rounded-3xl border border-emerald-100 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-emerald-600 tracking-widest mb-1.5 ml-1">Harga Per View (Rp)</label>
                                        <input type="text" value="{{ $bounty->rate_per_view }}" readonly
                                            class="w-full bg-emerald-100 border-0 rounded-2xl px-4 py-3.5 font-bold text-emerald-800 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-emerald-600 tracking-widest mb-1.5 ml-1">Minimal Views Claim</label>
                                        <input type="number" name="min_views" value="{{ old('min_views', $bounty->min_views) }}"
                                            class="w-full bg-white border border-emerald-200 rounded-2xl px-4 py-3.5 focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-800">
                                    </div>
                                </div>
                            </div>

                            {{-- B. Jika Tiered (Tampilkan Repeater Read-Only) --}}
                            <div x-show="strategy === 'performance_tiered'" class="p-6 bg-gradient-to-br from-gray-900 to-gray-800 rounded-[2rem] border border-gray-700 shadow-xl space-y-6">
                                <div class="flex justify-between items-center pb-4 border-b border-gray-700/50">
                                    <h4 class="font-black text-white flex items-center gap-2 text-lg">
                                        <span class="p-1.5 bg-amber-500 rounded-lg text-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                        </span>
                                        Konfigurasi Tiers
                                    </h4>
                                    {{-- Hidden Min Views untuk validasi --}}
                                    <input type="hidden" name="min_views" value="{{ $bounty->min_views }}">
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(tier, index) in tiers" :key="index">
                                        <div class="flex flex-col sm:flex-row items-center gap-3 p-1.5 bg-gray-800/50 border border-gray-700 rounded-2xl">
                                            {{-- Views --}}
                                            <div class="flex-1 w-full relative">
                                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-500">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </div>
                                                <input type="text" :value="tier.target_views" readonly
                                                    class="w-full bg-white border-0 rounded-xl pl-10 pr-4 py-3 text-gray-900 font-bold text-sm cursor-not-allowed">
                                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                                                    <span class="text-[10px] font-bold text-gray-400 uppercase">Views</span>
                                                </div>
                                            </div>
                                            {{-- Reward --}}
                                            <div class="flex-1 w-full relative">
                                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                    <span class="text-amber-500 font-black text-xs">Rp</span>
                                                </div>
                                                <input type="text" :value="tier.reward" readonly
                                                    class="w-full bg-white border-0 rounded-xl pl-10 pr-4 py-3 text-gray-900 font-bold text-sm cursor-not-allowed">
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 4. REWARD AMOUNT (BUDGET) - READONLY
                        |--------------------------------------------------------------------------
                        --}}
                        <div class="p-4 bg-gray-100 rounded-2xl border border-gray-200 opacity-80 cursor-not-allowed mt-6">
                            <label class="block text-xs font-bold uppercase text-gray-500 tracking-widest mb-1.5 ml-1">Total Budget (Terkunci)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-black text-lg">Rp</span>
                                </div>
                                <input type="number" value="{{ $bounty->reward_amount }}" readonly
                                    class="pl-12 w-full bg-transparent border-0 text-gray-500 font-bold text-lg focus:ring-0 cursor-not-allowed">
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 5. DESKRIPSI
                        |--------------------------------------------------------------------------
                        --}}
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-2 ml-1">Deskripsi Detail</label>
                            <textarea name="description" rows="8" required
                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-5 text-gray-700 text-sm font-mono leading-relaxed focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none resize-y">{{ old('description', $bounty->description) }}</textarea>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 6. PLATFORM & HASHTAGS
                        |--------------------------------------------------------------------------
                        --}}
                        <div class="space-y-3 pt-2">
                            <label class="text-xs font-bold uppercase text-gray-400 tracking-widest ml-1">Platform Media Sosial</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-200 cursor-pointer hover:bg-white hover:border-gray-300 hover:shadow-sm transition">
                                    <input type="checkbox" name="platform[]" value="tiktok" @checked(in_array('tiktok', $bounty->platform ?? [])) class="rounded border-gray-300 text-indigo-600 h-5 w-5">
                                    <span class="text-sm text-gray-700 font-bold">TikTok</span>
                                </label>
                                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-200 cursor-pointer hover:bg-white hover:border-gray-300 hover:shadow-sm transition">
                                    <input type="checkbox" name="platform[]" value="youtube_shorts" @checked(in_array('youtube_shorts', $bounty->platform ?? [])) class="rounded border-gray-300 text-indigo-600 h-5 w-5">
                                    <span class="text-sm text-gray-700 font-bold">YouTube Shorts</span>
                                </label>
                                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl border border-gray-200 cursor-pointer hover:bg-white hover:border-gray-300 hover:shadow-sm transition">
                                    <input type="checkbox" name="platform[]" value="instagram_reels" @checked(in_array('instagram_reels', $bounty->platform ?? [])) class="rounded border-gray-300 text-indigo-600 h-5 w-5">
                                    <span class="text-sm text-gray-700 font-bold">Instagram Reels</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">Wajib Hashtag</label>
                                <input type="text" name="required_hashtags" value="{{ old('required_hashtags', $bounty->required_hashtags) }}"
                                    class="pl-10 w-full bg-gray-50 text-gray-900 border border-gray-200 rounded-2xl px-4 py-3.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">Wajib Mention</label>
                                <input type="text" name="required_mentions" value="{{ old('required_mentions', $bounty->required_mentions) }}"
                                    class="pl-10 w-full bg-gray-50 text-gray-900 border border-gray-200 rounded-2xl px-4 py-3.5 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 outline-none">
                            </div>
                        </div>

                        {{-- 
                        |--------------------------------------------------------------------------
                        | 7. DEADLINE (Datepicker Updated)
                        |--------------------------------------------------------------------------
                        --}}
                        <div>
                            <label class="block text-xs font-bold uppercase text-gray-400 tracking-widest mb-1.5 ml-1">Deadline</label>
                            <div class="relative group w-full md:w-1/2">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-indigo-500 group-focus-within:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <input type="date" name="deadline_at"
                                    value="{{ \Carbon\Carbon::parse($bounty->deadline_at)->format('Y-m-d') }}"
                                    class="pl-12 w-full bg-white border border-gray-200 text-gray-900 font-bold rounded-2xl px-4 py-3.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition cursor-pointer hover:border-indigo-300 hover:shadow-sm"
                                    onclick="this.showPicker()" required>
                            </div>
                        </div>

                        {{-- BUTTONS --}}
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100 mt-8">
                            <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">Batal</a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-8 rounded-2xl shadow-xl transform transition hover:-translate-y-1 flex items-center gap-2 text-sm uppercase tracking-widest">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>