<x-app-layout>
    <div class="pt-32 pb-12 bg-#f3f4f6 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- 
            |--------------------------------------------------------------------------
            | HEADER SECTION (DINAMIS DENGAN DROPDOWN)
            |--------------------------------------------------------------------------
            --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-6">
                {{-- Judul & Subtitle --}}
                <div>
                    <h2 class="text-4xl font-black text-gray-800 tracking-tighter leading-none">{{ $title }}</h2>
                    <div class="flex items-center gap-2 mt-2 ml-1">
                        <div
                            class="progress-track w-2 h-2 rounded-full bg-indigo-500 animate-pulse shadow-[0_0_8px_rgba(79,70,229,0.5)]">
                        </div>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]">
                            {{ $subTitle }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    {{-- [NAVIGASI] Dropdown Campaign Neumorph --}}
                    <div class="relative group">

                        <div x-data="{
                            open: false,
                            selectedLabel: '{{ isset($bounty) ? Str::limit($bounty->title, 25) : 'Global Overview' }}',
                            selectedIcon: '{{ isset($bounty) ? '🎯' : '📊' }}'
                        }" @click.outside="open = false" class="relative inline-block">

                            {{-- Trigger Button (Tampilan yang Terlihat) --}}
                            <button type="button" @click="open = !open"
                                class="input-neumorph-inset flex items-center justify-between min-w-[260px] px-4 py-2 outline-none border-none transition-all group">

                                <div class="flex items-center gap-3">
                                    <span class="text-lg" x-text="selectedIcon"></span>
                                    <span
                                        class="text-[11px] font-black text-gray-700 uppercase tracking-widest truncate max-w-[150px]"
                                        x-text="selectedLabel"></span>
                                </div>

                                <div class="progress-track p-1 rounded-lg transition-transform duration-300"
                                    :class="open ? 'rotate-180' : ''">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </button>

                            {{-- Dropdown Menu (List Pilihan) --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                class="card-timbul absolute z-[100] mt-4 w-full p-2 !rounded-2xl border-none"
                                style="display: none;">

                                <ul class="max-h-80 overflow-y-auto custom-scrollbar space-y-1">
                                    {{-- Pilihan Global Overview --}}
                                    <li>
                                        <a href="{{ route('analytics.overview') }}"
                                            class="hover-cekung flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group {{ !isset($bounty) ? 'nav-item-active' : '' }}">
                                            <span class="text-base">📊</span>
                                            <span
                                                class="text-[10px] font-black uppercase tracking-widest {{ !isset($bounty) ? 'text-indigo-600' : 'text-gray-500' }}">Global
                                                Overview</span>
                                        </a>
                                    </li>

                                    {{-- Label Group --}}
                                    <li class="px-4 py-2 mt-2">
                                        <span
                                            class="text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">Pilih
                                            Campaign</span>
                                    </li>

                                    {{-- List Campaign --}}
                                    @foreach ($allBounties as $b)
                                        <li>
                                            <a href="{{ route('bounties.analytics', $b->id) }}"
                                                class="hover-cekung flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group {{ isset($bounty) && $bounty->id == $b->id ? 'nav-item-active' : '' }}">
                                                <span class="text-base">🎯</span>
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-widest truncate {{ isset($bounty) && $bounty->id == $b->id ? 'text-indigo-600' : 'text-gray-500' }}">
                                                    {{ Str::limit($b->title, 25) }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Dashboard Utama --}}
                    <a href="{{ route('dashboard') }}" class="btn-neumorph-primary !py-3.5 !px-6 !rounded-2xl">
                        {{-- Efek Glow Refleksi --}}
                        <div
                            class="absolute top-0 -left-full w-full h-full bg-gradient-to-r from-transparent via-white/10 to-transparent group-hover:left-full transition-all duration-700">
                        </div>

                        <div class="flex items-center gap-2.5 relative z-10">
                            <svg class="w-4 h-4 text-gray-800 group-hover:scale-110 transition-transform duration-300"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            <span
                                class="text-[11px] font-black text-gray-800 uppercase tracking-[0.2em]">Dashboard</span>
                        </div>
                    </a>
                </div>
            </div>
            {{-- 
            |--------------------------------------------------------------------------
            | 1. HERO STATS
            |--------------------------------------------------------------------------
            --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Card 1: Views --}}
                <div class="card-timbul p-6 rounded-3xl relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Impressions</p>
                        <h3 class="text-3xl font-black text-gray-900">{{ number_format($totalViews) }}</h3>
                        <p class="text-[10px] text-green-500 font-bold mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            Views Terakumulasi
                        </p>
                    </div>
                    <div
                        class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-50 rounded-full group-hover:scale-110 transition-transform">
                    </div>
                    <svg class="w-12 h-12 text-indigo-100 absolute bottom-4 right-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </div>

                {{-- Card 2: Spend --}}
                <div class="card-timbul p-6 rounded-3xl relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Spend</p>
                        <h3 class="text-3xl font-black text-gray-900">Rp {{ number_format($totalSpend / 1000000, 1) }}M
                        </h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1">Real: Rp {{ number_format($totalSpend) }}
                        </p>
                    </div>
                    <div
                        class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-110 transition-transform">
                    </div>
                    <svg class="w-12 h-12 text-emerald-100 absolute bottom-4 right-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>

                {{-- Card 3: eCPV --}}
                <div class="card-timbul p-6 rounded-3xl relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">eCPV (Cost Per View)
                        </p>
                        <h3 class="text-3xl font-black text-gray-900">Rp {{ number_format($eCPV, 2) }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1">Rata-rata biaya per view</p>
                    </div>
                    <div
                        class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-50 rounded-full group-hover:scale-110 transition-transform">
                    </div>
                    <svg class="w-12 h-12 text-amber-100 absolute bottom-4 right-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>

                {{-- Card 4: Content --}}
                <div class="card-timbul p-6 rounded-3xl relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total UGC Video</p>
                        <h3 class="text-3xl font-black text-gray-900">{{ number_format($totalContent) }}</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1">Konten tercipta</p>
                    </div>
                    <div
                        class="absolute -right-4 -bottom-4 w-24 h-24 bg-rose-50 rounded-full group-hover:scale-110 transition-transform">
                    </div>
                    <svg class="w-12 h-12 text-rose-100 absolute bottom-4 right-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | 2. CHARTS SECTION
            |--------------------------------------------------------------------------
            --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                {{-- 1. GRAFIK PERTUMBUHAN VIEWS (MAIN CHART) --}}
                <div class="card-timbul lg:col-span-2 p-8 border-none relative overflow-hidden">
                    <div class="flex justify-between items-center mb-8 relative z-10">
                        <div>
                            <h4 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Pertumbuhan Views
                            </h4>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Statistik 30
                                Hari Terakhir</p>
                        </div>

                        <div class="card-timbul !px-4 !py-2 !rounded-xl flex items-center gap-2 border-none">
                            <span
                                class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_#10b981]"></span>
                            <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Live
                                Data</span>
                        </div>
                    </div>

                    {{-- Area Grafik: Efek Inset (Cekung) --}}
                    <div class="card-inset-panel p-6 !rounded-[2.5rem] bg-gray-50/30">
                        <div class="relative h-80 w-full">
                            <canvas id="viewsTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- 2. SEBARAN PLATFORM (PROGRESS BAR STYLE) --}}
                <div class="card-timbul p-8 border-none relative overflow-hidden">
                    <div class="flex justify-between items-start mb-10 relative z-10">
                        <div>
                            <h4 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Sebaran Platform
                            </h4>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Distribusi
                                Konten</p>
                        </div>
                        <div class="progress-track px-3 py-1.5 !rounded-xl shadow-inner">
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-tighter">
                                Total:
                                {{ is_array($platformStats) ? array_sum($platformStats) : $platformStats->sum() }}
                            </span>
                        </div>
                    </div>

                    {{-- Platform Progress List --}}
                    <div class="space-y-10 relative z-10 pt-2">
                        @php
                            $total = is_array($platformStats) ? array_sum($platformStats) : $platformStats->sum();
                            $platforms = [
                                [
                                    'name' => 'TikTok',
                                    'key' => 'TikTok',
                                    'color' => 'bg-indigo-500',
                                    'text' => 'text-indigo-600',
                                ],
                                [
                                    'name' => 'Instagram Reels',
                                    'key' => 'Instagram',
                                    'color' => 'bg-pink-500',
                                    'text' => 'text-pink-600',
                                ],
                                [
                                    'name' => 'YouTube Shorts',
                                    'key' => 'YouTube',
                                    'color' => 'bg-red-500',
                                    'text' => 'text-red-600',
                                ],
                            ];
                        @endphp

                        @foreach ($platforms as $p)
                            @php
                                $count = $platformStats[$p['key']] ?? 0;
                                $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                            @endphp
                            <div class="space-y-3">
                                <div class="flex justify-between items-end px-1">
                                    <span
                                        class="text-[11px] font-black text-gray-700 uppercase tracking-widest">{{ $p['name'] }}</span>
                                    <div class="card-timbul !px-2 !py-0.5 !rounded-lg border-none translate-y-1">
                                        <span
                                            class="text-[10px] font-mono font-black {{ $p['text'] }}">{{ $count }}
                                            VID</span>
                                    </div>
                                </div>

                                {{-- Progress Track (Cekung) --}}
                                <div class="progress-track h-4 w-full rounded-full p-1 shadow-inner overflow-hidden">
                                    {{-- Progress Bar (Timbul) --}}
                                    <div class="{{ $p['color'] }} h-full rounded-full transition-all duration-1000 ease-out shadow-[2px_0_5px_rgba(0,0,0,0.1)]"
                                        style="width: {{ $percentage }}%">
                                    </div>
                                </div>

                                <div class="flex justify-end pr-1">
                                    <span
                                        class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">{{ number_format($percentage, 1) }}%
                                        Coverage</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tip Box Neumorph --}}
                    <div class="mt-12 p-4 card-inset-panel !rounded-2xl border-none opacity-60">
                        <p
                            class="text-[9px] text-gray-500 font-bold leading-relaxed uppercase tracking-tight text-center">
                            Data diperbarui secara otomatis berdasarkan laporan clipper yang masuk.
                        </p>
                    </div>
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | 3. LEADERBOARD
            |--------------------------------------------------------------------------
            --}}
            <div class="card-timbul !p-0 !rounded-[2.5rem] overflow-hidden border-none">
    {{-- Header Tabel --}}
    <div class="p-8 flex justify-between items-center bg-transparent relative z-10">
        <div>
            <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Top Performing Clippers</h3>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Creator dengan kontribusi tertinggi</p>
        </div>
        <div class="progress-track p-2 rounded-xl">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
        </div>
    </div>

    <div class="overflow-x-auto px-4 pb-4">
        <table class="w-full text-left border-separate border-spacing-y-3">
            <thead class="text-gray-400 uppercase text-[10px] font-black tracking-[0.2em]">
                <tr>
                    <th class="px-6 py-4">Rank</th>
                    <th class="px-6 py-4">Clipper Details</th>
                    <th class="px-6 py-4 text-center">Submissions</th>
                    <th class="px-6 py-4">Performance</th>
                    <th class="px-6 py-4 text-right">Earnings</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @forelse($topClippers as $index => $clipper)
                    <tr class="hover-cekung group transition-all duration-300 rounded-2xl cursor-default">
                        {{-- Rank Column --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if ($index == 0)
                                    <div class="card-timbul !p-0 w-10 h-10 rounded-full flex items-center justify-center bg-amber-50 shadow-[4px_4px_10px_rgba(245,158,11,0.2)]">
                                        <span class="text-xl">🥇</span>
                                    </div>
                                @elseif($index == 1)
                                    <div class="card-timbul !p-0 w-10 h-10 rounded-full flex items-center justify-center bg-slate-50">
                                        <span class="text-xl">🥈</span>
                                    </div>
                                @elseif($index == 2)
                                    <div class="card-timbul !p-0 w-10 h-10 rounded-full flex items-center justify-center bg-orange-50">
                                        <span class="text-xl">🥉</span>
                                    </div>
                                @else
                                    <div class="progress-track w-10 h-10 rounded-xl flex items-center justify-center font-black text-gray-400 text-xs">
                                        #{{ $index + 1 }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- Clipper Info --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="progress-track p-1 rounded-full shrink-0 shadow-inner">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black text-sm border-2 border-white/20">
                                        {{ substr($clipper->name, 0, 1) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="font-black text-gray-800 tracking-tight">{{ $clipper->name }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ $clipper->email }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Submissions --}}
                        <td class="px-6 py-4 text-center">
                            <div class="inline-flex items-center justify-center card-inset-panel px-4 py-1.5 !rounded-xl">
                                <span class="font-mono font-black text-gray-700">{{ $clipper->total_videos }}</span>
                                <span class="text-[9px] font-black text-gray-400 ml-1.5 uppercase">vids</span>
                            </div>
                        </td>

                        {{-- Performance (Views) --}}
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-mono font-black text-gray-800 text-base leading-none">
                                    {{ number_format($clipper->total_views) }}
                                </span>
                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.2em] mt-1">Total Views</span>
                            </div>
                        </td>

                        {{-- Earnings --}}
                        <td class="px-6 py-4 text-right">
                            <div class="inline-block card-timbul !py-2 !px-4 !rounded-xl border-none">
                                <span class="font-black text-emerald-600 font-mono text-sm tracking-tighter">
                                    Rp {{ number_format($clipper->total_earnings) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="progress-track inline-block p-8 rounded-full mb-4">
                                <span class="text-4xl opacity-20">📭</span>
                            </div>
                            <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Belum ada data clipper yang tersedia</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

        </div>
    </div>

    {{-- Script Chart JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const trendData = @json($viewsTrend);
            const platformStats = @json($platformStats);

            const trendLabels = trendData.map(d => new Date(d.date).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short'
            }));
            const trendValues = trendData.map(d => d.total_views);

            new Chart(document.getElementById('viewsTrendChart'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Views',
                        data: trendValues,
                        borderColor: '#4F46E5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [2, 4],
                                color: '#E5E7EB'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('platformChart'), {
                type: 'bar',
                data: {
                    labels: ['TikTok', 'Instagram', 'YouTube'],
                    datasets: [{
                        data: [platformStats['TikTok'] || 0, platformStats['Instagram'] || 0,
                            platformStats['YouTube'] || 0
                        ],
                        backgroundColor: ['#111827', '#E1306C', '#FF0000'],
                        borderRadius: 5,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [2, 4],
                                color: '#F3F4F6'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
