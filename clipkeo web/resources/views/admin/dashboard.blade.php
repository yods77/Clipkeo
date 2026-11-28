<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- ======================================================================= --}}
    {{-- BAGIAN 1: ALERT / FLASH MESSAGE (Floating Top Right - Modern Style)     --}}
    {{-- ======================================================================= --}}
    <div class="fixed top-24 right-4 sm:right-8 z-[100] w-full max-w-sm space-y-4 pointer-events-none">

        {{-- SUCCESS ALERT --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-2"
                class="pointer-events-auto relative bg-white/90 backdrop-blur-md border border-green-100 border-l-4 border-l-green-500 p-4 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] shadow-green-100/50 flex items-start gap-4 group hover:shadow-lg transition-all duration-300">

                {{-- Ikon Sukses --}}
                <div class="shrink-0">
                    <div
                        class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 group-hover:scale-110 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                </div>

                {{-- Konten Teks --}}
                <div class="flex-1 pt-0.5">
                    <h4 class="font-bold text-sm text-gray-800 leading-none mb-1">Berhasil!</h4>
                    <p class="text-xs text-gray-500 leading-relaxed font-medium">{{ session('success') }}</p>
                </div>

                {{-- Tombol Close --}}
                <button @click="show = false"
                    class="text-gray-400 hover:text-gray-600 transition -mt-1 -mr-1 p-1 rounded-md hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        @endif

        {{-- ERROR ALERT --}}
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-2"
                class="pointer-events-auto relative bg-white/90 backdrop-blur-md border border-red-100 border-l-4 border-l-red-500 p-4 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] shadow-red-100/50 flex items-start gap-4 group hover:shadow-lg transition-all duration-300">

                {{-- Ikon Error --}}
                <div class="shrink-0">
                    <div
                        class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 group-hover:scale-110 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                </div>

                {{-- Konten Teks --}}
                <div class="flex-1 pt-0.5">
                    <h4 class="font-bold text-sm text-gray-800 leading-none mb-1">Aksi Ditolak!</h4>
                    <p class="text-xs text-gray-500 leading-relaxed font-medium">{{ session('error') }}</p>
                </div>

                {{-- Tombol Close --}}
                <button @click="show = false"
                    class="text-gray-400 hover:text-gray-600 transition -mt-1 -mr-1 p-1 rounded-md hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    {{-- 
    |--------------------------------------------------------------------------
    | WRAPPER UTAMA DASHBOARD
    |--------------------------------------------------------------------------
    | Halaman utama Dashboard Admin yang memuat statistik, tabel approval, 
    | dan grafik keuangan.
    --}}
    <div class="min-h-screen pt-32 pb-12 bg-gray-100 relative z-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8 px-4">

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: HEADER & STATUS
            |--------------------------------------------------------------------------
            --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <h2 class="font-black text-3xl text-gray-900 tracking-tight">Dashboard Admin</h2>
                    <p class="text-gray-500 text-sm mt-1">Overview performa platform ClipSquad hari ini.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        class="px-4 py-2 bg-red-50 border border-red-100 rounded-full text-xs font-bold text-red-600 flex items-center gap-2 shadow-sm shadow-gray-200">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        Admin Mode Active
                    </span>
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: STATISTIK UTAMA (GRID 4 CARD)
            |--------------------------------------------------------------------------
            --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- CARD 1: PENDING DEPOSITS --}}
                <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-lg shadow-gray-500/50 relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition duration-300"
                    onclick="window.location='#deposits'">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-16 h-16 text-green-500 transform rotate-12" fill="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </div>
                    <div class="flex flex-col h-full justify-between relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-green-50 rounded-2xl text-green-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            @if ($pendingDepositsCount > 0)
                                <span
                                    class="text-[10px] font-black text-green-700 bg-green-100 px-2 py-1 rounded-lg animate-pulse">ACTION
                                    NEEDED</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Pending Deposits</p>
                            <h3 class="text-3xl font-black text-gray-900 mt-1">{{ $pendingDepositsCount }} <span
                                    class="text-sm font-medium text-gray-400">Requests</span></h3>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-green-500"></div>
                </div>

                {{-- CARD 2: PENDING WITHDRAWALS --}}
                <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-lg shadow-gray-500/50 relative overflow-hidden group cursor-pointer hover:-translate-y-1 transition duration-300"
                    onclick="window.location='#withdrawals'">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-16 h-16 text-amber-500 transform -rotate-12" fill="currentColor"
                            viewBox="0 0 24 24">
                            <path
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                    </div>
                    <div class="flex flex-col h-full justify-between relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-amber-50 rounded-2xl text-amber-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                    </path>
                                </svg>
                            </div>
                            @if ($pendingWithdrawalsCount > 0)
                                <span
                                    class="text-[10px] font-black text-amber-700 bg-amber-100 px-2 py-1 rounded-lg animate-pulse">ACTION
                                    NEEDED</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Withdrawals Need Attention
                            </p>
                            <h3 class="text-3xl font-black text-gray-900 mt-1">{{ $pendingWithdrawalsCount }} <span
                                    class="text-sm font-medium text-gray-400">Requests</span></h3>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500"></div>
                </div>

                {{-- CARD 3: TOTAL USERS --}}
                <a href="{{ route('admin.users.index') }}"
                    class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-lg shadow-gray-500/50 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                    <div class="flex flex-col h-full justify-between relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-blue-50 rounded-2xl text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Total Users</p>
                            <h3 class="text-3xl font-black text-gray-900 mt-1">{{ $totalUsers }}</h3>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-500"></div>
                </a>

                {{-- CARD 4: ACTIVE CAMPAIGNS --}}
                <a href="{{ route('bounties.index') }}"
                    class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-lg shadow-gray-500/50 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
                    <div class="flex flex-col h-full justify-between relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-purple-50 rounded-2xl text-purple-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs uppercase font-bold tracking-widest">Active Campaigns</p>
                            <h3 class="text-3xl font-black text-gray-900 mt-1">{{ $activeBounties }}</h3>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 w-full h-1 bg-purple-500"></div>
                </a>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: TABEL APPROVALS (WITHDRAW & DEPOSIT)
            |--------------------------------------------------------------------------
            --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" id="withdrawals">

                {{-- 
                |-------------------------------------------------------
                | SUB-SECTION: WITHDRAW REQUESTS
                |-------------------------------------------------------
                --}}
                <div
                    class="bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-500/50 overflow-hidden flex flex-col h-full">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-gray-900 font-bold text-lg">Withdraw Requests</h3>
                        <div class="flex gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-[320px] overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead
                                class="bg-gray-50 text-gray-400 uppercase text-[10px] tracking-wider font-bold border-b border-gray-100 sticky top-0 z-10">
                                <tr>
                                    <th class="px-8 py-4">User Details</th>
                                    <th class="px-8 py-4">Amount</th>
                                    <th class="px-8 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                {{-- 
    |--------------------------------------------------------------------------
    | LOOP: DAFTAR REQUEST PENARIKAN (PENDING)
    |--------------------------------------------------------------------------
    | Menampilkan data user, bank tujuan, nominal bersih, dan tombol aksi.
    --}}
                                @forelse($pendingWithdrawals as $wd)
                                    <tr class="hover:bg-gray-50 transition duration-200">

                                        {{-- KOLOM 1: DETAIL USER & BANK --}}
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                {{-- Avatar Inisial --}}
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-500 text-xs border border-gray-200 shadow-sm">
                                                    {{ substr($wd->user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span
                                                        class="text-gray-900 font-bold block text-sm">{{ $wd->user->name }}</span>
                                                    {{-- Menampilkan Info Bank yang disimpan di Controller --}}
                                                    <div class="text-[10px] text-gray-500 font-mono mt-0.5 bg-gray-100 px-2 py-0.5 rounded inline-block border border-gray-200"
                                                        title="{{ $wd->bank_info }}">
                                                        {{ Str::limit($wd->bank_info ?? 'No Bank Info', 30) }}
                                                    </div>
                                                    @if ($wd->status === 'processing')
                                                        <span class="mt-1 inline-block text-[9px] font-black tracking-wider text-blue-700 bg-blue-50 border border-blue-100 px-2 py-1 rounded">
                                                            MENUNGGU KONFIRMASI XENDIT
                                                        </span>
                                                    @else
                                                        <span class="mt-1 inline-block text-[9px] font-black tracking-wider text-amber-700 bg-amber-50 border border-amber-100 px-2 py-1 rounded">
                                                            MENUNGGU PERSETUJUAN
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- KOLOM 2: NOMINAL & FEE --}}
                                        <td class="px-8 py-5">
                                            <div class="flex flex-col">
                                                {{-- Nominal Bersih (Yang akan ditransfer Xendit) --}}
                                                <h6 class="mb-0 text-sm font-black text-gray-900">
                                                    Rp {{ number_format($wd->amount_final, 0, ',', '.') }}
                                                </h6>
                                                {{-- Rincian Fee & Request Awal --}}
                                                <div class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                                    <span class="text-red-400">Fee:
                                                        {{ number_format($wd->amount_requested - $wd->amount_final, 0, ',', '.') }}</span>
                                                    <span class="text-gray-300">|</span>
                                                    <span>Req:
                                                        {{ number_format($wd->amount_requested, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- KOLOM 3: TOMBOL AKSI (APPROVE / REJECT) --}}
                                        <td class="px-8 py-5 text-right">
                                            <div class="flex items-center justify-end gap-2">

                                                {{-- 
                    | TOMBOL REJECT (TOLAK)
                    | Membuka Modal Pop-up untuk mengisi alasan penolakan.
                    --}}
                                                @if ($wd->status === 'pending')
                                                <button type="button"
                                                    data-action="{{ route('admin.withdrawals.reject', $wd->id) }}"
                                                    data-name="{{ $wd->user->name }}"
                                                    onclick="openWithdrawalRejectModal(this.dataset.action, this.dataset.name)"
                                                    class="p-2 rounded-xl text-red-500 hover:text-white hover:bg-red-500 transition shadow-lg shadow-red-300 hover:shadow-red-400"
                                                    title="Tolak Penarikan">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                                @endif

                                                {{-- 
                    | TOMBOL APPROVE (SETUJUI & TRANSFER)
                    | [UPDATE] Menambahkan confirm message yang lebih tegas karena ini memotong uang Xendit.
                    --}}
                                                <form action="{{ route('admin.withdrawals.approve', $wd->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm(@js($wd->status === 'processing'
                                                        ? 'Periksa status withdrawal ini di Xendit. Jika transfer sudah tercatat, sistem tidak akan mengirim transfer baru. Lanjutkan pemeriksaan?'
                                                        : 'Saldo Xendit Anda akan terpotong Rp ' . number_format($wd->amount_final, 0, ',', '.') . ' dan dikirim ke User. Lanjutkan transfer?'))">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 rounded-xl text-emerald-500 hover:text-white hover:bg-emerald-500 transition shadow-lg shadow-emerald-100 hover:shadow-emerald-200"
                                                        title="Cek Status / Setujui & Transfer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- STATE KOSONG --}}
                                    <tr>
                                        <td colspan="3" class="px-8 py-16 text-center text-gray-400 text-sm">
                                            <div class="flex flex-col items-center">
                                                <div
                                                    class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                    <svg class="w-6 h-6 text-gray-300" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                Tidak ada antrian withdraw saat ini.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 
                |-------------------------------------------------------
                | SUB-SECTION: PESAN MASUK
                |-------------------------------------------------------
                --}}
                <div
                    class="bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-500/50 overflow-hidden flex flex-col h-full">
                    <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-gray-900 font-bold text-lg">Pesan Masuk</h3>
                        <span
                            class="text-[10px] text-green-600 font-black bg-green-100 px-3 py-1 rounded-full uppercase tracking-wider">REVENUE</span>
                    </div>

                    <div class="overflow-x-auto max-h-[320px] overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead
                            class="bg-gray-50 text-gray-400 uppercase text-[10px] tracking-wider font-bold sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-3">Nama & Role</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Pesan</th>
                                <th class="px-6 py-3">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($messages as $msg)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span class="block font-bold text-gray-900">{{ $msg->name }}</span>
                                        <span
                                            class="text-[10px] px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-bold uppercase">{{ $msg->role }}</span>
                                    </td>
                                    <td class="px-6 py-4">{{ $msg->email }}</td>
                                    <td class="px-6 py-4 truncate max-w-xs" title="{{ $msg->message }}">
                                        {{ Str::limit($msg->message, 50) }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-400">
                                        {{ $msg->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: FINANCE OVERVIEW & CHARTS
            |--------------------------------------------------------------------------
            --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                {{-- Card Finance 1 --}}
                <div class="bg-white rounded-[2rem] p-6 border-l-8 border-indigo-500 shadow-lg shadow-gray-500/50">
                    <p class="text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-1">Total Dana Terendap
                        (Kas)</p>
                    <h3 class="text-3xl font-black text-gray-900 font-mono tracking-tight">
                        <span
                            class="text-indigo-500 text-sm align-top mr-1">Rp</span>{{ number_format($totalEscrowFunds, 0, ',', '.') }}
                    </h3>
                    <p class="text-[10px] text-indigo-400 mt-2 font-medium italic">*Kewajiban bayar sistem</p>
                </div>

                {{-- Card Finance 2 --}}
                <div class="bg-white rounded-[2rem] p-6 border-l-8 border-blue-500 shadow-lg shadow-gray-500/50">
                    <p class="text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-1">Total Saldo User</p>
                    <h4 class="text-xl font-bold text-gray-900 font-mono">Rp
                        {{ number_format($totalUserBalances, 0, ',', '.') }}</h4>
                    <p class="text-[10px] text-gray-400 mt-1">Siap ditarik user</p>
                </div>

                {{-- Card Finance 3 --}}
                <div class="bg-white rounded-[2rem] p-6 border-l-8 border-amber-500 shadow-lg shadow-gray-500/50">
                    <p class="text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-1">Dana Escrow (Misi)
                    </p>
                    <h4 class="text-xl font-bold text-gray-900 font-mono">Rp
                        {{ number_format($totalEscrowBounties, 0, ',', '.') }}</h4>
                    <p class="text-[10px] text-gray-400 mt-1">Anggaran campaign berjalan</p>
                </div>

                {{-- Card Finance 4 --}}
                <div class="bg-white rounded-[2rem] p-6 border-l-8 border-green-500 shadow-lg shadow-gray-500/50">
                    <p class="text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-1">Revenue (Profit)</p>
                    <h4 class="text-xl font-bold text-green-600 font-mono">Rp
                        {{ number_format($platformRevenue, 0, ',', '.') }}</h4>
                    <p class="text-[10px] text-green-500 mt-1 italic">*Keuntungan bersih</p>
                </div>
            </div>

            {{-- CHART LEDGER --}}
            <div class="bg-white border border-gray-100 rounded-[2rem] p-8 shadow-lg shadow-gray-500/50">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-gray-900 font-bold text-xl">Statistik Arus Kas (Ledger)</h3>
                    <select
                        class="bg-gray-50 text-xs text-gray-600 border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-100 font-bold cursor-pointer">
                        <option>Hari Ini</option>
                        <option>7 Hari Terakhir</option>
                        <option>Bulan Ini</option>
                    </select>
                </div>

                <div id="ledgerChart" class="w-full h-80 flex justify-center items-center"></div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 border-t border-gray-100 pt-6">
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Withdraw</p>
                        <h4 class="text-red-500 font-bold text-sm">Outflow</h4>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Fee</p>
                        <h4 class="text-green-500 font-bold text-sm">Profit</h4>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Reward</p>
                        <h4 class="text-amber-500 font-bold text-sm">Distribusi</h4>
                    </div>
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Deposit</p>
                        <h4 class="text-blue-500 font-bold text-sm">Inflow</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 
    |--------------------------------------------------------------------------
    | MODAL POPUP: REJECT DEPOSIT (GLOBAL)
    |--------------------------------------------------------------------------
    | Modal ini digunakan untuk menolak DEPOSIT.
    --}}
    <div id="rejectModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
                aria-hidden="true" onclick="closeRejectModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Panel Modal --}}
            <div
                class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="rejectForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="reason" id="final_reject_reason">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>

                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                    Tolak Deposit: <span id="modalUserName" class="text-red-500">User</span>
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Pilih alasan penolakan. User akan menerima notifikasi.
                                    </p>

                                    {{-- Dropdown Alasan --}}
                                    <label for="rejection_reason"
                                        class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Pilih
                                        Alasan</label>
                                    <select id="rejection_reason" name="rejection_reason"
                                        class="block w-full pl-3 pr-10 py-3 text-gray-600 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-2xl border bg-gray-50">
                                        <option value="" disabled selected> -- Pilih Alasan -- </option>
                                        <option value="Bukti transfer tidak ada">Bukti transfer tidak ada</option>
                                        <option value="Bukti transfer tidak terbaca / buram">Bukti transfer tidak
                                            terbaca / buram</option>
                                        <option value="Nominal transfer tidak sesuai">Nominal transfer tidak sesuai
                                        </option>
                                        <option value="Nama pengirim berbeda dengan akun">Nama pengirim berbeda dengan
                                            akun</option>
                                        <option value="Indikasi bukti transfer palsu">Indikasi bukti transfer palsu
                                        </option>
                                        <option value="Lainnya">Lainnya (Tulis Manual)</option>
                                    </select>

                                    {{-- Textarea Manual --}}
                                    <div id="manualReasonDiv" class="hidden mt-3">
                                        <label for="manual_reason"
                                            class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Keterangan
                                            Tambahan</label>
                                        <textarea name="manual_reason" id="manual_reason" rows="3"
                                            class="shadow-sm focus:ring-red-500 focus:border-red-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-xl p-3 bg-gray-50"
                                            placeholder="Tulis alasan spesifik di sini..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Action --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:w-auto sm:text-sm transition">
                            Tolak Deposit
                        </button>
                        <button type="button" onclick="closeRejectModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-100 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 
    |--------------------------------------------------------------------------
    | MODAL POPUP: REJECT WITHDRAWAL (GLOBAL) - [BARU]
    |--------------------------------------------------------------------------
    | Modal ini khusus untuk menolak PENARIKAN (Withdraw).
    --}}
    <div id="rejectWithdrawalModal" class="fixed inset-0 z-[9999] hidden overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
                aria-hidden="true" onclick="closeWithdrawalRejectModal()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Panel Modal --}}
            <div
                class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="rejectWithdrawalForm" action="" method="POST">
                    @csrf
                    {{-- Input Hidden untuk menyimpan alasan final (gabungan dropdown/manual) --}}
                    <input type="hidden" name="reason" id="final_withdrawal_reject_reason">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>

                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                    Tolak Penarikan: <span id="modalWithdrawalUserName"
                                        class="text-red-500">User</span>
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Saldo akan dikembalikan ke dompet user. Berikan alasan yang jelas.
                                    </p>

                                    {{-- Dropdown Alasan Penarikan --}}
                                    <label for="withdrawal_rejection_reason"
                                        class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Pilih
                                        Alasan</label>
                                    <select id="withdrawal_rejection_reason"
                                        class="block w-full pl-3 pr-10 py-3 text-gray-600 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-2xl border bg-gray-50">
                                        <option value="" disabled selected> -- Pilih Alasan -- </option>
                                        <option value="Informasi Rekening/E-Wallet Tidak Valid">Informasi
                                            Rekening/E-Wallet Tidak Valid</option>
                                        <option value="Nama Rekening Berbeda dengan Profil">Nama Rekening Berbeda
                                            dengan Profil</option>
                                        <option value="Indikasi Traffic Palsu / Kecurangan">Indikasi Traffic Palsu /
                                            Kecurangan</option>
                                        <option value="Saldo Tidak Mencukupi (System Error)">Saldo Tidak Mencukupi
                                            (System Error)</option>
                                        <option value="Lainnya">Lainnya (Tulis Manual)</option>
                                    </select>

                                    {{-- Textarea Manual --}}
                                    <div id="manualWithdrawalReasonDiv" class="hidden mt-3">
                                        <label for="manual_withdrawal_reason"
                                            class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Keterangan
                                            Tambahan</label>
                                        <textarea id="manual_withdrawal_reason" rows="3"
                                            class="shadow-sm focus:ring-red-500 focus:border-red-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-xl p-3 bg-gray-50"
                                            placeholder="Contoh: Nomor rekening kurang satu digit..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Action --}}
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:w-auto sm:text-sm transition">
                            Tolak Penarikan
                        </button>
                        <button type="button" onclick="closeWithdrawalRejectModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-100 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 
    |--------------------------------------------------------------------------
    | JAVASCRIPT LOGIC
    |--------------------------------------------------------------------------
    --}}
    <script>
        // ----------------------------------------------------------------------
        // FUNGSI 1: MODAL REJECT DEPOSIT
        // ----------------------------------------------------------------------
        function openRejectModal(actionUrl, userName) {
            // 1. Reset Form agar bersih
            document.getElementById('rejectForm').reset();
            document.getElementById('manualReasonDiv').classList.add('hidden');

            // 2. Set URL Action (Route Laravel) & Nama User di Judul Modal
            document.getElementById('rejectForm').action = actionUrl;
            document.getElementById('modalUserName').innerText = userName;

            // 3. Tampilkan Modal (Hapus class hidden)
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // ----------------------------------------------------------------------
        // FUNGSI 2: MODAL REJECT WITHDRAWAL
        // ----------------------------------------------------------------------
        function openWithdrawalRejectModal(actionUrl, userName) {
            // 1. Reset Form
            document.getElementById('rejectWithdrawalForm').reset();
            document.getElementById('manualWithdrawalReasonDiv').classList.add('hidden');

            // 2. Set URL Action & Nama User
            document.getElementById('rejectWithdrawalForm').action = actionUrl;
            document.getElementById('modalWithdrawalUserName').innerText = userName;

            // 3. Tampilkan Modal
            document.getElementById('rejectWithdrawalModal').classList.remove('hidden');
        }

        function closeWithdrawalRejectModal() {
            document.getElementById('rejectWithdrawalModal').classList.add('hidden');
        }

        // ----------------------------------------------------------------------
        // EVENT LISTENERS (Saat Halaman Dimuat)
        // ----------------------------------------------------------------------
        document.addEventListener("DOMContentLoaded", function() {

            // A. Listener untuk Dropdown Alasan Deposit
            const reasonSelect = document.getElementById('rejection_reason');
            const manualDiv = document.getElementById('manualReasonDiv');
            const manualText = document.getElementById('manual_reason');
            const rejectForm = document.getElementById('rejectForm');

            if (reasonSelect) {
                reasonSelect.addEventListener('change', function() {
                    // Jika pilih "Lainnya", tampilkan kotak teks manual
                    if (this.value === 'Lainnya') {
                        manualDiv.classList.remove('hidden');
                        manualText.setAttribute('required', 'required');
                    } else {
                        manualDiv.classList.add('hidden');
                        manualText.removeAttribute('required');
                    }
                });
            }

            // B. Listener Submit Form Deposit
            if (rejectForm) {
                rejectForm.addEventListener('submit', function(e) {
                    // Gabungkan nilai dropdown atau text manual ke input hidden 'reason'
                    const finalInput = document.getElementById('final_reject_reason');
                    finalInput.value = (reasonSelect.value === 'Lainnya') ? manualText.value : reasonSelect
                        .value;
                });
            }

            // C. Listener untuk Dropdown Alasan Withdrawal
            const wdReasonSelect = document.getElementById('withdrawal_rejection_reason');
            const wdManualDiv = document.getElementById('manualWithdrawalReasonDiv');
            const wdManualText = document.getElementById('manual_withdrawal_reason');
            const wdRejectForm = document.getElementById('rejectWithdrawalForm');

            if (wdReasonSelect) {
                wdReasonSelect.addEventListener('change', function() {
                    if (this.value === 'Lainnya') {
                        wdManualDiv.classList.remove('hidden');
                        wdManualText.setAttribute('required', 'required');
                    } else {
                        wdManualDiv.classList.add('hidden');
                        wdManualText.removeAttribute('required');
                    }
                });
            }

            // D. Listener Submit Form Withdrawal
            if (wdRejectForm) {
                wdRejectForm.addEventListener('submit', function(e) {
                    const finalWdInput = document.getElementById('final_withdrawal_reject_reason');
                    finalWdInput.value = (wdReasonSelect.value === 'Lainnya') ? wdManualText.value :
                        wdReasonSelect.value;
                });
            }

            // === CHART LOGIC (TIDAK BERUBAH) ===
            var labels = @json($chartLabels);
            var series = @json($chartSeries);
            var colorMap = {
                'Deposit': '#3b82f6',
                'Bounty Hold': '#a855f7',
                'Platform Fee': '#22c55e',
                'Payout': '#1e40af',
                'Reward': '#f59e0b',
                'Withdraw': '#ef4444',
                'Withdrawal': '#ef4444'
            };
            var dynamicColors = labels.map(label => colorMap[label] || '#94a3b8');

            var options = {
                series: [{
                    name: 'Total Nominal',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    background: 'transparent',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Inter, sans-serif'
                },
                colors: dynamicColors,
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '45%',
                        distributed: true
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px',
                            fontWeight: 600
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#94a3b8'
                        },
                        formatter: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + " Jt";
                            if (value >= 1000) return (value / 1000).toFixed(0) + " Rb";
                            return value;
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(val) {
                            return "Rp " + val.toLocaleString('id-ID');
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                legend: {
                    show: false
                }
            };

            var chart = new ApexCharts(document.querySelector("#ledgerChart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
