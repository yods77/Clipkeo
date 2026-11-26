<x-app-layout>
    {{-- 
    |--------------------------------------------------------------------------
    | HALAMAN MODERASI CAMPAIGN (ADMIN) - UPDATED
    |--------------------------------------------------------------------------
    | Fitur Baru: 
    | 1. Deskripsi lebih rapi (Preserve Newlines).
    | 2. Penambahan Info: Deadline, Hashtag, Mention, Skema Reward.
    --}}

    <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- Header Halaman --}}
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Moderasi Campaign</h2>
                    <p class="text-sm text-gray-500">Tinjau dan setujui campaign baru dari Publisher.</p>
                </div>
                <div
                    class="bg-white px-4 py-2 rounded-xl shadow-sm text-xs font-bold text-gray-600 border border-gray-100">
                    Pending: <span class="text-indigo-600">{{ $pendingBounties->count() }} Campaign</span>
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | ALPINE JS STATE MANAGEMENT
            |--------------------------------------------------------------------------
            --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-[1.5rem] border border-gray-100"
                x-data="{
                    // State Modal Reject
                    showRejectModal: false,
                    rejectAction: '',
                    selectedTitle: '',
                
                    // State Modal Detail (Wide)
                    showDetailModal: false,
                    detail: {
                        title: '',
                        category: '',
                        description: '',
                        source_link: '',
                        publisher_name: '',
                        publisher_avatar_url: '',
                        publisher_email: '',
                        publisher_join: '',
                        budget: '',
                        platform: [],
                        image_url: '',
                
                        // [DATA BARU UNTUK MODAL]
                        deadline: '',
                        hashtags: '',
                        mentions: '',
                        strategy: '',
                        rate: '',
                        tiers: []
                    }
                }">

                @if ($pendingBounties->isEmpty())
                    {{-- State Kosong --}}
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">Semua Bersih!</h3>
                        <p class="text-gray-500 text-sm mt-1">Tidak ada campaign yang perlu ditinjau saat ini.</p>
                    </div>
                @else
                    {{-- Table List --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-6 py-4 font-bold">Campaign Info</th>
                                    <th class="px-6 py-4 font-bold">Publisher</th>
                                    <th class="px-6 py-4 font-bold">Budget & Platform</th>
                                    <th class="px-6 py-4 font-bold text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($pendingBounties as $bounty)
                                    <tr class="hover:bg-gray-50/50 transition duration-150">

                                        {{-- Kolom 1: Info Campaign --}}
                                        <td class="px-6 py-4 align-top w-1/3">
                                            <div class="flex gap-4">
                                                <div
                                                    class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-xl overflow-hidden shadow-sm border border-gray-200">
                                                    <img src="{{ Storage::url($bounty->image_path) }}"
                                                        class="w-full h-full object-cover">
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-900 line-clamp-1">
                                                        {{ $bounty->title }}</h4>
                                                    <div class="flex items-center gap-2 mt-1 mb-2">
                                                        <span
                                                            class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-bold uppercase rounded-md tracking-wide">
                                                            Pending Review
                                                        </span>
                                                        <span
                                                            class="text-[10px] text-gray-400 font-medium bg-gray-100 px-2 py-0.5 rounded-md">
                                                            {{ $bounty->category }}
                                                        </span>
                                                    </div>
                                                    <a href="{{ $bounty->source_link }}" target="_blank"
                                                        class="text-xs text-indigo-600 hover:underline flex items-center gap-1 font-medium">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                            </path>
                                                        </svg>
                                                        Cek Link Sumber
                                                    </a>
                                                </div>
                                            </div>

                                            {{-- Tombol Buka Modal --}}
                                            <div class="mt-3">
                                                <button
                                                    @click="
                                                    showDetailModal = true;
                                                    detail = {
                                                        title: '{{ addslashes($bounty->title) }}',
                                                        category: '{{ $bounty->category }}',
                                                        description: `{{ nl2br(e($bounty->description)) }}`, 
                                                        source_link: '{{ $bounty->source_link }}',
                                                        publisher_name: '{{ addslashes($bounty->publisher->name) }}',
                                                        publisher_email: '{{ $bounty->publisher->email }}',
                                                        publisher_join: '{{ $bounty->publisher->created_at->format('d M Y') }}',
                                                        budget: '{{ number_format($bounty->remaining_budget, 0, ',', '.') }}',
                                                        platform: {{ json_encode($bounty->platform) }},
                                                        image_url: '{{ Storage::url($bounty->image_path) }}',
                                                        publisher_avatar_url: '{{ !empty($bounty->publisher->avatar_url)
                                                            ? (Str::startsWith($bounty->publisher->avatar_url, ['http', 'https'])
                                                                ? $bounty->publisher->avatar_url
                                                                : Storage::url($bounty->publisher->avatar_url))
                                                            : '' }}',
                                                        
                                                        // [UPDATE] Mengisi Data Baru
                                                        deadline: '{{ \Carbon\Carbon::parse($bounty->deadline_at)->format('d F Y') }}',
                                                        hashtags: '{{ $bounty->required_hashtags ?? '-' }}',
                                                        mentions: '{{ $bounty->required_mentions ?? '-' }}',
                                                        strategy: '{{ strtolower($bounty->distribution_strategy) }}', 
                                                        rate: '{{ number_format($bounty->rate_per_view ?? 0, 2, ',', '.') }}', 
                                                        tiers: {{ json_encode($bounty->rules['tiers'] ?? []) }}
                                                    }
                                                "
                                                    class="w-full text-left px-3 py-2 bg-indigo-50 hover:bg-indigo-100 rounded-lg text-xs font-bold text-indigo-600 border border-indigo-100 transition flex items-center justify-between group">
                                                    <span>Lihat Detail Lengkap</span>
                                                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>

                                        {{-- Kolom 2: Publisher --}}
                                        <td class="px-6 py-4 align-top">
                                            <div class="flex items-center gap-3">
                                                <img class="w-8 h-8 rounded-full object-cover border border-white shadow-sm"
                                                 src="{{ $bounty->publisher->avatar_url ? (str_starts_with($bounty->publisher->avatar_url, 'http') ? $bounty->publisher->avatar_url : asset('storage/' . $bounty->publisher->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode($bounty->publisher->name) . '&background=random&color=fff' }}"
                                                 alt="{{ $bounty->publisher->name }}">
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900">
                                                        {{ $bounty->publisher->name }}</p>
                                                    <p class="text-xs text-gray-400">{{ $bounty->publisher->email }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-400 mt-1">Join:
                                                        {{ $bounty->publisher->created_at->format('d M Y') }}</p>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Kolom 3: Budget --}}
                                        <td class="px-6 py-4 align-top">
                                            <div class="space-y-2">
                                                <div>
                                                    <p
                                                        class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                                        Total Budget</p>
                                                    <p class="text-sm font-black text-green-600 font-mono">
                                                        Rp {{ number_format($bounty->remaining_budget, 0, ',', '.') }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p
                                                        class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">
                                                        Platform</p>
                                                    <div class="flex gap-1 mt-1">
                                                        @foreach ($bounty->platform as $plat)
                                                            <span
                                                                class="px-2 py-1 bg-gray-100 border border-gray-200 rounded text-[10px] font-bold text-gray-600 capitalize">
                                                                {{ str_replace('_', ' ', $plat) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Kolom 4: Aksi --}}
                                        <td class="px-6 py-4 align-middle text-center">
                                            <div class="flex flex-col gap-2">
                                                <form action="{{ route('admin.bounties.approve', $bounty->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                        onclick="return confirm('Yakin ingin menerbitkan campaign ini?')"
                                                        class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold shadow-lg shadow-green-200 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Approve
                                                    </button>
                                                </form>
                                                <button type="button"
                                                    @click="
                                                    showRejectModal = true; 
                                                    rejectAction = '{{ route('admin.bounties.reject', $bounty->id) }}';
                                                    selectedTitle = '{{ addslashes($bounty->title) }}';
                                                "
                                                    class="w-full py-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Tolak & Refund
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- MODAL REJECT (TIDAK BERUBAH) --}}
                <div x-show="showRejectModal" style="display: none;"
                    class="fixed inset-0 z-[60] flex items-center justify-center px-4" x-transition.opacity>
                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showRejectModal = false">
                    </div>
                    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 relative z-10 transform transition-all"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-black text-gray-900">Tolak Campaign</h3>
                            <button @click="showRejectModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="bg-rose-50 border border-rose-100 p-3 rounded-lg mb-4 text-xs text-rose-700">
                            <strong>Perhatian:</strong> Menolak campaign akan otomatis mengembalikan seluruh saldo
                            (Refund) ke Publisher. Tindakan ini tidak dapat dibatalkan.
                        </div>
                        <form :action="rejectAction" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Alasan
                                    Penolakan (Wajib)</label>
                                <textarea name="reason" rows="4" required
                                    placeholder="Contoh: Video mengandung unsur kekerasan / Link tidak valid..."
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none transition placeholder-gray-400"></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="showRejectModal = false"
                                    class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl text-xs uppercase tracking-wider transition">Batal</button>
                                <button type="submit"
                                    class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-lg shadow-rose-200 transition">Tolak
                                    & Refund</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 
                |--------------------------------------------------------------------------
                | MODAL DETAIL CAMPAIGN (UPDATED)
                |--------------------------------------------------------------------------
                --}}
                <div x-show="showDetailModal" style="display: none;"
                    class="fixed inset-0 z-[60] flex items-center justify-center px-4" x-transition.opacity>

                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showDetailModal = false">
                    </div>

                    <div class="bg-white w-full max-w-4xl rounded-3xl shadow-2xl p-0 relative z-10 transform transition-all overflow-hidden flex flex-col max-h-[90vh]"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">

                        {{-- Header Modal --}}
                        <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-start bg-gray-50/30">
                            <div class="flex gap-5">
                                <img :src="detail.image_url"
                                    class="w-16 h-16 rounded-xl object-cover shadow-sm border border-gray-200">
                                <div>
                                    <h3 class="text-2xl font-black text-gray-900" x-text="detail.title"></h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 uppercase tracking-wide"
                                            x-text="detail.category"></span>
                                        <span class="text-xs text-gray-500">| Detail Review</span>
                                    </div>
                                </div>
                            </div>
                            <button @click="showDetailModal = false"
                                class="text-gray-400 hover:text-gray-600 p-2 hover:bg-gray-100 rounded-full transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        {{-- Body Modal (Grid Layout) --}}
                        <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                                {{-- KOLOM KIRI (2/3): Deskripsi, Link, Syarat --}}
                                <div class="md:col-span-2 space-y-6">

                                    {{-- INSTRUKSI --}}
                                    <div>
                                        <h4
                                            class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            Instruksi Lengkap
                                        </h4>
                                        <div class="text-gray-600 text-sm leading-snug font-light p-5 bg-gray-50 rounded-2xl shadow-lg border border-gray-200 whitespace-pre-wrap max-h-64 overflow-y-auto pr-2 custom-scrollbar"
                                            x-html="detail.description">
                                        </div>
                                    </div>

                                    {{-- [UPDATE] Skema Pembayaran / Reward --}}
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label
                                                class="block text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                                Skema Hadiah (Reward)
                                            </label>

                                            {{-- Badge Penanda Jenis Strategi --}}
                                            <span
                                                class="text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wide border"
                                                :class="detail.strategy === 'cpm' ?
                                                    'bg-blue-50 text-blue-600 border-blue-100' :
                                                    'bg-purple-50 text-purple-600 border-purple-100'"
                                                x-text="detail.strategy === 'cpm' ? 'STRATEGI: CPM (PAY PER VIEW)' : 'STRATEGI: PERFORMANCE TIERED'">
                                            </span>
                                        </div>

                                        {{-- Tampilan CPM --}}
                                        <template x-if="detail.strategy === 'cpm'">
                                            <div
                                                class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-between">
                                                <span class="text-sm font-medium text-emerald-800">Rate per 1
                                                    View</span>
                                                <span class="text-lg font-black text-emerald-600">Rp <span
                                                        x-text="detail.rate"></span></span>
                                            </div>
                                        </template>

                                        {{-- Tampilan Tiered (Performance) --}}
                                        <template x-if="detail.strategy === 'performance_tiered'">
                                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                                <table class="w-full text-sm text-left">
                                                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                                        <tr>
                                                            <th class="px-4 py-2">Target Views</th>
                                                            <th class="px-4 py-2 text-right">Hadiah</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100">
                                                        <template x-for="tier in detail.tiers ">
                                                            <tr>
                                                                {{-- Format Angka Ribuan untuk Target Views --}}
                                                                <td class="px-4 py-2 font-bold text-gray-800"><span
                                                                        x-text="parseInt(tier.target_views).toLocaleString('id-ID')"></span>
                                                                    Views</td>
                                                                {{-- Format Rupiah untuk Reward --}}
                                                                <td
                                                                    class="px-4 py-2 text-right font-bold text-emerald-600">
                                                                    Rp <span
                                                                        x-text="parseInt(tier.reward).toLocaleString('id-ID')"></span>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                        {{-- Jika Data Tier Kosong --}}
                                                        <template x-if="!detail.tiers || detail.tiers.length === 0">
                                                            <tr>
                                                                <td colspan="2"
                                                                    class="px-4 py-2 text-center text-gray-400 italic text-xs">
                                                                    Belum ada data tier</td>
                                                            </tr>
                                                        </template>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- [UPDATE] Wajib Hashtag & Mention --}}
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                            <label
                                                class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Wajib
                                                Hashtag</label>
                                            <p class="text-sm font-bold text-gray-800 font-mono break-words"
                                                x-text="detail.hashtags || '-'"></p>
                                        </div>
                                        <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                            <label
                                                class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Wajib
                                                Mention</label>
                                            <p class="text-sm font-bold text-gray-800 font-mono break-words"
                                                x-text="detail.mentions || '-'"></p>
                                        </div>
                                    </div>
                                </div>

                                {{-- KOLOM KANAN (1/3): Info Publisher & Budget --}}
                                <div class="space-y-6">
                                    {{-- Card Budget --}}
                                    <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-100 text-center">
                                        <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-1">
                                            Total Budget</p>
                                        <p class="text-2xl font-black text-emerald-700">Rp <span
                                                x-text="detail.budget"></span></p>
                                    </div>

                                    {{-- [UPDATE] Deadline Info --}}
                                    <div
                                        class="p-4 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-between">
                                        <span
                                            class="text-xs font-bold text-amber-700 uppercase tracking-wide">Deadline</span>
                                        <span
                                            class="text-gray-900 font-bold text-sm" x-text="detail.deadline"></span>
                                    </div>

                                    {{-- Info Publisher --}}
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Publisher
                                            Info</label>
                                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50/50">
                                            <div class="flex items-center gap-3 mb-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600 overflow-hidden relative border border-indigo-200">
                                                    {{-- A. Jika ada URL gambar (http/storage) --}}
                                                    <template x-if="detail.publisher_avatar_url">
                                                        <img :src="detail.publisher_avatar_url"
                                                            class="w-full h-full object-cover">
                                                    </template>
                                                    {{-- B. Jika tidak ada, pakai inisial --}}
                                                    <template x-if="!detail.publisher_avatar_url">
                                                        <span x-text="detail.publisher_name.charAt(0)"></span>
                                                    </template>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900"
                                                        x-text="detail.publisher_name"></p>
                                                    <p class="text-[10px] text-gray-500 uppercase">Verified Publisher
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="space-y-2 text-xs text-gray-600">
                                                <div class="flex justify-between border-b border-gray-200 pb-2">
                                                    <span>Email</span>
                                                    <span class="font-medium" x-text="detail.publisher_email"></span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Bergabung</span>
                                                    <span class="font-medium" x-text="detail.publisher_join"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Platform Badges --}}
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Platform
                                            Target</label>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="plat in detail.platform">
                                                <span
                                                    class="px-3 py-1.5 bg-gray-100 border border-gray-200 rounded-lg text-xs font-bold text-gray-600 capitalize"
                                                    x-text="plat.replace('_', ' ')"></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                                </path>
                                            </svg>
                                            Link Materi
                                        </label>
                                        <a :href="detail.source_link" target="_blank"
                                            class="flex items-center gap-3 p-3 rounded-xl border border-indigo-100 bg-indigo-50/50 hover:bg-indigo-50 transition group cursor-pointer">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center text-gray-600 group-hover:bg-indigo-200 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-xs font-bold text-gray-500 uppercase">Klik untuk membuka
                                                </p>
                                                <p class="text-sm font-bold text-gray-800 truncate"
                                                    x-text="detail.source_link"></p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                </div>
                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="p-6 border-t border-gray-100 bg-gray-50/30 flex justify-end gap-3">
                            <button @click="showDetailModal = false"
                                class="px-6 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-bold transition shadow-sm">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
