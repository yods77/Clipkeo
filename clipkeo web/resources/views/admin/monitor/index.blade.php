<x-app-layout>
    <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 px-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Mission Control Center</h2>
                    <p class="text-sm text-gray-500 mt-1">Pantau aktivitas sistem secara real-time.</p>
                </div>

                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
            {{-- 
            |--------------------------------------------------------------------------
            | BAGIAN 1: KARTU STATISTIK (OVERVIEW)
            |--------------------------------------------------------------------------
            --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Active Bounties --}}
                <div class="block bg-white p-6 rounded-2xl shadow-lg shadow-gray-500/50 border border-gray-100 hover:scale-[1.02] transition-transform duration-200 cursor-pointer group">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Misi Aktif</div>
                    <div class="text-2xl font-black text-gray-900 mt-2">{{ number_format($stats['active_bounties']) }}
                    </div>
                    <div class="text-xs text-green-500 mt-1">Sedang berjalan</div>
                </div>
                {{-- Pending Reviews --}}
                {{-- CARD ANTRIAN REVIEW (LINK KE MONITORING) --}}
                <a href="{{ route('admin.monitoring') }}"
                    class="block bg-white p-6 rounded-2xl shadow-lg shadow-gray-500/50 border border-gray-100 hover:scale-[1.02] transition-transform duration-200 cursor-pointer group">
                    <div class="flex justify-between items-start">
                        <div>
                            <div
                                class="text-xs font-bold text-gray-400 uppercase tracking-widest group-hover:text-amber-500 transition-colors">
                                Antrian Review
                            </div>
                            <div class="text-2xl font-black text-amber-500 mt-2">
                                {{ number_format($stats['pending_reviews']) }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">Video menunggu dicek</div>
                        </div>
                        {{-- Ikon Panah Kecil (Opsional, biar makin jelas kalau ini link) --}}
                        <div class="bg-amber-50 p-2 rounded-lg group-hover:bg-amber-100 transition">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </div>
                    </div>
                </a>
                {{-- Escrow Money --}}
                <div class="bg-white p-6 rounded-2xl shadow-lg shadow-gray-500/50 border border-gray-100">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Dana Tertahan (Escrow)</div>
                    <div class="text-2xl font-black text-indigo-600 mt-2">Rp
                        {{ number_format($stats['money_in_escrow']) }}</div>
                    <div class="text-xs text-gray-400 mt-1">Aman di sistem</div>
                </div>
                {{-- Total Paid --}}
                <div class="bg-white p-6 rounded-2xl shadow-lg shadow-gray-500/50 border border-gray-100">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Reward Cair</div>
                    <div class="text-2xl font-black text-green-600 mt-2">Rp
                        {{ number_format($stats['total_paid_reward']) }}</div>
                    <div class="text-xs text-gray-400 mt-1">Total pendapatan clipper</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- 
                |--------------------------------------------------------------------------
                | BAGIAN 2: RED FLAG MONITOR (PUBLISHER MENCURIGAKAN)
                |--------------------------------------------------------------------------
                | Menampilkan daftar Bounty dengan tingkat penolakan > 50%.
                | Admin bisa memantau jika ada publisher yang menyalahgunakan sistem.
                --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg shadow-gray-500/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-red-50 bg-red-50/30 flex items-center justify-between">
                        <h3 class="font-bold text-red-700 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                            High Rejection Watchlist (>50%)
                        </h3>
                        <span class="text-xs font-medium text-red-500 bg-red-100 px-2 py-1 rounded">Potensi Fraud</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">Publisher / Misi</th>
                                    <th class="px-6 py-3 text-center">Masuk</th>
                                    <th class="px-6 py-3 text-center">Ditolak</th>
                                    <th class="px-6 py-3 text-center">Rate</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($suspiciousBounties as $bounty)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-gray-900">{{ $bounty->publisher->name }}</div>
                                            <div class="text-xs text-gray-500 truncate w-48">{{ $bounty->title }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-medium">{{ $bounty->submissions_count }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-red-600">
                                            {{ $bounty->rejected_count }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-bold {{ $bounty->rejection_rate > 75 ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600' }}">
                                                {{ number_format($bounty->rejection_rate, 1) }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('bounties.show', $bounty->id) }}" target="_blank"
                                                class="text-indigo-600 hover:text-indigo-900 font-bold text-xs">Cek</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs">
                                            Aman! Belum ada misi dengan tingkat penolakan mencurigakan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 
                |--------------------------------------------------------------------------
                | BAGIAN 3: LIVE FEED (AKTIVITAS TERBARU)
                |--------------------------------------------------------------------------
                | Stream aktivitas real-time dari seluruh user.
                --}}
                <div class="bg-white rounded-2xl shadow-lg shadow-gray-500/50 border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="font-bold text-gray-900">Live Activity Feed</h3>
                    </div>
                    <div class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto">
                        @foreach ($recentActivities as $activity)
                            <div class="px-6 py-4 flex items-start gap-3 hover:bg-gray-50 transition">
                                {{-- Icon Status --}}
                                <div class="mt-1">
                                    @if ($activity->status == 'pending')
                                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                                    @elseif($activity->status == 'approved')
                                        <div
                                            class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]">
                                        </div>
                                    @elseif($activity->status == 'rejected')
                                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                    @endif
                                </div>

                                <div class="flex-1">
                                    <p class="text-xs text-gray-500 mb-0.5">
                                        {{ $activity->updated_at->diffForHumans() }}
                                    </p>
                                    <p class="text-sm text-gray-800 leading-snug">
                                        <span class="font-bold text-gray-900">{{ $activity->user->name }}</span>

                                        @if ($activity->status == 'pending')
                                            <span class="text-gray-500">menyetor link untuk</span>
                                        @elseif($activity->status == 'approved')
                                            <span class="text-green-600 font-bold">menerima reward</span> dari
                                        @elseif($activity->status == 'rejected')
                                            <span class="text-red-600 font-bold">ditolak</span> pada misi
                                        @endif

                                        <span
                                            class="font-medium text-indigo-600">"{{ Str::limit($activity->bounty->title, 20) }}"</span>
                                    </p>

                                    @if ($activity->status == 'approved')
                                        <p class="text-xs font-bold text-green-600 mt-1">+ Rp
                                            {{ number_format($activity->prize_amount) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
