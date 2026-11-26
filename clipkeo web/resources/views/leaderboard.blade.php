<x-app-layout>
    <div class="pt-32 pb-12 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- HEADER BANNER (NEUMORPH STYLE) --}}
            <div class="card-neumorphism overflow-hidden rounded-[3rem] mb-10 relative">
                {{-- Dekorasi Glow --}}
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl"></div>
                
                <div class="p-10 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                    <div>
                        <h1 class="text-4xl font-black text-gray-800 tracking-tighter mb-2">HALL OF FAME</h1>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.3em]">Peringkat Clipper Terbaik Berdasarkan XP & Reputasi</p>
                    </div>
                    <div class="w-20 h-20 card-inset-panel flex items-center justify-center text-4xl shadow-inner group">
                        <span class="animate-bounce">👑</span>
                    </div>
                </div>
            </div>

            {{-- CONTAINER TABEL --}}
            <div class="card-neumorphism rounded-[3rem] p-4 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-4">
                        <thead>
                            <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
                                <th class="px-6 py-4 text-center w-24">Rank</th>
                                <th class="px-6 py-4">Creator</th>
                                <th class="px-6 py-4">Pangkat / Tier</th>
                                <th class="px-6 py-4 text-right">Total XP</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach ($leaderboard as $index => $clipper)
                                @php
                                    $rank = $index + 1;
                                    $isMe = $clipper->id === Auth::id();
                                    
                                    // Mapping Warna Peringkat
                                    $rankColor = match($rank) {
                                        1 => 'text-yellow-500',
                                        2 => 'text-slate-400',
                                        3 => 'text-orange-400',
                                        default => 'text-gray-300'
                                    };

                                    $clipperBadge = $clipper->getCurrentBadge();
                                    $badgeImage = $clipperBadge ? $clipperBadge->icon_url : 'bronze_medal.png';
                                    $badgePath = 'badges/' . basename($badgeImage);
                                @endphp

                                <tr class="group transition-all duration-300">
                                    {{-- 1. RANKING --}}
                                    <td class="px-6 py-5 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl 
                                            {{ $isMe ? 'card-inset-panel text-emerald-500' : 'card-neumorphism group-hover:card-inset-panel' }} 
                                            transition-all duration-500">
                                            <span class="font-black text-xl {{ $rank <= 3 ? $rankColor : 'text-gray-400' }}">
                                                @if($rank == 1) 🥇 @elseif($rank == 2) 🥈 @elseif($rank == 3) 🥉 @else {{ $rank }} @endif
                                            </span>
                                        </div>
                                    </td>

                                    {{-- 2. PROFILE --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-5">
                                            {{-- Avatar + Badge --}}
                                            <div class="relative p-1 card-inset-panel rounded-full overflow-visible">
                                                <img class="w-12 h-12 rounded-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500"
                                                    src="{{ $clipper->avatar_url ? (str_starts_with($clipper->avatar_url, 'http') ? $clipper->avatar_url : asset('storage/' . $clipper->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode($clipper->name) . '&background=6366f1&color=fff' }}"
                                                    alt="{{ $clipper->name }}">
                                                
                                                {{-- Gambar Badge --}}
                                                <div class="absolute -bottom-1 -right-1 w-6 h-6 card-neumorphism rounded-full p-1 flex items-center justify-center shadow-lg">
                                                    <img src="{{ asset($badgePath) }}" 
                                                         class="w-full h-full object-contain"
                                                         onerror="this.src='{{ asset('badges/bronze_medal.png') }}'">
                                                </div>
                                            </div>

                                            <div>
                                                <div class="font-black text-gray-800 tracking-tight flex items-center gap-2">
                                                    {{ $clipper->name }}
                                                    @if ($isMe)
                                                        <span class="text-[8px] bg-emerald-500 text-white px-2 py-0.5 rounded-full tracking-widest">YOU</span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">@ {{ $clipper->username }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. TIER NAME --}}
                                    <td class="px-6 py-5">
                                        @php
                                            $tierStyle = match(true) {
                                                $clipper->reputation_score >= 4 => 'text-cyan-600 bg-cyan-50/50',
                                                $clipper->reputation_score >= 3 => 'text-amber-600 bg-amber-50/50',
                                                $clipper->reputation_score >= 2 => 'text-indigo-600 bg-indigo-50/50',
                                                default => 'text-orange-600 bg-orange-50/50'
                                            };
                                        @endphp
                                        <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest card-inset-panel {{ $tierStyle }}">
                                            {{ $clipper->tier_name }}
                                        </span>
                                    </td>

                                    {{-- 4. XP POINTS --}}
                                    <td class="px-6 py-5 text-right">
                                        <span class="block font-black text-gray-800 text-xl tracking-tighter">{{ number_format($clipper->reputation_points) }}</span>
                                        <span class="text-[9px] text-emerald-500 font-black uppercase tracking-[0.2em]">Clipper XP</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer Info --}}
                <div class="mt-4 p-6 card-inset-panel rounded-[2rem] text-center">
                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.2em]">
                        <i class="fas fa-sync-alt mr-2 animate-spin-slow"></i> Peringkat diperbarui secara realtime berdasarkan aktivitas campaign.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
