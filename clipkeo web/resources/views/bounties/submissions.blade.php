<x-app-layout>
    {{-- 
    |--------------------------------------------------------------------------
    | X-DATA GLOBAL (ALPINE JS)
    |--------------------------------------------------------------------------
    | Mengatur state modal review dan data submission yang sedang dipilih.
    | Data ini reaktif: saat variable berubah, tampilan otomatis berubah.
    --}}
    <div x-data="{
        showReview: false,
        strategy: '{{ $bounty->distribution_strategy }}',
        rate: {{ $bounty->rate_per_view ?? 0 }},
        min_views: {{ $bounty->min_views ?? 0 }},
    
        // [DATA MODEL] Objek video yang sedang aktif di layar Review
        data: {
            id: null,
            video_url: '',
            analytics: '',
            caption: '',
            views: 0,
            last_paid: 0,
            user: null,
            platform: [], // [FIX 1] Tambah koma disini
            linked_account: null,
            status: '',
            rejection_reason: ''
        },
    
        // [DATA PORTOFOLIO]
        portfolio: {
            total_views: 0,
            total_earned: 0,
            submissions: []
        },
    
        // [FUNGSI 1] Membuka Modal Review
        openReview(user_portfolio) {
            this.portfolio = user_portfolio;
    
            // Otomatis pilih video pertama
            if (this.portfolio.submissions.length > 0) {
                this.selectVideo(this.portfolio.submissions[0]);
            }
            this.showReview = true;
        },
    
        // [FUNGSI 2] Memilih Video
        selectVideo(sub) {
            this.data = {
                id: sub.id,
                video_url: sub.video_url, // [FIX 3] Gunakan 'video_url' (sesuai PHP), bukan 'url'
                analytics: sub.analytics,
                caption: sub.caption,
                views: sub.views,
                last_paid: sub.paid_views,
                linked_account: sub.linked_account,
                user: sub.user,
                platform: {{ json_encode($bounty->platform) }}, // [FIX 2] Tambah koma disini
                status: sub.status, // [FIX 4] Jangan lupa update statusnya
                rejection_reason: (sub.status === 'rejected') ? sub.rejection_reason : '',
            };
        }
    }">

        {{-- 
    |--------------------------------------------------------------------------
    | HEADER PAGE & NAVIGATION
    |--------------------------------------------------------------------------
    --}}
        <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    {{-- Judul Halaman --}}
                    <div>
                        <h2 class="text-4xl font-black text-gray-800 tracking-tighter leading-none">Review Kiriman</h2>
                        <div class="flex items-center gap-3 mt-3 ml-1">
                            <span class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">Misi:</span>
                            <div class="card-inset-panel px-4 py-1.5 !rounded-xl">
                                <span class="text-xs font-black text-emerald-600 uppercase tracking-tight">
                                    {{ $bounty->title }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Kembali --}}
                    <a href="{{ route('dashboard') }}"
                        class="btn-neumorph-primary !py-3 !px-6 !rounded-2xl group flex items-center gap-3">
                        <div class="p-1 rounded-lg">
                            <svg class="w-4 h-4 text-gray-500 group-hover:text-gray-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </div>
                        <span
                            class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-600 group-hover:text-gray-600">Kembali</span>
                    </a>
                </div>

                {{-- 
|--------------------------------------------------------------------------
| INFO STRATEGI & BUDGET (CARD ATAS)
|--------------------------------------------------------------------------
--}}
                <div
                    class="card-timbul !p-8 !rounded-[2.5rem] border-none flex flex-col md:flex-row gap-8 items-center justify-between mb-10 relative overflow-hidden group">
                    {{-- Background Decoration --}}
                    <div
                        class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-50/50 rounded-full blur-3xl group-hover:bg-indigo-100/50 transition-colors duration-700">
                    </div>

                    <div class="flex items-center gap-6 relative z-10">
                        {{-- Icon Strategy: Inset Style --}}
                        <div
                            class="progress-track w-20 h-20 rounded-[1.8rem] flex items-center justify-center text-emerald-600 shadow-inner">
                            @if ($bounty->distribution_strategy === 'cpm')
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            @else
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-black text-gray-800 text-2xl tracking-tight uppercase leading-none mb-2">
                                {{ $bounty->distribution_strategy === 'cpm' ? 'CPM (Pay Per View)' : 'Performance Tiered' }}
                            </h4>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                                    @if ($bounty->distribution_strategy === 'cpm')
                                        Rate: <span class="text-emerald-600">Rp
                                            {{ number_format($bounty->rate_per_view, 2) }}</span> per view
                                    @else
                                        Sistem Pembayaran Berbasis Pencapaian Target Tier
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Budget Display: Inset Panel --}}
                    <div
                        class="card-inset-panel !p-8 !rounded-[2rem] bg-gray-50/50 text-center md:text-right min-w-[280px] relative overflow-hidden group/budget">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-2 relative z-10">
                            Sisa Budget Tersedia</p>
                        <div class="flex items-baseline justify-center md:justify-end gap-2 relative z-10">
                            <span class="text-gray-400 font-black text-lg">Rp</span>
                            <span class="text-3xl font-black text-gray-800 font-mono tracking-tighter">
                                {{ number_format($bounty->remaining_budget) }}
                            </span>
                        </div>

                        {{-- Progress Bar Mini (Visualisasi Budget) --}}
                        <div class="mt-4 progress-track h-1.5 w-full rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>

                {{-- 
            |--------------------------------------------------------------------------
            | TABEL DAFTAR CLIPPER (GROUPED VIEW)
            |--------------------------------------------------------------------------
            | Menampilkan daftar user. Satu baris mewakili satu user dengan N video.
            --}}
                <div class="card-neumorphism !p-0 !rounded-[2.5rem] overflow-hidden border-none">
                    {{-- Header Tabel --}}
                    <div class="px-8 py-7 flex justify-between items-center bg-transparent relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="progress-track p-3 rounded-2xl shadow-inner text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-800 text-xl uppercase tracking-tighter">Daftar Clipper
                                </h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Kelola
                                    kiriman video creator</p>
                            </div>
                        </div>

                        <div class="card-inset-panel !px-4 !py-2 !rounded-xl">
                            <span class="text-[11px] font-black text-emerald-600 uppercase tracking-widest">
                                {{ $groupedSubmissions->total() }} Total Clipper
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto px-4 pb-4">
                        <table class="w-full text-left border-separate border-spacing-y-3">
                            <thead class="text-gray-400 uppercase text-[10px] font-black tracking-[0.2em]">
                                <tr>
                                    <th class="px-6 py-4">Clipper</th>
                                    <th class="px-6 py-4 text-center">Volume</th>
                                    <th class="px-6 py-4">Total Views</th>
                                    <th class="px-6 py-4">Est. Earnings</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @forelse($groupedSubmissions as $group)
                                    {{-- DATA PREPARATION (LOGIC TETAP) --}}
                                    @php
                                        $group->submissions_list->load('linkedAccount');
                                        $portfolioData = [
                                            'total_views' => $group->total_views_all,
                                            'total_earned' => $group->total_earnings_all,
                                            'submissions' => $group->submissions_list
                                                ->map(function ($item) use ($group) {
                                                    return [
                                                        'id' => $item->id,
                                                        'video_url' => $item->video_url,
                                                        'views' => $item->views_count,
                                                        'paid_views' => $item->last_paid_views ?? 0,
                                                        'prize' => $item->prize_amount,
                                                        'status' => $item->status,
                                                        'analytics' => str_starts_with($item->analytics_path, 'http') 
                                                            ? $item->analytics_path 
                                                            : asset('storage/' . $item->analytics_path),
                                                        'caption' => $item->caption_content,
                                                        'created_at' => $item->created_at->format('d M H:i'),
                                                        'user' => $group->user,
                                                        'linked_account' => $item->linkedAccount,
                                                        'rejection_reason' =>
                                                            $item->status === 'rejected' ? $item->rejection_reason : '',
                                                    ];
                                                })
                                                ->values(),
                                        ];
                                    @endphp

                                    <tr class="rounded-2xl transition-all duration-300 group cursor-default">
                                        {{-- Nama Clipper --}}
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="progress-track p-1 rounded-full shadow-inner">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center font-black text-sm border-2 border-white/20">
                                                        {{ substr($group->user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-gray-800 font-black tracking-tight text-base">
                                                        {{ $group->user->name }}</div>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        @if ($group->general_status == 'pending')
                                                            <span
                                                                class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                                            <span
                                                                class="text-[9px] text-amber-600 font-black uppercase tracking-widest">Ada
                                                                Review Baru</span>
                                                        @else
                                                            <span
                                                                class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                                            <span
                                                                class="text-[9px] text-emerald-600 font-black uppercase tracking-widest">Sudah
                                                                Direview</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Jumlah Video --}}
                                        <td class="px-6 py-5 text-center">
                                            <div
                                                class="inline-flex items-center gap-2 px-4 py-1.5 card-inset-panel !rounded-xl border-none">
                                                <span
                                                    class="text-xs font-black text-gray-700 font-mono">{{ $group->submissions_list->count() }}</span>
                                                <span
                                                    class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">Vids</span>
                                            </div>
                                        </td>

                                        {{-- Total Views --}}
                                        <td class="px-6 py-5">
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-mono font-black text-gray-800 text-base leading-none tracking-tighter">
                                                    {{ number_format($group->total_views_all) }}
                                                </span>
                                                <span
                                                    class="text-[9px] font-black text-emerald-400 uppercase tracking-widest mt-1">Accumulated
                                                    Views</span>
                                            </div>
                                        </td>

                                        {{-- Total Earnings --}}
                                        <td class="px-6 py-5">
                                            <div
                                                class="inline-block card-timbul !py-1.5 !px-3 !rounded-lg border-none bg-emerald-50/30">
                                                <span
                                                    class="text-xs font-black text-emerald-600 font-mono tracking-tighter">
                                                    Rp {{ number_format($group->total_earnings_all) }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="px-6 py-5 text-right">
                                            <button @click='openReview(@json($portfolioData))'
                                                class="btn-neumorph-primary  !py-3 !px-5 !rounded-xl group/btn flex items-center gap-2 ml-auto">
                                                <span
                                                    class="text-[10px] font-black text-gray-800 uppercase tracking-widest">Detail
                                                    & Review</span>
                                                <svg class="w-3.5 h-3.5 text-gray-800/80 group-hover/btn:translate-x-1 transition-transform"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-20 text-center">
                                            <div
                                                class="progress-track inline-block p-10 rounded-[2rem] shadow-inner mb-6">
                                                <span class="text-5xl grayscale opacity-20">👥</span>
                                            </div>
                                            <p class="text-[11px] text-gray-400 font-black uppercase tracking-[0.3em]">
                                                Belum ada clipper yang bergabung di misi ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Area --}}
                    <div class="px-8 py-8 bg-gray-50/30 border-t border-gray-100/50">
                        <div class="progress-track !rounded-2xl p-2 shadow-inner">
                            {{ $groupedSubmissions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 
    |--------------------------------------------------------------------------
    | MODAL REVIEW UTAMA (3 KOLOM)
    |--------------------------------------------------------------------------
    --}}
        <template x-teleport="body">
            <div x-show="showReview" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
                {{-- Backdrop Gelap --}}
                <div @click="showReview = false"
                    class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity">
                </div>

                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="showReview" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="relative w-full max-w-6xl bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col md:flex-row h-[85vh]">

                        {{-- 
                    |-------------------------------------------------------
                    | KOLOM 1 (KIRI): NAVIGASI LIST VIDEO
                    |-------------------------------------------------------
                    --}}
                        <div
                            class="w-full md:w-1/3 bg-[#f3f4f6] border-r border-gray-200/50 flex flex-col h-full shadow-inner">

                            {{-- Ringkasan Stats User --}}
                            <div class="p-8 border-b border-gray-200/50 bg-transparent">
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 ml-1">
                                    Total Kontribusi User Ini
                                </label>

                                <div class="card-inset-panel !p-6 !rounded-[2rem] bg-gray-50/50">
                                    <div class="flex items-end gap-2 mb-3">
                                        <div class="text-4xl font-black text-gray-800 tracking-tighter"
                                            x-text="new Intl.NumberFormat('id-ID').format(portfolio.total_views)">
                                        </div>
                                        <div
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                            Views</div>
                                    </div>

                                    <div
                                        class="inline-flex items-center gap-2 px-4 py-2 card-timbul !rounded-xl border-none">
                                        <span
                                            class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]"></span>
                                        <span class="text-[11px] font-black text-emerald-600 uppercase tracking-tight">
                                            Earned: Rp <span
                                                x-text="new Intl.NumberFormat('id-ID').format(portfolio.total_earned)"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- List Scrollable Video --}}
                            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                                <div class="flex items-center justify-between mb-4 px-2">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                        Daftar Video (Klik untuk Review)
                                    </p>
                                    <div class="progress-track px-2 py-1 rounded-md">
                                        <span class="text-[9px] font-black text-gray-500"
                                            x-text="portfolio.submissions.length + ' Items'"></span>
                                    </div>
                                </div>

                                <template x-for="item in portfolio.submissions" :key="item.id">
                                    <div @click="selectVideo(item)"
                                        class="relative p-5 rounded-[1rem] transition-all duration-300 cursor-pointer group overflow-hidden border-none"
                                        :class="item.id === data.id ?
                                            'nav-item-active scale-[0.98]' :
                                            'card-timbul hover:scale-[1.01]'">

                                        {{-- Indikator Aktif (Glow Dot) --}}
                                        <template x-if="item.id === data.id">
                                            <div
                                                class="absolute right-6 top-6 w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse shadow-[0_0_10px_#4f46e5]">
                                            </div>
                                        </template>

                                        <div class="flex justify-between items-start mb-4">
                                            <div class="progress-track px-3 py-1 rounded-lg">
                                                <span class="font-black text-gray-500 text-[10px] tracking-tighter">
                                                    #<span x-text="item.id"></span> • <span
                                                        x-text="item.created_at"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div class="space-y-1">
                                                <p
                                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                                    Total Views</p>
                                                <p class="font-black text-gray-800 text-sm font-mono tracking-tighter"
                                                    x-text="new Intl.NumberFormat('id-ID').format(item.views)"></p>
                                            </div>
                                            <div class="space-y-1">
                                                <p
                                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest">
                                                    Status</p>
                                                <span class="text-[10px] font-black uppercase tracking-tight"
                                                    :class="{
                                                        'text-emerald-600': item.status === 'approved',
                                                        'text-amber-600': item.status === 'pending',
                                                        'text-rose-600': item.status === 'rejected'
                                                    }"
                                                    x-text="item.status"></span>
                                            </div>
                                        </div>

                                        {{-- Footer Kartu --}}
                                        <div class="pt-3 border-t border-gray-400 flex justify-between items-center">
                                            <div class="text-[10px] font-black uppercase tracking-widest">
                                                {{-- 1. Status: PAID (Jika status approved ATAU memang sudah pernah ada views yang dibayar) --}}
                                                <template x-if="item.status === 'approved' || item.paid_views > 0">
                                                    <div
                                                        class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50/30 px-2 py-1 rounded-lg w-fit">
                                                        <span
                                                            class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_#10b981]"></span>
                                                        <span>Paid</span>
                                                        {{-- Tambahan info jumlah views yang dibayar jika ada --}}
                                                        <template x-if="item.paid_views > 0">
                                                            <span class="opacity-70 font-mono"
                                                                x-text="'(' + new Intl.NumberFormat('id-ID').format(item.paid_views) + ')'"></span>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- 2. Status: REJECTED --}}
                                                <template x-if="item.status === 'rejected'">
                                                    <div
                                                        class="flex items-center gap-1.5 text-rose-500 bg-rose-50/50 px-2 py-1 rounded-lg w-fit">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                        <span>Unpaid</span>
                                                    </div>
                                                </template>

                                                {{-- 3. Status: PENDING / BELUM DI-REVIEW --}}
                                                <template x-if="item.status === 'pending' && !item.paid_views">
                                                    <div class="flex items-center gap-1.5 text-gray-400 px-2 py-1">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                                        <span class="italic opacity-60">Waiting Review</span>
                                                    </div>
                                                </template>
                                            </div>

                                            <a :href="item.video_url" target="_blank" @click.stop
                                                class="progress-track p-2 rounded-xl text-gray-400 hover:text-indigo-600 transition-colors shadow-inner">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- 
                    |-------------------------------------------------------
                    | KOLOM 2 (TENGAH): PLAYER VIDEO / BUKTI
                    |-------------------------------------------------------
                    --}}
                        <div class="w-full md:w-1/3 bg-[#f3f4f6] border-r border-gray-200/50 flex flex-col relative group shadow-inner">
    
    {{-- Header Kolom --}}
    <div class="p-5 bg-transparent border-b border-gray-200/50 flex justify-between items-center px-8">
        <div>
            <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">2. Bukti Video Analytics</h4>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                <span class="text-[9px] font-black text-gray-800 uppercase tracking-widest" x-text="'ID: #' + data.id"></span>
            </div>
        </div>

        {{-- Tombol Link Asli (Gaya Timbul) --}}
        <a :href="data.video_url" target="_blank"
            class="card-timbul !px-3 !py-2 !rounded-xl text-[9px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2 hover:scale-105 transition-all border-none">
            Link Asli 
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
        </a>
    </div>

    {{-- AREA PLAYER (INSET / CEKUNG) --}}
    <div class="flex-1 flex items-center justify-center p-8 bg-transparent overflow-hidden relative">
        
        {{-- Empty State --}}
        <div x-show="!data.analytics" class="text-center p-10 card-inset-panel !rounded-[2rem] w-full">
            <div class="text-3xl mb-3 opacity-20 grayscale">🎬</div>
            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Pilih video untuk memutar bukti</p>
        </div>

        {{-- VIDEO PLAYER CONTAINER (Gaya Layar Tertanam) --}}
        <div x-show="data.analytics" x-transition.opacity
             class="card-inset-panel !p-3 !rounded-[2.5rem] w-full h-full bg-gray-200/50 flex items-center justify-center shadow-inner">
            
            <div class="w-full h-full rounded-[2rem] overflow-hidden bg-black shadow-2xl relative group/video">
                {{-- Player Video Cloudinary --}}
                <video x-ref="videoPlayer"
                       :src="data.analytics" 
                       controls
                       class="w-full h-full object-contain focus:outline-none"
                       {{-- Agar video tidak otomatis muter dan berat --}}
                       preload="metadata">
                </video>

                {{-- Overlay tipis saat video tidak diputar --}}
                <div class="absolute inset-0 pointer-events-none border-[8px] border-black/10 rounded-[2rem]"></div>
            </div>
        </div>
    </div>

    {{-- Footer Info --}}
    <div class="p-4 bg-transparent text-center">
        <div class="progress-track !rounded-full py-2 px-6 inline-block shadow-inner">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">
                Cloudinary Optimized Streaming
            </p>
        </div>
    </div>
