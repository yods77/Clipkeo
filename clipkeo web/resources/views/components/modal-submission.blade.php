@props(['mySocials'])
<div x-data="{
    modalSubmit: false,
    step: 1,
    viewsInput: 0,
    minRequired: 0,
    bountyId: null,
    detail: {},
    inputName: '',
    checks: { c1: false, c2: false, c3: false, c4: false, c5: false },
    previewUrl: null,
    fileName: null,
    showCopied: false,
    link: '',
    status: '',
    message: '',
    isLoading: false,

    async checkLink(bountyId) {
        console.log('🔍 Memulai pengecekan link:', this.link);
        this.status = '';
        this.message = '';
        if (!this.link) return;
        this.isLoading = true;
        try {
            let response = await fetch('{{ route('api.check.video.link') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    link: this.link,
                    bounty_id: bountyId
                })
            });
            if (!response.ok) { throw new Error('Server Error: ' + response.status); }
            let data = await response.json();
            console.log('✅ Respon Server:', data);
            this.status = data.status;
            this.message = data.message;
        } catch (error) {
            console.error('❌ Error:', error);
            this.status = 'danger';
            this.message = 'Gagal terhubung ke server.';
        } finally {
            this.isLoading = false;
        }
    },

    isStep1Valid() {
        return this.inputName === '{{ auth()->user()->name ?? '' }}' &&
            this.checks.c1 && this.checks.c2 && this.checks.c3 && this.checks.c4 && this.checks.c5;
    },

    isStep2Valid() {
        return this.viewsInput >= this.minRequired && // Validasi Views
            this.previewUrl && // Harus ada file upload
            this.link && // Harus ada link video
            this.status !== 'danger'; // Status link tidak boleh error
    },

    copyCaption() {
        let textToCopy = this.$refs.captionText.innerText;
        let onCopySuccess = () => {
            this.showCopied = true;
            setTimeout(() => this.showCopied = false, 2000);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(onCopySuccess).catch(() => {
                this.fallbackCopy(textToCopy);
            });
        } else {
            this.fallbackCopy(textToCopy);
        }
    },

    fallbackCopy(text) {
        let textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            this.showCopied = true;
            setTimeout(() => this.showCopied = false, 2000);
        } catch (err) {
            alert('Gagal menyalin. Silakan copy manual.');
        }
        document.body.removeChild(textArea);
    },

    handleFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('video/')) {
            alert('Harap upload file VIDEO saja (MP4/MOV)!');
            event.target.value = '';
            return;
        }
        this.fileName = file.name;
        this.previewUrl = URL.createObjectURL(file);
    },

    removeFile() {
        this.previewUrl = null;
        this.fileName = null;
        document.getElementById('videoInput').value = '';
    }
}" {{-- TAMBAHKAN BARIS INI (Listener) --}}
    @set-bounty-data.window="
        console.log('Sinyal diterima!', $event.detail); // Debugging
        modalSubmit = true;          // Buka Modal
        bountyId = $event.detail.id;
        minRequired = $event.detail.min;
        detail = $event.detail.detail;
        viewsInput = 0;              // Reset Input
        step = 1;                    // Reset Step
    ">
    {{-- 3. KONTROL VISIBILITAS --}}
    <div x-show="modalSubmit" x-transition class="fixed inset-0 z-[100] overflow-y-auto">
        <div @click="modalSubmit = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center">

            <div x-show="modalSubmit" x-transition
                class="card-neumorphism relative w-full max-w-2xl overflow-hidden text-left p-0 border-none shadow-none">

                {{-- HEADER FORM --}}
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-900">
                        <span x-show="step === 1">Pernyataan & Persetujuan</span>
                        <span x-show="step === 2">Upload Bukti Tayang</span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors duration-300"
                            :class="step === 1 ? 'bg-emerald-600 text-white' :
                                (isStep1Valid() ? 'bg-emerald-200 text-emerald-800' : 'bg-gray-200 text-gray-500')">1</span>
                        <span class="w-4 h-px bg-gray-300"></span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-colors duration-300"
                            :class="step === 2 ? 'bg-emerald-600 text-white' :
                                'bg-gray-200 text-gray-500'">
                            2
                        </span>
                    </div>
                    <button @click="modalSubmit = false" class="text-gray-400 hover:text-gray-600 ml-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <form :action="'/bounties/' + (bountyId || '') + '/submit'" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- STEP 1: AGREEMENT --}}
                    <div x-show="step === 1" class="p-6 space-y-4">
                        <div class="space-y-2">
                            {{-- Label dengan tipografi yang lebih tegas --}}
                            <label class="block text-[10px] text-gray-500 uppercase font-black tracking-widest">
                                Ketik nama lengkap Anda untuk konfirmasi:
                                <span class="text-emerald-600">"{{ auth()->user()->name ?? 'Guest' }}"</span>
                            </label>

                            {{-- Input Field dengan gaya Inset (Cekung) --}}
                            <input type="text" x-model="inputName" placeholder="Ketik nama sesuai di atas..."
                                class="input-neumorph-inset w-full px-4 py-3 text-sm text-gray-800 placeholder-gray-400">

                            {{-- Tip: Menambahkan feedback visual kecil jika nama sudah cocok --}}
                            <template x-if="inputName === '{{ auth()->user()->name ?? '' }}'">
                                <p
                                    class="text-[9px] text-green-600 font-bold flex items-center gap-1 mt-1 animate-pulse">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Nama sesuai, silakan lanjut.
                                </p>
                            </template>
                        </div>

                        <div class="card-inset-panel p-5 space-y-4">
                            {{-- Loop Persyaratan --}}
                            @php
                                $checkList = [
                                    'c1' => 'Link video adalah <strong class="text-red-600">milik sendiri</strong>.',
                                    'c2' =>
                                        'Tidak menggunakan <strong class="text-gray-900">AI Generator</strong> instan.',
                                    'c3' => 'Views 100% <strong class="text-green-600">ORGANIK</strong>.',
                                    'c4' => 'View Count sudah menyentuh target minimal views.',
                                    'c5' =>
                                        'Saya siap menerima sanksi <strong class="text-red-600">BANNED PERMANEN</strong> jika curang.',
                                ];
                            @endphp

                            @foreach ($checkList as $key => $label)
                                <label class="flex items-start gap-4 cursor-pointer group">
                                    <div class="relative flex items-center justify-center mt-0.5">
                                        {{-- Tambahkan focus:ring-0 dan outline-none untuk menghilangkan stroke biru/ungu --}}
                                        <input type="checkbox" x-model="checks.{{ $key }}"
                                            class="peer appearance-none w-5 h-5 card-inset-panel rounded-md checked:bg-emerald-500 transition-all cursor-pointer outline-none focus:ring-0 focus:ring-offset-0">

                                        {{-- Checkmark Icon --}}
                                        <svg class="absolute w-3 h-3 text-emerald-600 opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-[11px] text-gray-700 font-bold group-hover:text-gray-900 leading-tight">
                                        {!! $label !!}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <button type="button" @click="if(isStep1Valid()) step = 2" :disabled="!isStep1Valid()"
                            class="w-full py-4 rounded-2xl font-black text-xs uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-3 group"
                            :class="isStep1Valid() ?
                                'btn-neumorph-primary !text-emerald-800' :
                                'card-inset-panel !text-gray-400 opacity-60 cursor-not-allowed shadow-none'">

                            <span
                                x-text="isStep1Valid() ? 'Lengkapi Persyaratan & Lanjut' : 'Selesaikan Semua Syarat'"></span>

                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- STEP 2: UPLOAD & FORM --}}
                    <div x-show="step === 2" class="p-6 h-[70vh] overflow-y-auto custom-scrollbar">

                        {{-- Info Platform --}}
                        <div class="mb-6">
                            <label class="block text-[10px] text-gray-900 uppercase font-bold mb-2">Platform
                                Campaign</label>
                            <div class="flex flex-wrap gap-3 mb-3">
                                <template x-for="plat in (detail.platform || [])">
                                    <span
                                        class="px-3 py-1.5 card-inset-panel rounded-lg text-[10px] font-black text-gray-500 uppercase tracking-widest"
                                        x-text="plat"></span>
                                </template>
                            </div>
                            <div class="card-inset-panel p-4 flex gap-3 items-start border-l-4 !border-amber-400">
                                {{-- Ikon dengan background neumorph kecil --}}
                                <div
                                    class="flex-shrink-0 btn-neumorph-primary w-8 h-8 rounded-lg !shadow-sm flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-[11px] text-gray-700 font-black uppercase tracking-tight">
                                        Perhatian Kreator
                                    </p>
                                    <p class="text-[10px] text-gray-500 leading-snug">
                                        Pastikan akun sosmed sudah terhubung agar sistem bisa
                                        memverifikasi views secara otomatis.
                                    </p>
                                    <a href="{{ route('profile.edit') }}"
                                        class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-bold hover:text-emerald-800 transition-colors">
                                        Cek Profil Saya
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Section Caption --}}
                        <div class="mb-8">
                            {{-- LABEL --}}
                            <label
                                class="block text-[10px] text-gray-500 uppercase font-black mb-3 flex items-center gap-2 tracking-widest">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                Copy Caption Lengkap (Wajib)
                            </label>

                            {{-- BOX TEKS (Gaya Cekung/Inset) --}}
                            <div class="card-inset-panel p-5 mb-5">
                                <div x-ref="captionText"
                                    class="text-xs font-mono text-gray-600 leading-relaxed break-words italic">
                                    {{-- Tambahkan pengecekan detail.hashtags || '-' --}}
                                    <span
                                        x-text="'#' + (detail.required_hashtags || detail.hashtags || '-')"></span><br>

                                    {{-- Tambahkan pengecekan detail.mentions || '-' --}}
                                    <span x-text="'@' + (detail.required_mentions || detail.mentions || '-')"></span>

                                    <span class="opacity-50 not-italic text-[10px]"
                                        x-text="detail.slug || detail.unique_code"></span>
                                </div>
                            </div>

                            {{-- TOMBOL COPY (Gaya Timbul/Embossed Neumorph) --}}
                            <button type="button" @click="copyCaption()"
                                class="btn-neumorph-primary w-full py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all duration-300 flex items-center justify-center gap-3 outline-none focus:ring-0 active:scale-[0.98]"
                                :class="showCopied ? 'bg-emerald-500 !text-white !shadow-none' :
                                    'text-indigo-600'">

                                <template x-if="!showCopied">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                                            </path>
                                        </svg>
                                        <span>Copy Semua untuk Caption</span>
                                    </div>
                                </template>

                                <template x-if="showCopied">
                                    <div class="flex items-center gap-2 animate-pulse">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span class="text-emerald-600">Berhasil Disalin!</span>
                                    </div>
                                </template>
                            </button>
                        </div>

                        {{-- Form Input --}}
                        <div class="space-y-4 mb-6">
                            <div class="space-y-4 mb-6">
                                <div class="space-y-2">
                                    <label
                                        class="block text-[10px] text-gray-500 uppercase font-black tracking-widest mb-1">
                                        Pilih Akun Sosmed *
                                    </label>

                                    @if ($mySocials->isEmpty())
                                        {{-- STATE: BELUM ADA AKUN (Alert Inset dengan Aksen Merah) --}}
                                        <div
                                            class="card-inset-panel px-4 py-3 flex justify-between items-center border-l-4 !border-red-500 bg-red-50/30">
                                            <span class="text-red-600 font-bold text-[11px] flex items-center gap-2">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                    </path>
                                                </svg>
                                                Belum ada akun terhubung
                                            </span>
                                            <a href="{{ route('linked-accounts.index') }}"
                                                class="btn-neumorph-primary px-3 py-1 text-[9px] rounded-full font-black text-red-600 hover:text-red-700 transition-all">
                                                Hubungkan
                                            </a>
                                            <input type="hidden" name="linked_account_id" required>
                                        </div>
                                    @else
                                        {{-- STATE: ADA AKUN (Dropdown Gaya Inset) --}}
                                        <div class="relative group">
                                            <select name="linked_account_id" required
                                                class="input-neumorph-inset w-full px-4 py-3 text-gray-800 text-xs appearance-none cursor-pointer focus:ring-0 outline-none">
                                                <option value="" disabled selected>Pilih Akun
                                                    Sosmed Anda</option>
                                                @foreach ($mySocials as $social)
                                                    <option value="{{ $social->id }}">
                                                        {{ ucfirst($social->provider_name) }} —
                                                        {{ '@' . $social->username }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            {{-- Custom Arrow Icon (Neumorph Accent) --}}
                                            <div
                                                class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-400 group-hover:text-gray-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                {{-- [START] UPDATE: REAL-TIME LINK CHECKER (VERSI FETCH + DEBUG) --}}
                                <div class="space-y-4">
                                    <div class="space-y-4">
                                        <div>

                                            {{-- Label bergaya Neumorph minimalis --}}
                                            <label
                                                class="block text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2">
                                                Video URL *
                                            </label>

                                            <div class="relative group">
                                                {{-- Input Field dengan gaya Inset (Cekung) --}}
                                                <input type="url" name="video_url" x-model="link"
                                                    @input.debounce.1000ms="checkLink(detail.id)" required
                                                    placeholder="https://vt.tiktok.com/..."
                                                    class="input-neumorph-inset w-full px-4 py-3 text-xs text-gray-800 transition-all duration-300 outline-none focus:ring-0"
                                                    :class="{
                                                        '!border-red-400': status === 'danger',
                                                        '!border-green-400': status === 'success',
                                                        '!border-blue-400': status === 'info'
                                                    }">

                                                {{-- Loading Spinner Neumorph (Timbul di kanan) --}}
                                                <div x-show="isLoading"
                                                    class="absolute right-3 top-1/2 -translate-y-1/2">
                                                    <div
                                                        class="btn-neumorph-primary w-7 h-7 flex items-center justify-center rounded-full scale-75 shadow-sm">
                                                        <svg class="animate-spin h-3.5 w-3.5 text-emerald-600"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12"
                                                                r="10" stroke="currentColor" stroke-width="4">
                                                            </circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Alert Message bergaya Inset Alert --}}
                                            <div x-show="message"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 -translate-y-2"
                                                class="card-inset-panel mt-3 p-3 text-[10px] font-black flex items-center gap-3 border-none shadow-none"
                                                :class="{
                                                    'bg-red-50/50 text-red-600': status === 'danger',
                                                    'bg-green-50/50 text-green-600': status === 'success',
                                                    'bg-blue-50/50 text-blue-600': status === 'info'
                                                }">
                                                <span x-text="message" class="tracking-tight leading-tight"></span>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- [END] UPDATE --}}
                                    <div class="space-y-5">
                                        {{-- Input Caption --}}
                                        <div>
                                            <label
                                                class="block text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2">
                                                Caption Video Anda *
                                            </label>
                                            {{-- Textarea Inset --}}
                                            <textarea name="caption_content" rows="3" placeholder="Paste caption yang Anda gunakan di sosmed..."
                                                class="input-neumorph-inset w-full px-4 py-3 text-xs text-gray-800 focus:ring-0 outline-none transition-all placeholder-gray-400"></textarea>
                                        </div>

                                        {{-- BAGIAN INPUT VIEWS (VALIDASI MERAH) --}}
                                        <div>
                                            <label
                                                class="block text-[10px] text-gray-500 uppercase font-black tracking-widest mb-2">
                                                Jumlah Views Saat Ini *
                                            </label>
                                            <div class="relative group">
                                                <input type="number" name="current_views" required placeholder="0"
                                                    x-model.number="viewsInput"
                                                    class="input-neumorph-inset w-full px-4 py-3 text-xs text-gray-800 focus:ring-0 outline-none transition-all">
                                            </div>

                                            {{-- PESAN MERAH --}}
                                            <p x-show="viewsInput > 0 && viewsInput < minRequired"
                                                class="text-[9px] text-red-500 font-bold mt-2 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Minimal views: <span x-text="minRequired"></span> views.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="p-2 bg-red-50 border border-red-100 rounded-xl text-[10px] text-red-600 text-center shadow-lg font-bold leading-tight">
                                    SILAHKAN UPLOAD VIDEO ANALYTICSNYA UNTUK PROSES VERIFIKASI <br>⚠️
                                    UPLOAD VIDEO ANALYTICS SAJA. JANGAN VIDEO CLIPPINGNYA!
                                </div>
                                <br>
                                {{-- AREA UPLOAD --}}
                                <div class="mb-8">
                                    {{-- 1. STATE: BELUM PILIH FILE (Area Dropzone Cekung) --}}
                                    <div x-show="!previewUrl"
                                        class="card-inset-panel p-8 relative group transition-all duration-300 border-2 border-dashed border-gray-300/50 hover:border-indigo-400/50">

                                        <input type="file" id="videoInput" name="analytics_file" accept="video/*"
                                            @change="handleFile($event)"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                                        <div class="text-center relative z-0">
                                            {{-- Icon Timbul di Tengah Cekungan --}}
                                            <div
                                                class="w-14 h-14 btn-neumorph-primary rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                                                <svg class="w-6 h-6 text-emerald-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                    </path>
                                                </svg>
                                            </div>

                                            <h4
                                                class="text-[11px] font-black text-gray-700 uppercase tracking-widest mb-1">
                                                Upload Screen Record Analytics
                                            </h4>
                                            <p
                                                class="text-[9px] text-gray-400 font-bold mb-4 uppercase tracking-tighter">
                                                MP4, MOV (MAX 50MB)
                                            </p>

                                            {{-- Tombol Bantu --}}
                                            <div
                                                class="btn-neumorph-primary inline-flex px-4 py-2 rounded-lg text-[9px] font-black text-indigo-600 uppercase tracking-wider group-hover:bg-indigo-50 transition-colors">
                                                Klik atau Seret Video
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. STATE: PREVIEW VIDEO (Tampilan Profesional) --}}
                                    <div x-show="previewUrl" x-transition style="display: none;"
                                        class="card-inset-panel overflow-hidden relative border-none p-2">

                                        <div class="relative rounded-xl overflow-hidden bg-black shadow-inner">
                                            <button type="button" @click="removeFile()"
                                                class="absolute top-3 right-3 z-20 bg-red-600/90 text-white p-2 rounded-xl hover:bg-red-700 transition-all shadow-lg active:scale-90"
                                                title="Hapus Video">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>

                                            <video :src="previewUrl" controls
                                                class="w-full h-56 object-contain bg-black transition-all"></video>
                                        </div>

                                        {{-- File Info Bar --}}
                                        <div
                                            class="mt-2 py-3 px-4 flex items-center justify-between text-[10px] font-black text-gray-500 uppercase tracking-widest">
                                            <div class="flex items-center gap-2 truncate pr-4">
                                                <span class="text-emerald-600">File:</span>
                                                <span x-text="fileName" class="truncate text-gray-700"></span>
                                            </div>
                                            <div class="shrink-0 flex items-center gap-1 text-emerald-600">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Ready
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inset-panel p-4 mb-6 border-l-4 !border-indigo-500 shadow-none">
                                    <p class="text-[10px] text-gray-500 font-black uppercase tracking-[0.15em] mb-3">
                                        Syarat Sebelum Mengirim Submission
                                    </p>

                                    <div class="space-y-2.5">
                                        <div class="flex items-center gap-3 group">
                                            <div
                                                class="flex-shrink-0 w-5 h-5 btn-neumorph-primary rounded-full flex items-center justify-center scale-90">
                                                <svg class="w-3 h-3 text-emerald-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="4" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            <p
                                                class="text-[10px] text-gray-700 font-bold tracking-tight group-hover:text-gray-900 transition-colors">
                                                Video sudah mencapai minimal views
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-3 group">
                                            <div
                                                class="flex-shrink-0 w-5 h-5 btn-neumorph-primary rounded-full flex items-center justify-center scale-90">
                                                <svg class="w-3 h-3 text-emerald-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="4" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            <p
                                                class="text-[10px] text-gray-700 font-bold tracking-tight group-hover:text-gray-900 transition-colors">
                                                Video menggunakan hashtag yang diminta
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-3 group">
                                            <div
                                                class="flex-shrink-0 w-5 h-5 btn-neumorph-primary rounded-full flex items-center justify-center scale-90">
                                                <svg class="w-3 h-3 text-emerald-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="4" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            <p
                                                class="text-[10px] text-gray-700 font-bold tracking-tight group-hover:text-gray-900 transition-colors">
                                                Video sudah mention akun yang diminta
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-3 group">
                                            <div
                                                class="flex-shrink-0 w-5 h-5 btn-neumorph-primary rounded-full flex items-center justify-center scale-90">
                                                <svg class="w-3 h-3 text-emerald-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="4" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            <p
                                                class="text-[10px] text-gray-700 font-bold tracking-tight group-hover:text-gray-900 transition-colors">
                                                Video sudah di-publish & akses publik
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 border-t border-gray-100 pt-4">
                                    <button type="button" @click="step = 1"
                                        class="btn-neumorph-primary w-1/3 py-3 rounded-2xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all">Kembali</button>
                                    <button type="submit" :disabled="!isStep2Valid()"
                                        :class="!isStep2Valid() ? 'opacity-50 cursor-not-allowed bg-gray-400' :
                                            'bg-green-600 hover:bg-green-500'"
                                        class="btn-neumorph-primary w-2/3 py-3 rounded-2xl font-bold text-xs uppercase tracking-wider text-white transition-all">
                                        Kirim Submission
                                    </button>
                                </div>
                                {{-- DEBUG INFO (Sementara) --}}
                                <div class="text-[9px] text-gray-400 mt-2 text-center bg-gray-50 p-2 rounded-lg border border-gray-200"
                                    x-show="!isStep2Valid()">
                                    <div class="font-bold mb-1">Status Validasi (Debug):</div>
                                    <span
                                        :class="viewsInput >= minRequired ? 'text-green-600 font-bold' :
                                            'text-red-500 font-bold'">
                                        Views: <span x-text="viewsInput + ' / ' + minRequired"></span>
                                    </span> |
                                    <span :class="previewUrl ? 'text-green-600 font-bold' : 'text-red-500 font-bold'">
                                        File: <span x-text="previewUrl ? 'Ada' : 'Kosong'"></span>
                                    </span> |
                                    <span :class="link ? 'text-green-600 font-bold' : 'text-red-500 font-bold'">
                                        Link: <span x-text="link ? 'Ada' : 'Kosong'"></span>
                                    </span> |
                                    <span
                                        :class="status !== 'danger' ? 'text-green-600 font-bold' :
                                            'text-red-500 font-bold'">
                                        Link Status: <span x-text="status !== 'danger' ? 'OK' : 'Error'"></span>
                                    </span>
                                </div>
                            </div>
                </form>
            </div>
        </div>
    </div>
</div>
