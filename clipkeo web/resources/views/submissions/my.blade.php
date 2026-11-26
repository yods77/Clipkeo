<x-app-layout>
    {{-- Main Wrapper --}}
    <div class="pt-32 pb-12 min-h-screen" x-data="{
        isOpen: false,
        data: {},
        openReport(item) {
            this.data = item;
            this.isOpen = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- HEADER & BACK BUTTON --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 px-2">
                <div>
                    <h2 class="text-3xl font-black text-gray-800 tracking-tighter">SETORAN VIDEO</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mt-1">Pantau status
                        validasi dan performa karya Anda</p>
                </div>

                <a href="{{ route('dashboard') }}"
                    class="btn-neumorph-primary inline-flex items-center px-6 py-3 rounded-2xl text-[10px] font-black text-gray-600 uppercase tracking-widest">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            {{-- CONTAINER TABEL (Embossed) --}}
            <div class="card-neumorphism rounded-[2.5rem] overflow-hidden border-none">

                {{-- TABLE HEADER INFO --}}
                <div class="px-8 py-6 border-b border-gray-100 bg-white/30 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 card-inset-panel flex items-center justify-center text-emerald-500 rounded-xl">
                            <i class="fas fa-video text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-gray-700 uppercase tracking-widest leading-none">History
                                Submission</h3>
                        </div>
                    </div>
                    <span
                        class="text-[9px] font-black px-4 py-1.5 card-timbul rounded-full text-emerald-600 tracking-widest">
                        {{ $submissions->count() }} VIDEO TERDATA
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead
                            class="bg-white text-gray-600 uppercase text-[9px] font-black tracking-[0.2em] border-b border-gray-50">
                            <tr>
                                <th class="px-8 py-5">Tanggal</th>
                                <th class="px-8 py-5">Misi / Sayembara</th>
                                <th class="px-8 py-5">Detail Laporan</th>
                                <th class="px-8 py-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white/20">
                            @forelse ($submissions as $sub)
                                @php
                                    $strategy = $sub->bounty->distribution_strategy;
                                    $schemeLabel = '-';

                                    if ($strategy === 'cpm') {
                                        // Jika CPM, tampilkan nominal per view-nya
                                        $rateValue = $sub->bounty->rate_per_view ?? 0;
                                        $estimate = $sub->views_count * $rateValue;

                                        // --- PERBAIKAN DISINI ---
                                        // Gunakan angka 2 (atau lebih) agar desimal tampil, misal: 0,5
                                        // Kita gunakan trim untuk menghilangkan ,00 jika angkanya bulat
                                        $formattedRate = number_format($rateValue, 2, ',', '.');
                                        $formattedRate = rtrim(rtrim($formattedRate, '0'), ','); // Menghapus nol tak berguna di belakang

                                        $schemeLabel = "CPM Rp $formattedRate / View";
                                    } elseif ($strategy === 'performance_tiered') {
                                        // Jika Tiered, tampilkan label khusus
                                        $schemeLabel = 'Performance Tiered';
                                    } else {
                                        // Jika Flat
                                        $reward = number_format($sub->bounty->reward_amount ?? 0, 0, ',', '.');
                                        $schemeLabel = "Reward Tetap (Rp $reward)";
                                    }
                                    $estimate = 0;
                                    $strategy = $sub->bounty->distribution_strategy;

                                    if ($strategy === 'cpm') {
                                        $rate = $sub->bounty->rate_per_view ?? 0;
                                        $estimate = $sub->views_count * $rate;
                                    } elseif ($strategy === 'performance_tiered') {
                                        $tiers = $sub->bounty->rules['tiers'] ?? [];
                                        $sortedTiers = collect($tiers)->sortBy('target_views');
                                        foreach ($sortedTiers as $tier) {
                                            if ($sub->views_count >= (int) $tier['target_views']) {
                                                $estimate = (int) $tier['reward'];
                                            }
                                        }
                                    } else {
                                        $estimate = $sub->bounty->reward_amount ?? 0;
                                    }

                                    $link = strtolower($sub->video_url);
                                    $platformName = 'General';
                                    $platformIcon = 'fas fa-globe'; // Icon default

                                    if (str_contains($link, 'tiktok')) {
                                        $platformName = 'TikTok';
                                        $platformIcon = 'fab fa-tiktok';
                                    } elseif (str_contains($link, 'instagram')) {
                                        $platformName = 'Instagram';
                                        $platformIcon = 'fab fa-instagram';
                                    } elseif (str_contains($link, 'youtube') || str_contains($link, 'youtu.be')) {
                                        $platformName = 'YouTube';
                                        $platformIcon = 'fab fa-youtube';
                                    }

                                    // --- BUNGKUS DATA UNTUK MODAL ---
                                    $modalData = [
                                        'id' => $sub->id,
                                        'sub_id_formatted' => '#SUB-' . str_pad($sub->id, 5, '0', STR_PAD_LEFT), // Jadinya #SUB-00123
                                        'title' => $sub->bounty->title,
                                        'platform_name' => $platformName,
                                        'platform_icon' => $platformIcon,
                                        'views' => number_format($sub->views_count, 0, ',', '.'),
                                        'status' => $sub->status, // pending, approved, rejected
                                        'estimate' => number_format($estimate, 0, ',', '.'),
                                        'date' => $sub->created_at->translatedFormat('d M Y, H:i'),
                                        'rejection_reason' => $sub->rejection_reason,
                                        'note' => $sub->reject_reason,
                                        'video_link' => $sub->video_url,
                                        'reward_scheme' => $schemeLabel,
                                        'is_income' => $sub->status === 'approved', // True jika sudah jadi pendapatan
                                    ];
                                @endphp
                                {{-- Baris: Flat by default, Timbul saat Hover --}}
                                <tr class="group transition-all duration-300">

                                    {{-- KOLOM 1: JUDUL & EARNINGS --}}
                                    {{-- KOLOM 4: WAKTU --}}
                                    <td class="px-8 py-6">
                                        <div class="text-[11px] font-black text-gray-800 uppercase tracking-tighter">
                                            {{ $sub->created_at->format('d M Y') }}
                                        </div>

                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-gray-800 font-black text-sm tracking-tight mb-2">
                                            {{ $sub->bounty->title }}
                                        </div>

                                        @if ($sub->prize_amount > 0)
                                            <div
                                                class="inline-flex items-center gap-1.5 text-[10px] font-black text-emerald-600 px-2.5 py-1 rounded-md tracking-widest">
                                                <i class="fas fa-coins"></i>
                                                Earned: Rp {{ number_format($sub->prize_amount) }}
                                            </div>
                                        @elseif($sub->status == 'pending')
                                            <div
                                                class="inline-flex items-center gap-1.5 text-[9px] font-black text-amber-500 px-2.5 py-1 rounded-md tracking-widest">
                                                <i class="fas fa-hourglass-half"></i>
                                                Menunggu Review
                                            </div>
                                        @elseif($sub->status == 'approved' && $sub->prize_amount == 0)
                                            <div class="inline-flex items-center gap-1.5 text-[9px] font-black text-gray-500 px-2.5 py-1 rounded-md tracking-widest"
                                                title="Views video ini telah dihitung ke dalam total akumulasi campaign">
                                                <i class="fas fa-chart-line"></i>
                                                Views Terhitung
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 text-[9px] font-black text-rose-500 px-2.5 py-1 rounded-md tracking-widest"
                                                title="Views video ini telah dihitung ke dalam total akumulasi campaign">
                                                <i class="fas fa-times-circle"></i>
                                                Ditolak
                                            </div>
                                        @endif
                                    </td>

                                    {{-- KOLOM 2: Tombol Laporan --}}
                                    <td class="px-8 py-6 relative flex items-center gap-2">
                                        <button type="button"
                                            data-json="{{ json_encode($modalData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                                            @click="openReport(JSON.parse($el.dataset.json))"
                                            class="px-4 py-2 text-gray-400 btn-neumorph-primary rounded-xl group transition-all duration-300">
                                            <i class="fas fa-file-invoice text-[16px]"></i>
                                        </button>
                                        <span class="font-black text-sm tracking-tight">Lihat Laporan</span>
                                    </td>

                                    {{-- KOLOM 3: STATUS --}}
                                    <td class="px-8 py-6">
                                        @if ($sub->status == 'pending')
                                            <div class="flex flex-col items-center gap-1">
                                                <span
                                                    class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 card-inset-panel">
                                                    Pending
                                                </span>
                                                <span
                                                    class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                                    {{ $sub->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        @elseif($sub->status == 'approved')
                                            <div class="flex flex-col items-center gap-1">
                                                <span
                                                    class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 card-inset-panel">
                                                    Disetujui
                                                </span>
                                                @if ($sub->payout_status == 'paid')
                                                    <span
                                                        class="text-[8px] text-emerald-500 font-black uppercase tracking-[0.2em] animate-pulse">Lunas</span>
                                                @endif
                                                <span
                                                    class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                                    {{ $sub->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        @elseif($sub->status == 'rejected')
                                            @php
                                                $isBudgetIssue = \Illuminate\Support\Str::contains(
                                                    strtolower($sub->rejection_reason),
                                                    ['budget', 'habis', 'sold out', 'auto-reject'],
                                                );
                                            @endphp

                                            <div class="flex flex-col items-center gap-1">
                                                <span
                                                    class="inline-flex items-center px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 card-inset-panel">
                                                    Ditolak
                                                </span>

                                                @if ($isBudgetIssue)
                                                    <div class="mt-2 text-rose-500 flex items-center gap-1">
                                                        <i class="fas fa-lock text-[10px]"></i>
                                                        <span
                                                            class="text-[8px] font-black uppercase tracking-tighter">Budget
                                                            Habis</span>
                                                    </div>
                                                @else
                                                    {{-- Bagian Alasan Tanpa Tooltip --}}
                                                    <div class="flex flex-col items-center mt-2">
                                                        <span
                                                            class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                                            {{ $sub->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center justify-center opacity-40">
                                            <div
                                                class="w-16 h-16 card-inset-panel rounded-full flex items-center justify-center mb-4">
                                                <i class="fas fa-video-slash text-2xl text-gray-400"></i>
                                            </div>
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                                Belum
                                                ada riwayat setoran video</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER LEGEND --}}
            <div class="mt-10 px-4 text-center">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] leading-relaxed">
                    <i class="fas fa-info-circle text-emerald-400 mr-1"></i>
                    Data diperbarui secara realtime. Gunakan dashboard untuk monitoring performa kumulatif.
                </p>
            </div>
        </div>
        <template x-teleport="body">
            <div x-show="isOpen" class="fixed inset-0 z-[999] overflow-y-auto" style="display: none;">

                {{-- Backdrop Blur --}}
                <div @click="isOpen = false" x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-200/60 backdrop-blur-sm"></div>

                {{-- Modal Content (Receipt Style) --}}
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-10 scale-95"
                        class="relative w-full max-w-sm bg-[#f3f4f6] rounded-[2rem] p-8 shadow-[20px_20px_60px_#bec3c9,-20px_-20px_60px_#ffffff] border border-white/40">

                        {{-- Close Button (Pojok Kanan Atas) --}}
                        <button @click="isOpen = false"
                            class="absolute top-6 right-6 w-8 h-8 rounded-full bg-[#f3f4f6] shadow-[5px_5px_10px_#bec3c9,-5px_-5px_10px_#ffffff] flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors active:shadow-[inset_5px_5px_10px_#bec3c9,inset_-5px_-5px_10px_#ffffff]">
                            <i class="fas fa-times text-xs"></i>
                        </button>

                        {{-- Header Icon --}}
                        <div class="flex justify-center mb-6">
                            <div
                                class="w-16 h-16 rounded-2xl bg-[#f3f4f6] shadow-[inset_5px_5px_10px_#bec3c9,inset_-5px_-5px_10px_#ffffff] flex items-center justify-center">
                                <i class="fas fa-receipt text-2xl text-gray-600"></i>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="text-center space-y-6">

                            {{-- Status Badge --}}
                            <div>
                                <span
                                    class="px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-widest shadow-[5px_5px_10px_#bec3c9,-5px_-5px_10px_#ffffff]"
                                    :class="{
                                        'text-emerald-600': data.status === 'approved',
                                        'text-amber-600': data.status === 'pending',
                                        'text-red-600': data.status === 'rejected'
                                    }"
                                    x-text="data.status === 'approved' ? 'Diterima' : (data.status === 'pending' ? 'Sedang Direview' : 'Ditolak')">
                                </span>
                            </div>

                            {{-- Nominal Uang --}}
                            <div class="relative py-4">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2"
                                    x-text="data.is_income ? 'TOTAL PENDAPATAN' : 'ESTIMASI PENDAPATAN'">
                                </p>
                                <h2 class="text-3xl font-black text-emerald-700 tracking-tighter"
                                    style="font-family: 'Space Mono', monospace;">
                                    Rp <span x-text="data.estimate"></span>
                                </h2>
                            </div>

                            {{-- Detail List (Inset Style) --}}
                            <div
                                class="bg-[#f3f4f6] rounded-xl p-5 shadow-[inset_5px_5px_10px_#bec3c9,inset_-5px_-5px_10px_#ffffff] space-y-3 text-left">
                                {{-- [BARU] Submission ID --}}
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">No. Ref</span>
                                    <span
                                        class="text-[10px] font-black text-gray-500 font-mono tracking-widest bg-gray-200/50 px-2 py-0.5 rounded"
                                        x-text="data.sub_id_formatted"></span>
                                </div>
                                {{-- Tanggal --}}
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Tanggal</span>
                                    <span class="text-[10px] font-bold text-gray-600" x-text="data.date"></span>
                                </div>

                                {{-- Campaign --}}
                                <div class="flex justify-between items-start gap-4">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Campaign</span>
                                    <span class="text-[10px] font-black text-gray-700 text-right leading-tight"
                                        x-text="data.title"></span>
                                </div>

                                {{-- [BARU] Platform --}}
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Platform</span>
                                    <div class="flex items-center gap-1.5">
                                        {{-- Icon Platform Dinamis --}}
                                        <i :class="data.platform_icon" class="text-[10px] text-gray-600"></i>
                                        <span class="text-[10px] font-black text-gray-700 uppercase tracking-wide"
                                            x-text="data.platform_name"></span>
                                    </div>
                                </div>

                                {{-- Skema Reward --}}
                                <div class="flex justify-between items-start gap-4">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Skema Reward</span>
                                    <span class="text-[10px] font-black text-gray-700 text-right leading-tight"
                                        x-text="data.reward_scheme"></span>
                                </div>

                                {{-- Views --}}
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase">Views</span>
                                    <span class="text-[10px] font-bold text-gray-800 font-mono"><span
                                            x-text="data.views"></span> Views</span>
                                </div>
                            </div>

                            {{-- Catatan Admin (Jika Ada/Rejected) --}}
                            <template x-if="data.status === 'rejected' || data.note">
                                <div
                                    class="bg-red-50/50 border border-red-100 p-4 rounded-xl text-center relative overflow-hidden">
                                    {{-- Dekorasi Garis Merah di Samping --}}
                                    <div class="absolute top-0 left-0 w-1 h-full bg-red-400"></div>

                                    <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-1">
                                        Alasan Penolakan
                                    </p>

                                    <p class="text-[10px] font-bold text-red-600 leading-relaxed"
                                        x-text="data.status === 'rejected' ? (data.rejection_reason || 'Maaf, submission Anda belum memenuhi syarat.') : data.note">
                                    </p>
                                </div>
                            </template>

                            {{-- Tombol Link Video --}}
                            <a :href="data.video_link" target="_blank"
                                class="inline-block text-[10px] font-bold text-emerald-500 hover:text-emerald-700 underline decoration-emerald-300 underline-offset-4 cursor-pointer">
                                Lihat Video Asli <i class="fas fa-external-link-alt ml-1"></i>
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
