<x-app-layout>
    {{-- 
    |--------------------------------------------------------------------------
    | ADMIN TAKEOVER VIEW (UPDATED FULL FEATURE)
    |--------------------------------------------------------------------------
    | Tampilan ini sekarang 100% mirror dari Publisher Review, 
    | tapi action form-nya mengarah ke Admin Routes.
    --}}

    <div x-data="{
        // [ADMIN] Langsung true agar modal terbuka
        showReview: true,
        strategy: '{{ $bounty->distribution_strategy }}',
        rate: {{ $bounty->rate_per_view ?? 0 }},
        min_views: {{ $bounty->min_views ?? 0 }},
    
        // Data Aktif
        data: {
            id: null,
            video_url: '',
            analytics: '',
            caption: '',
            views: 0,
            last_paid: 0,
            user: null,
            platform: [],
            linked_account: null
        },
    
        // Data Portfolio (Disuntik dari Controller)
        portfolio: {{ json_encode($portfolioData) }},
    
        init() {
            // [ADMIN] Otomatis pilih video target berdasarkan index dari controller
            const targetIndex = {{ $targetIndex }};
            if (this.portfolio.submissions.length > 0 && this.portfolio.submissions[targetIndex]) {
                this.selectVideo(this.portfolio.submissions[targetIndex]);
            }
        },
    
        selectVideo(sub) {
            this.data = {
                id: sub.id,
                video_url: sub.url,
                analytics: sub.analytics,
                caption: sub.caption,
                views: sub.views,
                last_paid: sub.paid_views,
                linked_account: sub.linked_account,
                user: sub.user,
                platform: {{ json_encode($bounty->platform) }},
                status: sub.status,
                rejection_reason: sub.rejection_reason ? sub.rejection_reason : ''
            };
        }
    }">

        {{-- BACKGROUND (Tampilan Belakang Modal - Dibuat Minimalis untuk Admin) --}}
        <div class="pt-32 pb-12 bg-gray-100 min-h-screen text-center">
            <h2 class="text-2xl font-bold text-gray-400">Mode Admin Takeover</h2>
            <p class="text-gray-500">Sedang meninjau campaign: {{ $bounty->title }}</p>
            <a href="{{ route('admin.monitoring') }}" class="mt-4 inline-block text-indigo-600 hover:underline">Kembali ke
                Monitoring</a>
        </div>

        {{-- 
        |--------------------------------------------------------------------------
        | MODAL REVIEW
        |--------------------------------------------------------------------------
        --}}
        <template x-teleport="body">
            <div x-show="showReview" class="fixed inset-0 z-[100] overflow-y-auto">
                {{-- Backdrop Gelap --}}
                <div class="fixed inset-0 bg-gray-900/90 backdrop-blur-sm transition-opacity"></div>

                <div class="flex min-h-full items-center justify-center p-4">
                    {{-- Border Amber-400 menandakan Mode Admin --}}
                    <div
                        class="relative w-full max-w-6xl bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col md:flex-row h-[85vh] border-4 border-amber-400">


                        {{-- KOLOM 1: NAVIGASI (Sama seperti sebelumnya) --}}
                        <div class="w-full md:w-1/3 bg-gray-50 border-r border-gray-100 flex flex-col">

                            {{-- Ringkasan Stats User --}}
                            <div class="p-6 border-b border-gray-200 bg-white">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total
                                    Kontribusi User Ini</h4>
                                <div class="flex items-end gap-2 mb-1">
                                    <div class="text-3xl font-black text-gray-900"
                                        x-text="new Intl.NumberFormat('id-ID').format(portfolio.total_views)"></div>
                                    <div class="text-sm font-bold text-gray-500 mb-1.5">Views</div>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 text-xs text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded-lg w-fit border border-emerald-100">
                                    Earned: Rp <span
                                        x-text="new Intl.NumberFormat('id-ID').format(portfolio.total_earned)"></span>
                                </div>
                            </div>

                            {{-- List Scrollable Video --}}
                            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1 mb-2">
                                    Daftar Video (Klik untuk Review)</p>

                                <template x-for="item in portfolio.submissions" :key="item.id">
                                    <div @click="selectVideo(item)"
                                        class="p-3 rounded-xl border transition text-xs relative cursor-pointer group"
                                        :class="item.id === data.id ?
                                            'bg-indigo-50 border-indigo-300 ring-1 ring-indigo-300 shadow-sm' :
                                            'bg-white border-gray-200 hover:border-indigo-200 hover:shadow-sm'">

                                        {{-- Indikator Aktif (Titik Biru/Pulse) --}}
                                        <template x-if="item.id === data.id">
                                            <div
                                                class="absolute right-3 top-3 w-2 h-2 rounded-full bg-indigo-600 animate-pulse">
                                            </div>
                                        </template>

                                        <div class="flex justify-between items-start mb-2">
                                            <span class="font-bold text-gray-700 truncate w-2/3 block">#<span
                                                    x-text="item.id"></span> - <span
                                                    x-text="item.created_at"></span></span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 mb-2">
                                            <div>
                                                <p class="text-[10px] text-gray-400">Total Views</p>
                                                <p class="font-bold text-gray-900"
                                                    x-text="new Intl.NumberFormat('id-ID').format(item.views)"></p>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-gray-400">Status</p>
                                                <span class="font-bold"
                                                    :class="{
                                                        'text-emerald-600': item.status === 'approved',
                                                        'text-amber-600': item.status === 'pending',
                                                        'text-rose-600': item.status === 'rejected'
                                                    }"
                                                    x-text="item.status.charAt(0).toUpperCase() + item.status.slice(1)"></span>
                                            </div>
                                        </div>

                                        {{-- Tombol Link Kecil --}}
                                        <div class="pt-2 border-t border-gray-100 flex justify-between items-center">
                                            <div class="text-[10px] text-gray-500">
                                                <span x-show="item.paid_views > 0" class="text-emerald-600">Paid:
                                                    <span
                                                        x-text="new Intl.NumberFormat('id-ID').format(item.paid_views)"></span></span>
                                                <span x-show="!item.paid_views"
                                                    class="text-gray-400 italic">Unpaid</span>
                                            </div>
                                            <a :href="item.url" target="_blank" @click.stop
                                                class="text-gray-400 hover:text-indigo-600 p-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- KOLOM 2: PLAYER (Sama seperti sebelumnya) --}}
                        <div class="w-full md:w-1/3 bg-black flex flex-col relative">
                            <div
                                class="p-4 bg-gray-900 border-b border-gray-800 text-center flex justify-between items-center">
                                <h4 class="text-xs font-bold text-gray-400 uppercase">2. Bukti Analytics</h4>
                                <a :href="data.video_url" target="_blank"
                                    class="text-[10px] bg-white/10 hover:bg-white/20 text-white px-2 py-1 rounded flex items-center gap-1 transition">
                                    Buka Link Asli <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                        </path>
                                    </svg>
                                </a>
                            </div>
                            <div class="flex-1 flex items-center justify-center bg-black overflow-hidden relative">
                                <div x-show="!data.analytics" class="text-gray-500 text-xs">No Data</div>
                                <video x-show="data.analytics" :src="data.analytics" controls
                                    class="w-full h-full object-contain focus:outline-none"></video>
                            </div>
                            <div
                                class="p-3 bg-gray-900 border-t border-gray-800 text-[10px] text-gray-500 text-center font-mono">
                                ID: #<span x-text="data.id"></span> (Admin Mode)
                            </div>
                        </div>

                        {{-- KOLOM 3: FORM ADMIN (UPDATED: SAMA PERSIS DENGAN SUBMISSION BLADE) --}}
                        <div class="w-full md:w-1/3 bg-white flex flex-col p-6 overflow-y-auto">
                            <div class="mb-6">
                                <h3 class="text-xl font-black text-gray-900">Validasi Admin</h3>
                                <p class="text-xs text-amber-600 font-bold mt-1">Anda mengambil alih tugas Publisher.
                                </p>
                                <template x-if="data.status === 'rejected'">
                                    <div x-show="data.status === 'rejected'"
                                        x-transition:enter="transition ease-out duration-300"
                                        class="mt-4 bg-rose-50 border border-rose-100 rounded-xl p-3 flex items-start gap-3 animate-pulse-once">

                                        {{-- Icon Peringatan --}}
                                        <div class="mt-0.5 text-rose-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>

                                        {{-- Isi Pesan --}}
                                        <div class="flex-1">
                                            <p
                                                class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-0.5">
                                                Status: Ditolak
                                            </p>
                                            <p class="text-xs text-rose-800 font-medium leading-relaxed">
                                                <span
                                                    x-text="data.rejection_reason || 'Tidak ada alasan spesifik.'"></span>
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- 1. INFO CLIPPER & PLATFORM --}}
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-6" x-show="data.id">
                                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200 border-dashed">
                                    <div class="relative w-10 h-10 flex-shrink-0">

                                        {{-- OPSI 1: TAMPILKAN FOTO (Jika user punya avatar_url) --}}
                                        <template x-if="data.user && data.user.avatar_url">
                                            <img {{-- Logic URL: Cek apakah link dari Google (http) atau Local Storage (/storage/) --}}
                                                :src="data.user.avatar_url.startsWith('http') ?
                                                    data.user.avatar_url :
                                                    '/storage/' + data.user.avatar_url"
                                                alt="User Avatar"
                                                class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                                        </template>

                                        {{-- OPSI 2: TAMPILKAN INISIAL (Jika user TIDAK punya foto) --}}
                                        <div x-show="!data.user || !data.user.avatar_url"
                                            class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm border border-indigo-200">
                                            {{-- Logic Inisial: Ambil huruf pertama nama user --}}
                                            <span
                                                x-text="data.user && data.user.name ? data.user.name.charAt(0).toUpperCase() : 'U'"></span>
                                        </div>

                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900"
                                            x-text="data.user ? data.user.name : 'Unknown'"></h4>
                                        <p class="text-xs text-gray-500" x-text="data.user ? data.user.email : '-'">
                                        </p>
                                    </div>
                                </div>
                                {{-- 
                                |--------------------------------------------------------------------------
                                | [UPDATE] INFO AKUN SOSMED (VERSI MINIMALIS)
                                |--------------------------------------------------------------------------
                                | Menampilkan: NAMA PLATFORM | @USERNAME
                                --}}
                                <template x-if="data.linked_account">
                                    <div class="mb-6">
                                        <p class="text-[10px] font-bold text-gray-400 tracking-widest mb-1">
                                            Akun Upload
                                        </p>

                                        {{-- Tampilan Simpel: TIKTOK | @USERNAME --}}
                                        <a :href="data.linked_account.profile_link" target="_blank"
                                            class="text-sm font-bold text-gray-900 hover:text-indigo-600 transition-colors flex items-center">

                                            {{-- Nama Platform --}}
                                            <span x-text="data.linked_account.provider_name"></span>

                                            {{-- Pemisah Garis --}}
                                            <span class="mx-2 text-gray-300">|</span>

                                            {{-- Username --}}
                                            <span x-text="'@' + data.linked_account.username"></span>

                                            {{-- Icon Link Kecil --}}
                                            <svg class="w-3 h-3 ml-1 text-gray-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                </path>
                                            </svg>
                                        </a>
                                    </div>
                                </template>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold mb-1">
                                        Platform Source</p>
                                    <a :href="data.video_url" target="_blank"
                                        class="group flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-gray-200 hover:border-indigo-300 hover:shadow-sm transition-all duration-200">
                                        <div class="text-lg">
                                            <template x-if="data.video_url && data.video_url.includes('tiktok')"><span
                                                    class="text-black"><i class="fab fa-tiktok"></i></span></template>
                                            <template
                                                x-if="data.video_url && (data.video_url.includes('instagram') || data.video_url.includes('reels'))"><span
                                                    class="text-pink-600"><i
                                                        class="fab fa-instagram"></i></span></template>
                                            <template
                                                x-if="data.video_url && (data.video_url.includes('youtube') || data.video_url.includes('youtu.be'))"><span
                                                    class="text-red-600"><i
                                                        class="fab fa-youtube"></i></span></template>
                                        </div>
                                        <div class="flex-1 overflow-hidden">
                                            <p class="text-xs font-medium text-gray-600 truncate group-hover:text-indigo-600 transition-colors"
                                                x-text="data.video_url"></p>
                                        </div>
                                        <i
                                            class="fas fa-external-link-alt text-gray-300 text-xs group-hover:text-indigo-500"></i>
                                    </a>
                                </div>
                            </div>

                            {{-- 2. CAPTION --}}
                            <div class="mb-6">
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Caption
                                </p>
                                <div class="p-3 bg-gray-50 border border-gray-100 rounded-xl text-xs text-gray-600 font-mono h-24 overflow-y-auto custom-scrollbar"
                                    x-text="data.caption"></div>
                            </div>

                            {{-- 3. FORM APPROVE (DENGAN INPUT VIEWS) --}}
                            <div class="mt-auto">

                                {{-- FORM FORCE APPROVE (Mengarah ke Admin Route) --}}
                                <form :action="'/admin/submission/' + data.id + '/takeover-approve'" method="POST"
                                    class="mb-3"
                                    onsubmit="return confirm('Yakin FORCE APPROVE? Dana akan langsung cair ke Clipper.')">
                                    @csrf @method('PATCH')

                                    {{-- Input Views CPM --}}
                                    <template x-if="strategy === 'cpm'">
                                        <div class="mb-4 bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
                                            <label
                                                class="text-[10px] text-emerald-600 uppercase font-bold mb-1.5 block tracking-widest">Validasi
                                                Views</label>
                                            <input type="number" name="views" required x-model="data.views"
                                                class="w-full bg-white border border-emerald-200 rounded-xl text-emerald-900 font-bold p-3 focus:ring-2 focus:ring-emerald-500 outline-none text-lg">
                                            <div
                                                class="flex justify-between items-center text-xs border-t border-emerald-200/50 pt-2 mt-2">
                                                <span class="text-emerald-700">Selisih (+<span
                                                        x-text="new Intl.NumberFormat('id-ID').format(Math.max(0, data.views - data.last_paid))"></span>)</span>
                                                <span class="font-black text-emerald-700 text-sm">
                                                    Rp <span
                                                        x-text="new Intl.NumberFormat('id-ID').format(Math.floor(Math.max(0, data.views - data.last_paid) * rate))"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Input Views Tiered --}}
                                    <template x-if="strategy === 'performance_tiered'">
                                        <div class="mb-4">
                                            <label
                                                class="text-[10px] text-gray-500 uppercase font-bold mb-1.5 block">Validasi
                                                Views</label>
                                            <input type="number" name="views" required x-model="data.views"
                                                class="w-full border border-gray-200 rounded-xl p-3 text-gray-900 font-bold text-lg">
                                        </div>
                                    </template>

                                    <button type="submit" {{-- [LOGIC DISABLE] Mati jika status bukan 'pending' --}}
                                        :disabled="data.status !== 'pending'" {{-- [LOGIC WARNA] Ganti warna jadi abu-abu jika disabled --}}
                                        :class="data.status !== 'pending' ?
                                            'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none' :
                                            'bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-200 hover:-translate-y-0.5'"
                                        {{-- [STYLING DASAR] --}}
                                        class="w-full py-4 font-bold rounded-2xl transition-all duration-200 text-xs uppercase tracking-widest mb-3 transform flex items-center justify-center gap-2">

                                        {{-- Teks Berubah Sesuai Status --}}
                                        <span
                                            x-text="data.status === 'approved' ? 'SUDAH DISETUJUI' : (data.status === 'rejected' ? 'SUDAH DITOLAK' : 'SETUJUI SUBMISSION')"></span>
                                    </button>
                                </form>

                                {{-- 4. FORM REJECT (DENGAN DROPDOWN) --}}
                                <form :action="'/admin/submission/' + data.id + '/takeover-reject'" method="POST"
                                    x-data="{
                                        showReason: false,
                                        reason: '',
                                        openDropdown: false,
                                        options: [
                                            'Konten tidak sesuai instruksi / brief',
                                            'Minimal views tidak terpenuhi',
                                            'Bukti analytics tidak valid / salah',
                                            'Indikasi kecurangan (Fake Views)',
                                            'Link video rusak / tidak bisa dibuka',
                                            'Video telah dihapus dari platform',
                                            'Lainnya'
                                        ]
                                    }">
                                    @csrf @method('PATCH')

                                    <input type="hidden" name="rejection_reason"
                                        :value="reason === 'Lainnya' ? '' : reason">

                                    {{-- DROPDOWN MENU --}}
                                    <div x-show="showReason" class="mb-3 space-y-3 relative">
                                        <div class="relative">
                                            <button type="button" @click="openDropdown = !openDropdown"
                                                @click.outside="openDropdown = false"
                                                class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-3 text-left flex justify-between items-center transition-all duration-200 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 group hover:border-rose-200 hover:shadow-sm">
                                                <span :class="reason ? 'text-gray-800 font-bold' : 'text-gray-400'"
                                                    x-text="reason || 'Pilih Alasan Penolakan...'"></span>
                                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                                    :class="openDropdown ? 'rotate-180 text-rose-500' : ''"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>

                                            <div x-show="openDropdown"
                                                class="absolute z-50 w-full bottom-full mb-2 bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden max-h-60 overflow-y-auto custom-scrollbar">
                                                <template x-for="item in options">
                                                    <div @click="reason = item; openDropdown = false"
                                                        class="px-4 py-3 text-sm cursor-pointer transition-colors duration-150 flex items-center justify-between"
                                                        :class="reason === item ? 'bg-rose-50 text-rose-600 font-bold' :
                                                            'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                                                        <span x-text="item"></span>
                                                        <svg x-show="reason === item" class="w-4 h-4 text-rose-500"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div x-show="reason === 'Lainnya'" class="overflow-hidden">
                                            <textarea name="rejection_reason_manual"
                                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none text-sm transition-all resize-none h-24"
                                                placeholder="Jelaskan detail alasan penolakan di sini..." :required="reason === 'Lainnya'"
                                                :name="reason === 'Lainnya' ? 'rejection_reason' : ''"></textarea>
                                        </div>
                                    </div>

                                    <button type="button" {{-- [LOGIC DISABLE] Matikan tombol jika status bukan 'pending' --}}
                                        :disabled="data.status !== 'pending'" {{-- [LOGIC KLIK] Tetap pertahankan logika asli (Buka input / Submit) --}}
                                        @click="if(showReason) { if(reason) $el.type='submit'; } else { showReason = true }"
                                        {{-- [LOGIC WARNA] --}} {{-- Jika Bukan Pending: Jadi abu-abu polos & cursor dilarang --}} {{-- Jika Pending: Tetap style asli (Border garis, hover jadi merah) --}}
                                        :class="data.status !== 'pending' ?
                                            'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed' :
                                            'border border-gray-200 text-gray-400 hover:text-rose-600 hover:border-rose-200 cursor-pointer'"
                                        {{-- [STYLING DASAR] --}}
                                        class="w-full py-3 font-bold rounded-2xl transition text-xs uppercase flex items-center justify-center gap-2">

                                        {{-- [TEKS BERUBAH] --}}
                                        <span
                                            x-text="data.status !== 'pending' ? 'TIDAK BISA DITOLAK' : (showReason ? 'Kirim Penolakan' : 'Tolak Submission')">
                                        </span>
                                    </button>
                                </form>

                                <a href="{{ route('admin.monitoring') }}"
                                    class="block text-center mt-4 text-[10px] text-gray-400 hover:text-gray-600 font-bold">KEMBALI
                                    KE DAFTAR</a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
