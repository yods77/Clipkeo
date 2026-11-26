<x-app-layout>
    <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Monitor Transaksi</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola Seluruh Data Transaksi.</p>
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
            {{-- 
            |--------------------------------------------------------------------------
            | BAGIAN 1: KARTU SALDO (WALLET CARD)
            |--------------------------------------------------------------------------
            | Hanya ditampilkan untuk Publisher & Clipper (Admin tidak punya saldo).
            --}}
            @if (auth()->user()->role !== 'admin')
                {{-- Kartu Utama: Efek Cekung (Inset) --}}
                <div class="card-inset-panel p-10 mb-12 relative overflow-hidden group">

                    {{-- Dekorasi Glow Halus (Biar gak flat banget) --}}
                    <div
                        class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-all duration-700">
                    </div>

                    <div class="relative z-10">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-8">

                            {{-- Info Saldo --}}
                            <div class="text-center md:text-left">
                                <p
                                    class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-4 flex items-center justify-center md:justify-start gap-2">
                                    <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                        </path>
                                    </svg>
                                    Saldo Aktif Saat Ini
                                </p>
                                <div class="flex items-baseline justify-center md:justify-start gap-2">
                                    <span class="text-xl font-bold text-gray-400">Rp. </span>
                                    <h3 class="text-6xl font-black text-gray-800 tracking-tighter">
                                        {{ number_format($balance, 0, ',', '.') }}
                                    </h3>
                                </div>
                            </div>

                            {{-- Action Buttons (Dibuat Timbul agar kontras dengan Background Cekung) --}}
                            <div class="flex flex-wrap justify-center gap-5">

                                {{-- Tombol Topup (Hanya Publisher) --}}
                                @if (auth()->user()->role === 'publisher')
                                    <a href="{{ route('deposits.create') }}"
                                        class="btn-neumorph-primary px-8 py-4 rounded-2xl group/btn text-indigo-600 transition-all">
                                        <svg class="w-4 h-4 mr-2 group-hover/btn:rotate-90 transition-transform duration-300"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Isi Saldo</span>
                                    </a>
                                @endif

                                {{-- Tombol Withdraw (Semua user) --}}
                                <a href="{{ route('withdrawals.create') }}"
                                    class="btn-neumorph-primary px-8 py-4 rounded-2xl group/btn text-emerald-600">
                                    <span class="text-[10px] font-black uppercase tracking-widest">Tarik Dana</span>
                                    <svg class="w-4 h-4 ml-2"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>

                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- 
            |--------------------------------------------------------------------------
            | BAGIAN 2: TABEL MUTASI REKENING
            |--------------------------------------------------------------------------
            | Menampilkan daftar riwayat transaksi dengan filter lengkap.
            --}}
            <div
                class="card-neumorphism overflow-hidden relative transform transition-all duration-300 cursor-default group">

                {{-- HEADER & FILTER SECTION --}}
                <div class="border-b border-gray-100 bg-gray-50/50">
                    <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <h3 class="font-bold text-lg text-gray-900">Mutasi Rekening</h3>
                            @if (auth()->user()->role === 'admin')
                                <span
                                    class="px-2 py-0.5 bg-red-100 text-red-600 text-[10px] font-bold rounded border border-red-200 uppercase tracking-widest">Admin
                                    Mode</span>
                            @endif
                        </div>

                        {{-- Tombol Reset Filter --}}
                        @if (request()->anyFilled(['search', 'type', 'start_date', 'end_date']))
                            <a href="{{ route('wallet.index') }}"
                                class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset Filter
                            </a>
                        @endif
                    </div>

                    {{-- FORM FILTER --}}
                    <form action="{{ route('wallet.index') }}" method="GET" class="px-6 pb-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            {{-- Search --}}
                            <div class="md:col-span-4 relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-emerald-500 transition"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    class="input-neumorph-inset w-full text-gray-800 text-sm rounded-full pl-12 pr-4 py-3.5 placeholder-gray-500"
                                    placeholder="Cari Kode Ref, Ket...">
                            </div>
                            {{-- Tipe --}}
                            <div class="md:col-span-3 relative" x-data="{
                                open: false,
                                selectedLabel: '{{ request('type') ? ucwords(str_replace('_', ' ', request('type'))) : 'Semua Tipe' }}',
                                selectedValue: '{{ request('type') ?? '' }}'
                            }">

                                <button type="button" @click="open = !open"
                                    class="input-neumorph-inset w-full p-3.5 text-xs font-black uppercase tracking-widest text-gray-700 flex justify-between items-center outline-none">
                                    <span x-text="selectedLabel"></span>
                                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-300"
                                        :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <input type="hidden" name="type" x-model="selectedValue">

                                <div x-show="open" x-cloak @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute z-50 w-full mt-4 card-neumorphism p-2 shadow-2xl">

                                    <ul class="space-y-1">
                                        @php
                                            // Mapping antara Label yang muncul dan Value yang dikirim ke Database
                                            $options = [
                                                ['label' => 'Semua Tipe', 'value' => ''],
                                                ['label' => 'Deposit', 'value' => 'deposit'],
                                                ['label' => 'Hadiah Misi', 'value' => 'reward'],
                                                ['label' => 'Payout', 'value' => 'payout'],
                                                ['label' => 'Withdrawal', 'value' => 'withdrawal'],
                                                ['label' => 'Biaya Admin', 'value' => 'platform_fee'],
                                            ];
                                        @endphp

                                        @foreach ($options as $opt)
                                            <li>
                                                <button type="button"
                                                    @click="selectedLabel = '{{ $opt['label'] }}'; selectedValue = '{{ $opt['value'] }}'; open = false"
                                                    class="w-full text-left px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-600 rounded-xl hover:text-emerald-600 hover:card-inset-panel transition-all duration-200">
                                                    {{ $opt['label'] }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            {{-- Tanggal Mulai --}}
                            <div class="md:col-span-2">
                                <div class="relative group">
                                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                                        class="input-neumorph-inset w-full p-2.5 text-[10px] font-black uppercase tracking-widest text-gray-700 outline-none cursor-pointer">
                                    {{-- Label melayang kecil biar user gak bingung --}}
                                    <span
                                        class="absolute -top-5 left-2 text-[9px] font-black uppercase tracking-tighter text-gray-400">Dari
                                        Tanggal</span>
                                </div>
                            </div>
                            {{-- Tanggal Selesai --}}
                            <div class="md:col-span-2">
                                <div class="relative group">
                                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                                        class="input-neumorph-inset w-full p-2.5 text-[10px] font-black uppercase tracking-widest text-gray-700 outline-none cursor-pointer">
                                    <span
                                        class="absolute -top-5 left-2 text-[9px] font-black uppercase tracking-tighter text-gray-400">Sampai
                                        Tanggal</span>
                                </div>
                            </div>
                            {{-- Tombol Cari (Teks Murni Neumorph) --}}
                            <div class="md:col-span-1">
                                <button type="submit"
                                    class="btn-neumorph-primary w-full h-full p-2.5 rounded-2xl text-emerald-600 transition-all duration-300">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em]">
                                        Cari
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABLE CONTENT --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50/80 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 font-bold tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 font-bold tracking-wider">Kode Ref</th>
                                @if (auth()->user()->role === 'admin')
                                    <th class="px-6 py-4 font-bold tracking-wider">User</th>
                                @endif
                                <th class="px-6 py-4 font-bold tracking-wider">Tipe</th>
                                <th class="px-6 py-4 font-bold tracking-wider">Keterangan</th>
                                <th class="px-6 py-4 text-right font-bold tracking-wider">Nominal (IDR)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $trx)
                                @php
                                    $isAdmin = auth()->user()->role === 'admin';
                                    $amountColor = '';
                                    $symbol = '';
                                    $badgeLabel = '';
                                    $badgeClass = '';

                                    // ==========================================================
                                    // 1. LOGIKA WARNA & SIMBOL NOMINAL (SUKSES)
                                    // ==========================================================

                                    if ($isAdmin) {
                                        // --- LOGIKA ADMIN ---
                                        if ($trx->type === 'platform_fee') {
                                            $amountColor = 'text-emerald-600';
                                            $symbol = '+'; // Profit (Hijau Tua)
                                        } elseif ($trx->type === 'withdrawal') {
                                            $amountColor = 'text-rose-600';
                                            $symbol = '-'; // Uang Keluar (Merah)
                                        } elseif ($trx->type === 'deposit') {
                                            $amountColor = 'text-blue-600';
                                            $symbol = '+'; // Uang Masuk (Biru)
                                        } else {
                                            // Transaksi Internal
                                            $amountColor = 'text-amber-600';
                                            $symbol = in_array($trx->type, ['reward', 'bounty_refund']) ? '+' : '-';
                                        }
                                    } else {
                                        // --- LOGIKA USER (CLIPPER/PUBLISHER) ---
                                        $incomeTypes = ['deposit', 'reward', 'refund'];

                                        if (in_array($trx->type, $incomeTypes)) {
                                            $amountColor = 'text-emerald-600'; // Hijau Income
                                            $symbol = '+';
                                        } else {
                                            $amountColor = 'text-rose-600'; // Merah Expense
                                            $symbol = '-';
                                        }
                                    }

                                    // ==========================================================
                                    // 2. LOGIKA LABEL BADGE & WARNA BADGE (PASTEL STYLE)
                                    // ==========================================================

                                    switch ($trx->type) {
                                        case 'deposit':
                                            $badgeLabel = 'Deposit';
                                            $badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                                            break;
                                        case 'reward':
                                            $badgeLabel = 'Hadiah Misi';
                                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                            break;
                                        case 'payout':
                                            $badgeLabel = 'Bayar Pemenang';
                                            $badgeClass = $isAdmin
                                                ? 'bg-amber-50 text-amber-700 border-amber-200'
                                                : 'bg-rose-50 text-rose-700 border-rose-200';
                                            break;
                                        case 'bounty_hold':
                                            $badgeLabel = 'Hold Dana';
                                            $badgeClass = $isAdmin
                                                ? 'bg-amber-50 text-amber-700 border-amber-200'
                                                : 'bg-gray-100 text-gray-600 border-gray-200';
                                            break;
                                        case 'refund':
                                            $badgeLabel = 'Refund Dana';
                                            $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                            break;
                                        case 'platform_fee':
                                            if ($isAdmin) {
                                                $badgeLabel = 'Profit Fee';
                                                $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                            } else {
                                                $badgeLabel = 'Admin Fee';
                                                $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                            }
                                            break;
                                        case 'withdrawal':
                                            $badgeLabel = 'Withdraw';
                                            $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                            break;
                                        default:
                                            $badgeLabel = $trx->type;
                                            $badgeClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                    }
                                @endphp

                                <tr class="hover:bg-gray-50 transition duration-150">
                                    {{-- 1. TANGGAL --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        <div class="font-bold text-gray-700">
                                            {{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y') }}</div>
                                        <span
                                            class="block mt-0.5">{{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }}</span>
                                    </td>

                                    {{-- 2. KODE REF --}}
                                    <td class="px-6 py-4">
                                        <span
                                            class="font-mono text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 select-all cursor-pointer hover:bg-indigo-100 transition">
                                            {{ $trx->reference_id ?? '-' }}
                                        </span>
                                    </td>

                                    {{-- 3. USER (KHUSUS ADMIN) --}}
                                    @if ($isAdmin)
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] text-gray-600 font-bold border border-gray-300">
                                                    {{ substr($trx->user->name ?? '?', 0, 1) }}
                                                </div>
                                                <div>
                                                    <span
                                                        class="block text-gray-900 text-xs font-bold">{{ $trx->user->name ?? 'Deleted' }}</span>
                                                    <span
                                                        class="text-[9px] uppercase tracking-wider text-gray-400">{{ $trx->user->role ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    @endif

                                    {{-- 4. TIPE (BADGE) --}}
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 rounded-md text-[10px] font-bold border {{ $badgeClass }} uppercase tracking-wider whitespace-nowrap">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>

                                    {{-- 5. KETERANGAN --}}
                                    <td class="px-6 py-4 text-xs text-gray-500 max-w-[200px] truncate"
                                        title="{{ $trx->description }}">
                                        {{ $trx->description }}
                                    </td>

                                    {{-- 6. NOMINAL (LOGIC UPDATE) --}}
                                    <td class="px-6 py-4 text-right font-mono font-bold whitespace-nowrap text-sm">
                                        @if (in_array($trx->status, ['failed', 'rejected']))
                                            {{-- TAMPILAN JIKA GAGAL/REJECT --}}
                                            <div class="flex flex-col items-end">
                                                <span
                                                    class="text-gray-400 line-through decoration-gray-400 decoration-2">
                                                    Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                                </span>
                                                <span
                                                    class="text-[10px] text-red-500 font-bold bg-red-50 px-1.5 py-0.5 rounded uppercase tracking-wider mt-0.5 border border-red-100">
                                                    {{ $trx->status == 'rejected' ? 'DITOLAK' : 'GAGAL' }}
                                                </span>
                                            </div>
                                        @else
                                            {{-- TAMPILAN NORMAL (SUKSES/PENDING) --}}
                                            <span class="{{ $amountColor }}">
                                                {{ $symbol }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}"
                                        class="px-6 py-16 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <div
                                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-2">
                                                <svg class="w-6 h-6 text-gray-300" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                    </path>
                                                </svg>
                                            </div>
                                            <p>Belum ada transaksi tercatat.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination (Light) --}}
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
