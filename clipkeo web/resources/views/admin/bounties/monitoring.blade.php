<x-app-layout>
    <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 px-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Mission Control Center</h2>
                    <p class="text-sm text-gray-500 mt-1">Pantau aktivitas sistem secara real-time.</p>
                </div>

                <a href="{{ route('admin.monitor.index') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            {{-- STATISTIK SINGKAT --}}
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-lg shadow-gray-500/50 rounded-2xl p-6">
                    <div class="text-gray-500 text-xs uppercase font-bold tracking-wider">Total Antrian</div>
                    <div class="text-3xl font-black text-gray-800 mt-1">
                        {{ $pendingSubmissions->total() }} <span class="text-sm font-normal text-gray-400">Video</span>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg shadow-gray-500/50 rounded-2xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-100">

                    @if ($pendingSubmissions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr>
                                        <th
                                            class="p-4 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider rounded-tl-lg">
                                            Waktu Submit</th>
                                        <th
                                            class="p-4 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Campaign & Publisher</th>
                                        <th
                                            class="p-4 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Clipper (Pelamar)</th>
                                        <th
                                            class="p-4 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            Bukti Tayang</th>
                                        <th
                                            class="p-4 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider rounded-tr-lg text-right">
                                            Durasi Menunggu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($pendingSubmissions as $sub)
                                        <tr class="hover:bg-gray-50 transition">

                                            {{-- 1. WAKTU SUBMIT --}}
                                            <td class="p-4 align-top">
                                                <div class="text-sm font-bold text-gray-700">
                                                    {{ $sub->created_at->format('d M Y') }}
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    {{ $sub->created_at->format('H:i') }} WIB
                                                </div>
                                            </td>

                                            {{-- 2. CAMPAIGN & PUBLISHER (Yg harus review) --}}
                                            <td class="p-4 align-top">
                                                <div class="text-sm font-bold text-indigo-600 mb-1">
                                                    {{ $sub->bounty->title ?? 'Campaign Dihapus' }}
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <img class="w-6 h-6 rounded-full object-cover border border-white shadow-sm"
                                                        src="{{ $sub->bounty->publisher->avatar_url ? (str_starts_with($sub->bounty->publisher->avatar_url, 'http') ? $sub->bounty->publisher->avatar_url : asset('storage/' . $sub->bounty->publisher->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode($sub->bounty->publisher->name) . '&background=random&color=fff' }}"
                                                        alt="{{ $sub->bounty->publisher->name }}">
                                                    <div class="text-xs text-gray-500">
                                                        Publisher: <span
                                                            class="font-bold text-gray-700">{{ $sub->bounty->publisher->name ?? 'Unknown' }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- 3. CLIPPER (Yg setor) --}}
                                            <td class="p-4 align-top">
                                                <div class="flex items-center gap-2">
                                                   <img class="w-8 h-8 rounded-full object-cover border border-white shadow-sm"
                                                 src="{{ $sub->user->avatar_url ? (str_starts_with($sub->user->avatar_url, 'http') ? $sub->user->avatar_url : asset('storage/' . $sub->user->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode($sub->user->name) . '&background=random&color=fff' }}"
                                                 alt="{{ $sub->user->name }}">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $sub->user->name }}</div>
                                                        <div class="text-xs text-gray-400">{{ $sub->user->email }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- 4. LINK BUKTI --}}
                                            <td class="p-4 align-top">
                                                <a href="{{ $sub->link }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-gray-100 text-xs font-medium text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition border border-gray-200">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                        </path>
                                                    </svg>
                                                    Cek Video
                                                </a>
                                                <div class="mt-2 flex gap-2">
                                                    {{-- Badge Platform --}}
                                                    @if (Str::contains($sub->link, 'tiktok'))
                                                        <span
                                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-black text-white">TikTok</span>
                                                    @elseif(Str::contains($sub->link, 'instagram'))
                                                        <span
                                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-pink-100 text-pink-600">IG
                                                            Reels</span>
                                                    @elseif(Str::contains($sub->link, 'youtu'))
                                                        <span
                                                            class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-600">YouTube</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- 5. DURASI MENUNGGU & AKSI --}}
                                            <td class="p-4 align-top text-right">
                                                @php
                                                    // [UPDATE] LOGIKA WAKTU: Ubah hitungan ke MENIT (diffInMinutes)
                                                    // $minutes = selisih waktu dalam menit
                                                    $minutes = $sub->created_at->diffInMinutes(now());
                                                @endphp

                                                <div class="flex flex-col items-end gap-2">
                                                    {{-- Badge Durasi --}}
                                                    {{-- Logic Warna: Jika sudah 5 Menit (>= 5) langsung MERAH --}}
                                                    <span
                                                        class="inline-block px-3 py-1 rounded-full text-xs font-bold border 
            {{ $minutes >= 5 ? 'text-red-600 bg-red-50 border-red-100' : 'text-green-600 bg-green-50 border-green-100' }}">

                                                        {{-- Teks (misal: "5 minutes ago") --}}
                                                        {{ $sub->created_at->diffForHumans() }}
                                                    </span>

                                                    {{-- TOMBOL MUNCUL SETELAH 5 MENIT --}}
                                                    @if ($minutes >= 5)
                                                        <div class="text-[10px] text-red-500 font-bold mb-1">
                                                            Boleh Takeover
                                                        </div>

                                                        {{-- TOMBOL: REVIEW & TAKEOVER --}}
                                                        <a href="{{ route('admin.submission.takeover.view', $sub->id) }}"
                                                            class="inline-block bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-indigo-700 shadow-sm transition">
                                                            Review & Takeover
                                                        </a>
                                                    @else
                                                        {{-- INFO SISA WAKTU --}}
                                                        <span class="text-[10px] text-gray-400 italic">
                                                            Tunggu {{ 5 - $minutes }} menit lagi
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-6">
                            {{ $pendingSubmissions->links() }}
                        </div>
                    @else
                        {{-- Tampilan Kosong --}}
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Semua Aman!</h3>
                            <p class="text-gray-500 text-sm">Tidak ada antrian submission yang tertunda.</p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