</div>

                        {{-- 
                    |-------------------------------------------------------
                    | KOLOM 3 (KANAN): FORM VALIDASI & INFO USER
                    |-------------------------------------------------------
                    --}}
                        <div class="w-full md:w-1/3 bg-#f3f4f6 flex flex-col p-6 overflow-y-auto">
                            <div class="mb-6">
                                <h3 class="text-xl font-black text-gray-900 mb-1">Validasi & Bayar</h3>
                                <p class="text-xs text-gray-500">Video terpilih: <span
                                        class="font-mono font-bold text-gray-700"
                                        x-text="new Intl.NumberFormat('id-ID').format(data.views)"></span> Views</p>
                                <template x-if="data.status === 'rejected'">
                                    <div x-show="data.status === 'rejected'"
                                        x-transition:enter="transition ease-out duration-300"
                                        class="card-neumorphism mt-4 bg-rose-50 border border-rose-100 rounded-xl p-3 flex items-start gap-3 animate-pulse-once">

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

                            {{-- [TAMBAHAN FITUR: INFO CLIPPER & PLATFORM] --}}
                            <div class="card-inset-panel p-4 rounded-xl mb-6" x-show="data.id">

                                {{-- 1. Profil Clipper --}}
                                <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-200 border-dashed">
                                    <div
                                        class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm shadow-sm">
                                        <span x-text="data.user ? data.user.name.charAt(0) : 'U'"></span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">
                                            Uploaded By</p>
                                        <h4 class="text-sm font-bold text-gray-900 leading-tight"
                                            x-text="data.user ? data.user.name : 'Unknown'"></h4>
                                        <p class="text-xs text-gray-500" x-text="data.user ? data.user.email : '-'">
                                        </p>
                                    </div>
                                </div>

                                {{-- 2. Sumber Platform & Link --}}
                                <div>
                                    <template x-if="data.linked_account">
                                        <div class="mb-6">
                                            <p class="text-[10px] font-bold text-gray-400 tracking-widest mb-1">
                                                Akun Upload
                                            </p>

                                            {{-- Tampilan: TIKTOK - @USERNAME --}}
                                            <a :href="data.linked_account.profile_link" target="_blank"
                                                class="text-sm font-bold text-gray-900 hover:text-indigo-600 transition-colors flex items-center">

                                                {{-- Nama Platform --}}
                                                <span x-text="data.linked_account.provider_name"></span>

                                                {{-- Pemisah --}}
                                                <span class="mx-2 text-gray-300">|</span>

                                                {{-- Username --}}
                                                <span x-text="'@' + data.linked_account.username"></span>
                                            </a>
                                        </div>
                                    </template>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold mb-1">
                                        Platform Source</p>

                                    {{-- Gunakan data.video_url --}}
                                    <a :href="data.video_url" target="_blank"
                                        class="btn-neumorph-primary flex items-center gap-2 px-3 py-2 rounded-lg">

                                        {{-- Logika Ikon Otomatis --}}
                                        <div class="text-lg">
                                            <template x-if="data.video_url && data.video_url.includes('tiktok')">
                                                <span class="text-black"><i class="fab fa-tiktok"></i></span>
                                            </template>
                                            <template
                                                x-if="data.video_url && (data.video_url.includes('instagram') || data.video_url.includes('reels'))">
                                                <span class="text-pink-600"><i class="fab fa-instagram"></i></span>
                                            </template>
                                            <template
                                                x-if="data.video_url && (data.video_url.includes('youtube') || data.video_url.includes('youtu.be'))">
                                                <span class="text-red-600"><i class="fab fa-youtube"></i></span>
                                            </template>
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

                            {{-- Caption --}}
                            <div class="mb-6">
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-2 tracking-widest">Caption
                                </p>
                                <div class="card-neumorphism p-3 rounded-xl text-xs text-gray-600 font-mono h-24 overflow-y-auto custom-scrollbar"
                                    x-text="data.caption"></div>
                            </div>

                            {{-- Form Logic --}}
                            @if ($bounty->remaining_budget <= 100)
                                <div class="mt-auto">
                                    <div class="mb-4 bg-gray-50 p-4 rounded-xl border border-gray-200 text-center">
                                        <span class="block text-gray-600 font-bold text-xs uppercase mb-1">Budget
                                            Campaign Habis</span>
                                        <p class="text-[10px] text-gray-400 leading-snug">
                                            Tidak dapat melakukan review (Validasi/Tolak) karena sisa budget Rp
                                            {{ number_format($bounty->remaining_budget) }}.
                                        </p>
                                    </div>
                                    <button disabledSetujui
                                        class="w-full py-3 bg-gray-200 text-gray-400 font-bold rounded-2xl cursor-not-allowed text-xs uppercase mb-2">
                                        (Disabled)</button>
                                    <button disabled
                                        class="w-full py-3 border border-gray-200 text-gray-300 font-bold rounded-2xl cursor-not-allowed text-xs uppercase">Tolak
                                        (Disabled)</button>
                                </div>
                            @else
                                {{-- Form Normal --}}
                                <form :action="'/submissions/' + data.id + '/approve'" method="POST" class="mt-auto">
                                    @csrf
                                    <template x-if="strategy === 'cpm'">
                                        <div class="card-neumorphism mb-4 p-4 rounded-2xl border border-emerald-100">
                                            <label
                                                class="text-[10px] text-emerald-600 uppercase font-bold mb-1.5 block tracking-widest">Update
                                                Views Terbaru</label>
                                            <input type="number" name="views" required x-model="data.views"
                                                class="w-full bg-white border border-emerald-200 rounded-xl text-emerald-900 font-bold p-3 focus:ring-2 focus:ring-emerald-500 outline-none text-lg">
                                            <div
                                                class="flex justify-between items-center text-xs border-t border-emerald-200/50 pt-2 mt-2">
                                                <span class="text-emerald-700">Selisih (+<span
                                                        x-text="new Intl.NumberFormat('id-ID').format(Math.max(0, data.views - data.last_paid))"></span>
                                                    views)</span>
                                                <span class="font-black text-emerald-700 text-sm">
                                                    Rp <span
                                                        x-text="new Intl.NumberFormat('id-ID').format(Math.floor(Math.max(0, data.views - data.last_paid) * rate))"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="strategy === 'performance_tiered'">
                                        <div class="mb-4">
                                            <label
                                                class="text-[10px] text-gray-500 uppercase font-bold mb-1.5 block">Validasi
                                                Views</label>
                                            <input type="number" name="views" required x-model="data.views"
                                                class="input-neumorph-inset w-full p-3 text-gray-900 font-bold text-lg">
                                        </div>
                                    </template>
                                    <button type="submit" {{-- [LOGIC DISABLE] Mati jika status bukan 'pending' --}}
                                        :disabled="data.status !== 'pending'" {{-- [LOGIC WARNA] Ganti warna jadi abu-abu jika disabled --}}
                                        :class="data.status !== 'pending' ?
                                            'bg-emerald-300 text-emerald-500' :
                                            'bg-emerald-500 hover:bg-emerald-600 text-white'"
                                        {{-- [STYLING DASAR] --}}
                                        class="btn-neumorph-approve w-full py-4 font-bold text-emerald-500 rounded-2xl transition-all duration-200 text-xs uppercase tracking-widest mb-3 transform flex items-center justify-center gap-2">

                                        {{-- Teks Berubah Sesuai Status --}}
                                        <span
                                            x-text="data.status === 'approved' ? 'SUDAH DISETUJUI' : (data.status === 'rejected' ? 'SUDAH DITOLAK' : 'SETUJUI SUBMISSION')"></span>
                                    </button>
                                </form>

                                <form :action="'/submissions/' + data.id + '/reject'" method="POST"
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
                                    @csrf

                                    {{-- Input Hidden (Ini yang dikirim ke Server) --}}
                                    <input type="hidden" name="rejection_reason"
                                        :value="reason === 'Lainnya' ? '' : reason">

                                    {{-- WRAPPER DROPDOWN --}}
                                    <div x-show="showReason" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 -translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="mb-3 space-y-3 relative">

                                        {{-- 1. TRIGGER BUTTON (Tampilan Luar Dropdown) --}}
                                        <div class="relative">
                                            <button type="button" @click="openDropdown = !openDropdown"
                                                @click.outside="openDropdown = false"
                                                class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-3 text-left flex justify-between items-center transition-all duration-200 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 group hover:border-rose-200 hover:shadow-sm">

                                                {{-- Placeholder vs Selected Value --}}
                                                <span :class="reason ? 'text-gray-800 font-bold' : 'text-gray-400'"
                                                    x-text="reason || 'Pilih Alasan Penolakan...'"></span>

                                                {{-- Icon Chevron (Berputar saat dibuka) --}}
                                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                                    :class="openDropdown ? 'rotate-180 text-rose-500' : ''"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>

                                            {{-- 2. LIST OPTIONS (Menu Melayang dengan Animasi) --}}
                                            <div x-show="openDropdown"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                x-transition:leave="transition ease-in duration-150"
                                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                                class="absolute z-50 w-full bottom-full mb-2 bg-white border border-gray-100 rounded-2xl shadow-xl shadow-gray-200/50 overflow-hidden max-h-60 overflow-y-auto custom-scrollbar">

                                                <template x-for="item in options">
                                                    <div @click="reason = item; openDropdown = false"
                                                        class="px-4 py-3 text-sm cursor-pointer transition-colors duration-150 flex items-center justify-between"
                                                        :class="reason === item ? 'bg-rose-50 text-rose-600 font-bold' :
                                                            'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">

                                                        <span x-text="item"></span>

                                                        {{-- Icon Check jika dipilih --}}
                                                        <svg x-show="reason === item" class="w-4 h-4 text-rose-500"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- 3. TEXTAREA MANUAL (Hanya muncul jika pilih 'Lainnya') --}}
                                        <div x-show="reason === 'Lainnya'"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 h-0"
                                            x-transition:enter-end="opacity-100 h-24" class="overflow-hidden">
                                            <textarea name="rejection_reason_manual"
                                                class="w-full bg-gray-50 border border-gray-200 rounded-2xl p-4 text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 outline-none text-sm transition-all resize-none h-24"
                                                placeholder="Jelaskan detail alasan penolakan di sini..." :required="reason === 'Lainnya'"
                                                :name="reason === 'Lainnya' ? 'rejection_reason' : ''"></textarea>
                                        </div>

                                    </div>

                                    {{-- TOMBOL EKSEKUSI --}}
                                    <button type="button" {{-- [LOGIC DISABLE] Matikan tombol jika status bukan 'pending' --}}
                                        :disabled="data.status !== 'pending'" {{-- [LOGIC KLIK] Tetap pertahankan logika asli Bosku --}}
                                        @click="if(showReason) { if(reason) $el.type='submit'; } else { showReason = true }"
                                        {{-- [STYLING] Gunakan :class untuk ganti warna otomatis --}}
                                        :class="data.status !== 'pending' ?
                                            'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed' :
                                            'border-rose-100 text-rose-500'"
                                        {{-- [BASE CLASS] Styling dasar layout --}}
                                        class="btn-neumorph-reject w-full py-4 border font-bold rounded-2xl transition-all duration-200 text-xs uppercase flex items-center justify-center gap-2 group">

                                        {{-- Ikon X (Muncul saat belum mode input reason) --}}
                                        <svg x-show="!showReason" class="w-4 h-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>

                                        {{-- Ikon Pesawat Kertas (Muncul saat mode input reason) --}}
                                        <svg x-show="showReason" class="w-4 h-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>

                                        {{-- [TEKS BERUBAH] Menyesuaikan Status & Mode Input --}}
                                        <span
                                            x-text="data.status !== 'pending' ? 'TIDAK BISA DITOLAK' : (showReason ? 'Kirim Penolakan' : 'Tolak Submission')"></span>
                                    </button>
                                </form>
                            @endif

                            <button @click="showReview = false"
                                class="w-full py-3 text-[10px] text-gray-400 font-bold hover:text-gray-600 mt-1">TUTUP</button>
                        </div>

                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
