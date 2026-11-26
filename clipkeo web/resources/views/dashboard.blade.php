<x-app-layout>


    {{-- ======================================================================= --}}
    {{-- START: ROLE PUBLISHER                                                   --}}
    {{-- ======================================================================= --}}
    @if (Auth::user()->role === 'publisher')
        <x-slot name="header">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Publisher Workspace') }}
                </h2>
                <span
                    class="px-3 py-1 bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-2xl text-xs font-bold uppercase tracking-widest">
                    Publisher
                </span>
            </div>
        </x-slot>

        <div class="py-12 bg-[#f3f4f6] min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                {{-- [CARD] HEADER UTAMA / WELCOME BANNER --}}
                <div class="card-neumorphism relative overflow-hidden group border-none rounded-3xl">
                    <div class="p-8 relative z-10">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div>
                                <h3 class="text-3xl font-bold text-gray-800 mb-2">
                                    Halo, {{ Auth::user()->name }}! 👋
                                </h3>
                                <p class="text-gray-500 text-lg max-w-2xl">
                                    Siap mencari clipper terbaik untuk campaignmu hari ini?
                                </p>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-wrap gap-4">
                                {{-- Button Buat Campaign (Timbul & Berwarna) --}}
                                <a href="{{ route('bounties.create') }}"
                                    class="btn-neumorph-primary !rounded-2xl py-3 px-8 text-indigo-600 font-bold hover:text-indigo-800 flex items-center gap-2 transition-all active:scale-95">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span>Buat Campaign</span>
                                </a>

                                {{-- Button Top Up (Timbul Standar) --}}
                                <a href="{{ route('deposits.create') }}"
                                    class="btn-neumorph-primary !rounded-2xl py-3 px-8 text-indigo-600 font-bold hover:text-indigo-800 flex items-center gap-2 transition-all active:scale-95">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Top Up Saldo</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Dekorasi Background Abstrak (Tetap dipertahankan dengan gaya soft) --}}
                    <div
                        class="absolute -right-10 -bottom-10 transform rotate-12 group-hover:scale-110 transition-transform duration-700 pointer-events-none">
                        <img src="{{ asset('images/campaign.png') }}" alt="Badge"
                            class="w-48 h-48 object-contain opacity-10">
                    </div>
                </div>

                {{-- [CONTAINER] GRID STATISTIK (4 KARTU) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

                    {{-- [CARD 1] Misi Aktif (Blue Theme) --}}
                    <div class="card-timbul relative overflow-hidden group border-none rounded-3xl p-6">
                        <div class="relative z-10">
                            <p class="text-emerald-500 text-sm font-bold uppercase tracking-wider">Misi Aktif</p>
                            <p class="text-4xl font-black text-gray-900 mt-2">{{ $activeCampaigns }}</p>
                            <p class="text-xs text-gray-400 font-medium mt-1">Campaign berjalan</p>
                        </div>
                        {{-- Icon Background Overlay --}}
                        <div
                            class="absolute -right-6 -bottom-6 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                            <img src="{{ asset('images/misi.png') }}" alt="Badge"
                                class="w-40 h-40 object-contain opacity-10">
                        </div>
                    </div>

                    {{-- [CARD 2] Total Pelamar (Green Theme) --}}
                    <div class="card-timbul relative overflow-hidden group border-none rounded-3xl p-6">
                        <div class="relative z-10">
                            <p class="text-emerald-500 text-sm font-bold uppercase tracking-wider">Total Pelamar</p>
                            <p class="text-4xl font-black text-gray-900 mt-2">{{ $totalApplicants }}</p>
                            <p class="text-xs text-gray-400 font-medium mt-1">Content Creator</p>
                        </div>
                        {{-- Icon Background Overlay --}}
                        <div
                            class="absolute -right-6 -bottom-6 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                            <img src="{{ asset('images/people.png') }}" alt="Badge"
                                class="w-40 h-40 object-contain opacity-10">
                        </div>
                    </div>

                    {{-- [CARD 3] Sisa Saldo Utama (Indigo Theme) --}}
                    <div class="card-timbul relative overflow-hidden group border-none rounded-3xl p-6">
                        <div class="relative z-10">
                            <p class="text-emerald-500 text-sm font-bold uppercase tracking-wider">Saldo Utama</p>
                            <p class="text-3xl font-black text-gray-600 mt-2 truncate">
                                Rp {{ number_format($currentBalance, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400 font-medium mt-1">Wallet tersedia</p>
                        </div>
                        {{-- Icon Background Overlay --}}
                        <div
                            class="absolute -right-6 -bottom-6 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                            <img src="{{ asset('images/wallet.png') }}" alt="Badge"
                                class="w-40 h-40 object-contain opacity-10">
                        </div>
                    </div>

                    {{-- [CARD 4] Dana Tertahan (Amber Theme) --}}
                    <div class="card-timbul relative overflow-hidden group border-none rounded-3xl p-6">
                        <div class="relative z-10">
                            <p class="text-emerald-500 text-sm font-bold uppercase tracking-wider">Dana Escrow</p>
                            <p class="text-3xl font-black text-gray-600 mt-2 truncate">
                                Rp {{ number_format($lockedFunds ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400 font-medium mt-1">Tertahan di campaign</p>
                        </div>
                        <div
                            class="absolute -right-6 -bottom-6 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                            <img src="{{ asset('images/escrow.png') }}" alt="Badge"
                                class="w-40 h-40 object-contain opacity-10">
                        </div>
                    </div>
                </div>

                {{-- [TABLE] KELOLA SAYEMBARA (BOUNTIES) --}}
                <div class="card-timbul overflow-hidden border-none rounded-3xl">
                    {{-- Header Kartu --}}
                    <div class="px-8 py-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-gray-800">Kelola Sayembara</h3>
                            <p class="text-xs text-gray-400 mt-1 font-medium">Total: {{ $myBounties->count() }} Misi
                                Terdaftar</p>
                        </div>
                        {{-- Menghapus bg-gray-50/50 agar menyatu dengan card --}}
                    </div>

                    {{-- Area Tabel dengan Efek Inset (Cekung) --}}
                    <div class="mx-4 mb-4 card-inset-panel overflow-hidden">
                        <div class="overflow-x-auto max-h-[400px] overflow-y-auto custom-scrollbar">
                            <table class="w-full text-left text-sm text-gray-600 border-collapse">
                                <thead
                                    class="bg-gray-50/50 text-gray-400 uppercase text-[10px] tracking-widest font-bold border-b border-gray-100 sticky top-0 z-20">
                                    <tr>
                                        <th class="px-8 py-5">Judul Misi</th>
                                        <th class="px-8 py-5">Kategori</th>
                                        <th class="px-8 py-5">Reward</th>
                                        <th class="px-8 py-5">Sisa Anggaran</th>
                                        <th class="px-8 py-5 text-center">Pelamar</th>
                                        <th class="px-8 py-5 text-center">Status</th>
                                        <th class="px-8 py-5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($myBounties as $bounty)
                                        <tr class="hover:bg-white/50 transition-colors duration-200">

                                            {{-- 1. JUDUL MISI --}}
                                            <td class="px-8 py-6">
                                                <div class="font-bold text-gray-800 text-sm leading-tight">
                                                    {{ $bounty->title }}</div>
                                                <div
                                                    class="text-[10px] text-gray-400 mt-1.5 flex items-center gap-1.5 font-medium">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Deadline:
                                                    {{ \Carbon\Carbon::parse($bounty->deadline_at)->format('d M Y') }}
                                                </div>
                                            </td>

                                            {{-- 2. KATEGORI --}}
                                            <td class="px-8 py-5 align-middle text-left">
                                                @php
                                                    $catStyle = match ($bounty->distribution_strategy) {
                                                        'performance_tiered' => [
                                                            'label' => 'Performance',
                                                            'color' => 'text-amber-600',
                                                        ],
                                                        default => [
                                                            'label' => 'CPM',
                                                            'color' => 'text-emerald-500',
                                                        ],
                                                    };
                                                @endphp

                                                {{-- Wrapper dengan class card-timbul --}}
                                                <div
                                                    class="card-timbul inline-flex items-center justify-center px-2 py-1 border-none rounded-lg">
                                                    <span
                                                        class="text-[8px] font-black {{ $catStyle['color'] }} uppercase tracking-widest whitespace-nowrap">
                                                        {{ $catStyle['label'] }}
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- 3. TOTAL REWARD --}}
                                            <td class="px-8 py-6">
                                                <span class="font-bold text-[12px] text-gray-700 font-mono">Rp.
                                                    {{ number_format($bounty->reward_amount, 0, ',', '.') }}</span>
                                            </td>

                                            {{-- 4. SISA DANA (DENGAN NEUMORPH BAR) --}}
                                            <td class="px-8 py-6">
                                                @php
                                                    $percent =
                                                        $bounty->reward_amount > 0
                                                            ? ($bounty->remaining_budget / $bounty->reward_amount) * 100
                                                            : 0;
                                                    $barColor =
                                                        $percent > 50
                                                            ? 'bg-green-500'
                                                            : ($percent > 20
                                                                ? 'bg-amber-500'
                                                                : 'bg-red-500');
                                                @endphp
                                                <div class="min-w-[140px]">
                                                    <div class="flex justify-between items-end mb-2">
                                                        <span class="text-[11px] font-bold text-gray-600 font-mono">
                                                            Rp.
                                                            {{ number_format($bounty->remaining_budget, 0, ',', '.') }}
                                                        </span>
                                                        <span
                                                            class="text-[10px] text-gray-400 font-black">{{ round($percent) }}%</span>
                                                    </div>
                                                    <div class="progress-track w-full h-2 rounded-full p-[2px]">
                                                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-700 shadow-sm"
                                                            style="width: {{ $percent }}%"></div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- 5. PELAMAR --}}
                                            <td class="px-8 py-5 align-middle">
                                                <div
                                                    class="card-timbul p-2 flex items-center justify-center min-w-[80px] rounded-lg">
                                                    @if ($bounty->submissions_count > 0)
                                                        <span
                                                            class="text-xs font-black text-gray-600 bg-gray-100 flex items-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5"
                                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                                </path>
                                                            </svg>
                                                            {{ $bounty->submissions_count }} Clipper
                                                        </span>
                                                    @else
                                                        <span
                                                            class="text-gray-300 text-xs italic font-medium">Kosong</span>
                                                    @endif
                                                </div>
                                            </td>

                                            {{-- 6. STATUS --}}
                                            <td class="px-8 py-6 text-center">
                                                @if ($bounty->status == 'active')
                                                    <span
                                                        class="card-timbul p-2 flex items-center justify-center min-w-[80px] rounded-lg flex items-center px-3 py-1.5 rounded-full text-[10px] font-black bg-green-50 text-green-600 uppercase tracking-widest">
                                                        <span
                                                            class="w-1.5 h-1.5 rounded-full bg-green-500 mr-2 animate-pulse"></span>
                                                        Aktif
                                                    </span>
                                                @elseif($bounty->status == 'pending_review')
                                                    <span
                                                        class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-600 border border-amber-100 uppercase tracking-widest">
                                                        Ditinjau
                                                    </span>
                                                @elseif($bounty->status == 'rejected')
                                                    <div class="flex items-center justify-center gap-2">
                                                        <span
                                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-black bg-red-50 text-red-600 border border-red-100 uppercase tracking-widest">
                                                            Ditolak
                                                        </span>
                                                        @if ($bounty->rejection_reason)
                                                            <div class="group relative">
                                                                <div
                                                                    class="w-5 h-5 rounded-full bg-white border border-red-200 text-red-400 flex items-center justify-center text-[10px] font-bold cursor-help shadow-sm">
                                                                    ?</div>
                                                                <div
                                                                    class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-48 p-3 bg-gray-900 text-white text-[10px] rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-[60] text-center">
                                                                    {{ $bounty->rejection_reason }}
                                                                    <div
                                                                        class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span
                                                        class="px-3 py-1.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-500 border border-gray-200 uppercase tracking-widest">
                                                        {{ $bounty->status }}
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- 7. AKSI --}}
                                            <td class="px-8 py-6 text-right">
                                                <div class="flex justify-end gap-3">
                                                    @php
                                                        $isLocked =
                                                            in_array(strtolower($bounty->status), [
                                                                'completed',
                                                                'rejected',
                                                            ]) || $bounty->remaining_budget < 10000;
                                                        $pendingCount = $bounty->submissions
                                                            ->where('status', 'pending')
                                                            ->count();
                                                    @endphp

                                                    {{-- Tombol View --}}
                                                    <a href="{{ route('bounties.submissions', $bounty->id) }}"
                                                        class="btn-neumorph-primary !p-2.5 !rounded-xl relative group"
                                                        title="Lihat Pelamar">
                                                        <svg class="w-4 h-4 text-indigo-600" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.5"
                                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2.5"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                            </path>
                                                        </svg>
                                                        @if ($pendingCount > 0)
                                                            <span class="absolute -top-1 -right-1 flex h-4 w-4 z-10">
                                                                <span
                                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                                <span
                                                                    class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[8px] text-white items-center justify-center font-bold">{{ $pendingCount }}</span>
                                                            </span>
                                                        @endif
                                                    </a>

                                                    {{-- Tombol Edit --}}
                                                    @if (!$isLocked)
                                                        <a href="{{ route('bounties.edit', $bounty->id) }}"
                                                            class="btn-neumorph-primary !p-2.5 !rounded-xl text-gray-500 hover:text-green-600"
                                                            title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5"
                                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                                </path>
                                                            </svg>
                                                        </a>
                                                    @endif

                                                    {{-- Tombol Hapus --}}
                                                    @if (!$isLocked && $bounty->submissions_count == 0)
                                                        <form action="{{ route('bounties.destroy', $bounty->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Hapus bounty ini?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn-neumorph-primary !p-2.5 !rounded-xl text-red-400 hover:text-red-600"
                                                                title="Hapus">
                                                                <svg class="w-4 h-4" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2.5"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-8 py-20 text-center">
                                                <div class="flex flex-col items-center justify-center opacity-40">
                                                    <div
                                                        class="progress-track w-16 h-16 rounded-full flex items-center justify-center mb-4">
                                                        <svg class="w-8 h-8 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <p
                                                        class="text-gray-500 font-bold uppercase tracking-widest text-xs">
                                                        Belum ada sayembara</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- [TABLE] RIWAYAT DEPOSIT --}}
                <div class="card-timbul overflow-hidden border-none rounded-3xl">
                    {{-- Header Kartu --}}
                    <div class="px-8 py-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-extrabold text-gray-800">Riwayat Deposit</h3>
                            <p class="text-xs text-gray-400 mt-1 font-medium">Transaksi terakhir akun Anda</p>
                        </div>
                        <a href="{{ route('deposits.create') }}"
                            class="btn-neumorph-primary !py-2 !px-5 !text-[11px] font-black text-indigo-600 uppercase tracking-wider !rounded-full">
                            + Deposit Baru
                        </a>
                    </div>

                    {{-- Area Tabel dengan Efek Inset (Cekung) --}}
                    <div class="mx-4 mb-5 card-neumorphism overflow-hidden rounded-3xl">
                        <div class="overflow-x-auto max-h-[350px] overflow-y-auto custom-scrollbar">
                            <table class="w-full text-left text-sm text-gray-600 border-collapse">
                                <thead
                                    class="bg-gray-50/50 text-gray-400 uppercase text-[10px] tracking-widest font-bold border-b border-gray-100 sticky top-0 z-20">
                                    <tr>
                                        <th class="px-8 py-5">Tanggal</th>
                                        <th class="px-8 py-5">Ref ID</th>
                                        <th class="px-8 py-5">Jumlah</th>
                                        <th class="px-8 py-5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($myDeposits as $deposit)
                                        <tr class="hover:bg-white/50 transition-colors duration-200">
                                            {{-- 1. TANGGAL --}}
                                            <td class="px-8 py-6 whitespace-nowrap">
                                                <div class="font-bold text-gray-700 text-sm">
                                                    {{ \Carbon\Carbon::parse($deposit->created_at)->format('d M Y') }}
                                                </div>
                                                <div class="text-[11px] text-gray-400 mt-0.5 font-medium">
                                                    {{ \Carbon\Carbon::parse($deposit->created_at)->format('H:i') }}
                                                    WIB
                                                </div>
                                            </td>

                                            {{-- 2. REF ID --}}
                                            <td class="px-8 py-6">
                                                <span
                                                    class="font-mono text-[11px] text-gray-500 bg-white/40 px-2 py-1 rounded-md border border-white/60">
                                                    {{ $deposit->reference_id }}
                                                </span>
                                            </td>

                                            {{-- 3. JUMLAH --}}
                                            <td class="px-8 py-6">
                                                <div class="text-green-600 font-black text-base">
                                                    Rp {{ number_format($deposit->amount, 0, ',', '.') }}
                                                </div>
                                            </td>

                                            {{-- 4. STATUS --}}
                                            <td class="px-8 py-6 text-center align-middle">
                                                <div class="flex justify-center">
                                                    @if ($deposit->status == 'pending')
                                                        <div
                                                            class="card-timbul inline-flex items-center px-4 py-1.5 rounded-full border-none">
                                                            <span
                                                                class="text-[10px] font-black text-amber-600 uppercase tracking-widest flex items-center gap-1.5">
                                                                <span
                                                                    class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                                Pending
                                                            </span>
                                                        </div>
                                                    @elseif($deposit->status == 'success')
                                                        <div
                                                            class="card-timbul inline-flex items-center px-4 py-1.5 rounded-full border-none">
                                                            <span
                                                                class="text-[10px] font-black text-green-600 uppercase tracking-widest">
                                                                Berhasil
                                                            </span>
                                                        </div>
                                                    @elseif($deposit->status == 'failed')
                                                        <div class="group relative inline-block">
                                                            <div
                                                                class="card-timbul inline-flex items-center px-4 py-1.5 rounded-full border-none cursor-help">
                                                                <span
                                                                    class="text-[10px] font-black text-red-600 uppercase tracking-widest">
                                                                    Ditolak
                                                                </span>
                                                            </div>
                                                            {{-- Tooltip Alasan --}}
                                                            <div
                                                                class="absolute bottom-full mb-3 left-1/2 -translate-x-1/2 w-48 p-3 bg-gray-900 text-white text-[10px] rounded-xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-[60] text-center leading-relaxed">
                                                                <p
                                                                    class="font-bold text-red-300 mb-1 uppercase tracking-wider">
                                                                    Alasan Penolakan:</p>
                                                                {{ $deposit->rejection_reason ?? 'Tidak ada alasan spesifik.' }}
                                                                <div
                                                                    class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-8 py-20 text-center">
                                                <div class="flex flex-col items-center justify-center opacity-40">
                                                    <div
                                                        class="progress-track w-16 h-16 rounded-full flex items-center justify-center mb-4">
                                                        <svg class="w-8 h-8 text-gray-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <p
                                                        class="text-gray-500 font-bold uppercase tracking-widest text-xs italic">
                                                        Belum ada riwayat deposit</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- ======================================================================= --}}
        {{-- START: ROLE CLIPPER (KREATOR)                                           --}}
        {{-- ======================================================================= --}}
        <x-slot name="header">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-200 leading-tight">
                    {{ __('Clipper Command Center') }}
                </h2>
                <span
                    class="px-3 py-1 bg-pink-600 rounded-full text-xs font-bold text-white uppercase tracking-widest">
                    Clipper
                </span>
            </div>
        </x-slot>

        <div class="py-12 bg-[#f3f4f6] min-h-screen" x-data="{ showDetailModal: false, modalSubmit: false, detail: {} }">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                {{-- [CONTAINER] GRID STATISTIK ATAS (EARNINGS, PROFILE, REPUTASI) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    {{-- [CARD 1] Total Earnings + Tombol Withdraw --}}
                    <div
                        class="card-neumorphism overflow-hidden relative transform transition-all duration-300 cursor-default group">
                        <div
                            class="absolute -right-6 -bottom-6 transform rotate-12 group-hover:scale-110 transition-transform hover:-translate-y-1 duration-500">
                            <img src="{{ asset('images/money.png') }}" alt="Badge"
                                class="w-40 h-40 object-contain opacity-10">
                        </div>
                        <div class="p-6 relative z-10">
                            <div class="text-lg font-bold text-black">Total Earnings</div>
                            <div class="text-3xl font-bold text-emerald-600">
                                Rp. {{ number_format(Auth::user()->wallet_balance, 0, ',', '.') }}
                            </div>
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('withdrawals.create') }}"
                                    class="btn-neumorph-primary text-gray-800 font-bold px-4 py-2 group rounded-xl relative flex flex-col p-6 overflow-hidden transform transition-all duration-300 cursor-pointer">
                                    Withdraw
                                </a>
                                <a href="{{ route('payout-methods.index') }}"
                                    class="btn-neumorph-primary text-gray-800 font-bold px-4 py-2 group rounded-xl relative flex flex-col p-6 overflow-hidden transform transition-all duration-300 cursor-pointer">
                                    Atur Rekening
                                </a>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-4 -right-4 w-32 h-32 bg-purple-600 rounded-full mix-blend-overlay filter blur-3xl opacity-50">
                        </div>
                    </div>


                    {{-- [CARD 2] Total Submission --}}

                    <div
                        class="card-neumorphism overflow-hidden relative transform transition-all duration-300 cursor-default group">
                        <div
                            class="absolute -right-6 -bottom-6 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                            <img src="{{ asset('images/submission.png') }}" alt="Badge"
                                class="w-40 h-40 object-contain opacity-10">
                        </div>
                        <div class="p-6 relative z-10">
                            <div class="text-lg font-bold text-black">Total Submission</div>
                            <div class="text-3xl font-bold text-emerald-600">
                                {{ $totalSubmissions ?? 0 }}
                            </div>
                            <div class="mt-4 flex gap-2">
                                <a href="{{ route('submissions.my') }}"
                                    class="btn-neumorph-primary text-gray-800 font-bold px-4 py-2 group rounded-xl relative flex flex-col p-6 overflow-hidden transform transition-all duration-300 cursor-pointer">
                                    Cek Submission
                                </a>
                            </div>
                        </div>
                        <div
                            class="absolute -bottom-4 -right-4 w-32 h-32 bg-purple-600 rounded-full mix-blend-overlay filter blur-3xl opacity-50">
                        </div>
                    </div>
                    {{-- [CARD 3] Badge System (FIXED VERSION) --}}
                    @php
                        // Ambil badge dari database
                        $currentBadge = Auth::user()->getCurrentBadge();

                        // LOGIKA PENENTU GAMBAR:
                        // Jika punya badge di DB -> Pakai nama file dari DB
                        // Jika TIDAK punya (akun lama) -> Paksa pakai 'bronze_medal.png'
                        $imageFilename = $currentBadge ? $currentBadge->icon_url : 'badges/bronze_medal.png';

                        // BERSIHKAN STRING:
                        // Kadang di DB tersimpan "badges/bronze.png", kadang cuma "bronze.png".
                        // Kita pastikan path-nya benar mengarah ke public/badges/
                        $imageName = basename($imageFilename);
                    @endphp
                    {{-- [CARD 3] Reputasi Akun --}}
                    <div
                        class="card-neumorphism flex flex-col justify-center overflow-hidden p-6 relative transform transition-all duration-300 cursor-default group">
                        <div class="text-lg font-bold text-black">Reputasi Akun</div>

                        <div
                            class="absolute -right-6 -bottom-6 transform rotate-12 group-hover:scale-110 transition-transform duration-500">
                            <img src="{{ asset('badges/' . $imageName) }}" alt="Badge"
                                class="w-40 h-60 object-contain opacity-10" alt="Badge Icon"
                                onerror="this.onerror=null;this.src='{{ asset('badges/bronze_medal.png') }}';">
                        </div>
                        <br>
                        <div class="flex items-center justify-between mb-2">
                            {{-- Menampilkan Nama Level (misal: Silver Clipper) --}}
                            <div
                                class="card-timbul inline-flex items-center px-4 py-1.5 rounded-lg text-[10px] font-black text-gray-600 border-none">
                                <span class="w-2 h-2 mr-2 bg-green-400 rounded-full animate-pulse"></span>
                                {{ $currentBadge ? $currentBadge->name : 'Bronze Clipper' }}
                            </div>

                            {{-- Menampilkan Angka Poin (misal: 1,250 XP) --}}
                            <span class="text-sm text-emerald-600 font-black tracking-wide">
                                {{ number_format(Auth::user()->reputation_points) }} XP
                            </span>
                        </div>

                        {{-- Progress Bar Dinamis --}}
                        <div class="w-full bg-gray-200 rounded-full h-2.5 shadow-inner">
                            {{-- Kita panggil fungsi getReputationProgress() di sini --}}
                            <div class="bg-gradient-to-r from-emerald-400 to-green-300 h-2.5 rounded-full transition-all duration-1000 ease-out relative"
                                style="width: {{ Auth::user()->getReputationProgress() }}%">

                                {{-- Efek Kilatan Cahaya di ujung bar --}}
                                <div class="absolute right-0 top-0 bottom-0 w-2 bg-white/50 rounded-full blur-[2px]">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mt-2">
                            <p class="text-[10px] text-gray-500 font-medium">Progress Level</p>
                            {{-- Menampilkan Angka Persen --}}
                            <p class="text-[10px] text-emerald-500 font-bold">
                                {{ Auth::user()->getReputationProgress() }}%</p>
                        </div>
                    </div>
                </div>


                <h3 class="text-lg font-bold text-gray-800 mt-8">Clipper Tools</h3>

                {{-- [CONTAINER] GRID TOOLS (AKUN, MISI, SETORAN, LEADERBOARD) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                    {{-- [CARD TOOL 1] Cari Misi --}}
                    <a href="{{ route('bounties.index') }}"
                        class="btn-neumorph-primary rounded-2xl group relative flex flex-col p-6 overflow-hidden transform transition-all duration-300 cursor-pointer">
                        <div class="absolute top-4 right-4 text-gray-600 group-hover:text-green-500 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h4 class="text-black font-bold mb-1">Cari Misi</h4>
                        <p class="text-xs text-gray-500">Temukan sayembara & cuan.</p>
                    </a>

                    {{-- [CARD TOOL 2] Setoran Saya --}}
                    <a href="{{ route('submissions.my') }}"
                        class="btn-neumorph-primary rounded-2xl group relative flex flex-col p-6 overflow-hidden transform transition-all duration-300 cursor-pointer">
                        <div class="absolute top-4 right-4 text-gray-600 group-hover:text-green-500 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <h4 class="text-black font-bold mb-1">Submissions Saya</h4>
                        <p class="text-xs text-gray-500">Cek status video yang dikirim.</p>
                    </a>

                    {{-- [CARD TOOL 3] Akun Sosmed --}}
                    <a href="{{ route('linked-accounts.index') }}"
                        class="btn-neumorph-primary rounded-2xl group relative flex flex-col p-6 overflow-hidden transform transition-all duration-300 cursor-pointer">
                        <div class="absolute top-4 right-4 text-gray-600 group-hover:text-green-500 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h4 class="text-black font-bold mb-1">Akun Sosmed</h4>
                        <p class="text-xs text-gray-500">Verifikasi identitas creator kamu.</p>
                    </a>

                    {{-- [CARD TOOL 4] Leaderboard --}}
                    <a href="{{ route('leaderboard') }}"
                        class="btn-neumorph-primary rounded-2xl group relative flex flex-col p-6 overflow-hidden transform transition-all duration-300 cursor-pointer">
                        <div class="absolute top-4 right-4 text-gray-600 group-hover:text-green-500 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <h4 class="text-black font-bold mb-1">Leaderboard</h4>
                        <p class="text-xs text-gray-500">Ranking clipper terbaik.</p>
                    </a>

                </div>

                {{-- SECTION: MISI TERBARU (MINI CARD) --}}
                <div class="mb-10">
                    <div class="flex justify-between items-end mb-6 px-2">
                        <div>
                            <h3 class="font-black text-gray-900 text-xl tracking-tight flex items-center gap-2">
                                Campaign Terbaru
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                            </h3>
                            <p class="text-gray-500 text-sm mt-1">Jadilah yang pertama mengambil peluang ini.</p>
                        </div>
                        <a href="{{ route('bounties.index') }}"
                            class="btn-neumorph-primary inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-[10px] font-black text-gray-600 uppercase tracking-widest">
                            Lihat Semua
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>

                    {{-- [UPDATE] GRID JADI 4 KOLOM (lg:grid-cols-4) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                        {{-- LOOP DATA REAL --}}
                        @foreach ($latestBounties as $bounty)
                            @php
                                $percentage = 0;
                                if ($bounty->reward_amount > 0) {
                                    $percentage = ($bounty->remaining_budget / $bounty->reward_amount) * 100;
                                }

                                $displayMinViews = $bounty->min_views;

                                if (
                                    $bounty->distribution_strategy === 'performance_tiered' &&
                                    !empty($bounty->rules['tiers'])
                                ) {
                                    // Collect array tiers, lalu ambil nilai minimal dari key 'target_views'
                                    $displayMinViews = collect($bounty->rules['tiers'])->min('target_views') ?? 0;
                                }

                                $minViewsToClaim = 0;

                                if (strtolower($bounty->distribution_strategy) === 'cpm') {
                                    // Jika CPM, ambil langsung dari kolom min_views
                                    $minViewsToClaim = $bounty->min_views;
                                } else {
                                    // Jika Tiered, ambil target_views paling kecil dari JSON tiers
                                    $tiers = $bounty->rules['tiers'] ?? [];
                                    if (!empty($tiers)) {
                                        $minViewsToClaim = collect($tiers)->min('target_views');
                                    }
                                }
                            @endphp

                            <div class="group relative flex flex-col h-full">
                                <div
                                    class="card-neumorphism overflow-hidden relative transform transition-all duration-300 cursor-default group">

                                    {{-- Cover Image --}}
                                    <div class="relative h-32 overflow-hidden bg-gray-200">
                                        @if ($bounty->image_path)
                                            <img src="{{ asset('storage/' . $bounty->image_path) }}"
                                                class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                                        @else
                                            <img src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?q=80&w=1000&auto=format&fit=crop"
                                                class="w-full h-full object-cover opacity-80">
                                        @endif

                                        {{-- Badge Kategori --}}
                                        <div class="absolute top-3 left-3 flex gap-1">
                                            <span
                                                class="px-2 py-1 rounded-lg bg-white/90 backdrop-blur-sm text-[9px] font-black uppercase tracking-wider shadow-sm text-gray-800">
                                                {{ $bounty->category ?? 'Miscellaneous' }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Card Body --}}
                                    <div class="p-4 flex flex-col flex-1">
                                        <h3
                                            class="text-sm font-bold text-gray-900 leading-tight mb-2 line-clamp-1 group-hover:text-emerald-600 transition">
                                            {{ $bounty->title }}
                                        </h3>

                                        <div class="flex flex-col gap-3 mb-6">
                                            <label
                                                class="block text-[7px] font-black text-gray-400 uppercase tracking-[0.3em]">
                                                Platform Target
                                            </label>

                                            <div class="flex flex-wrap gap-3">
                                                @foreach ((array) ($bounty->platform ?? []) as $plat)
                                                    @php
                                                        $p = strtolower(trim($plat));
                                                        $shortName = explode('_', $plat)[0];
                                                    @endphp

                                                    <div
                                                        class="card-timbul flex items-center gap-2.5 px-4 py-2 rounded-xl border-none">

                                                        {{-- Logika Pencarian Kata (Bukan Perbandingan Presisi) --}}
                                                        @if (str_contains($p, 'instagram'))
                                                            <i class="fab fa-instagram text-pink-500 text-xs"></i>
                                                        @elseif(str_contains($p, 'tiktok'))
                                                            <i class="fab fa-tiktok text-gray-800 text-xs"></i>
                                                        @elseif(str_contains($p, 'youtube'))
                                                            <i class="fab fa-youtube text-red-600 text-xs"></i>
                                                        @else
                                                            <i class="fas fa-globe text-indigo-500 text-xs"></i>
                                                        @endif

                                                        <span
                                                            class="text-[8px] font-black text-gray-700 tracking-widest">
                                                            {{ $shortName }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Info Views & Budget --}}
                                        <div
                                            class="bg-gray-50 rounded-2xl shadow-lg shadow-gray-500/50 p-3 mb-4 mt-auto border border-gray-100 group-hover:border-indigo-50 transition">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-[9px] font-bold text-gray-400 uppercase">Min.
                                                    Views</span>
                                                <span
                                                    class="text-xs font-black text-gray-800">{{ number_format($displayMinViews) }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-gradient-to-r from-emerald-400 to-green-300 h-full rounded-full transition-all duration-1000 ease-out"
                                                    style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <div class="flex justify-between mt-1">
                                                <span class="text-[9px] text-gray-400">Sisa Budget</span>
                                                <span
                                                    class="text-[9px] font-bold text-emerald-600">{{ number_format($percentage, 0) }}%
                                                    <span class="text-gray-400 font-normal">dari Rp
                                                        {{ number_format($bounty->reward_amount) }}</span></span>
                                            </div>
                                        </div>

                                        {{-- Tombol Detail --}}
                                        <button type="button"
                                            @click="
                                                showDetailModal = true;
                                                detail = {
                                                    id: {{ $bounty->id }},
                                                    unique_code: 'CS-{{ date('Y') }}-{{ strtoupper(substr(md5($bounty->id . auth()->id()), 0, 6)) }}',
                                                    title: '{{ addslashes($bounty->title) }}',
                                                    category: '{{ $bounty->category }}',
                                                    description: `{{ nl2br(e($bounty->description)) }}`, 
                                                    source_link: '{{ $bounty->source_link }}',
                                                    min_views: '{{ number_format($minViewsToClaim, 0, ',', '.') }}',
                                                    budget: '{{ number_format($bounty->reward_amount, 0, ',', '.') }}',
                                                    platform: {{ json_encode($bounty->platform) }},
                                                    image_url: '{{ Storage::url($bounty->image_path) }}',
                                                    publisher_avatar_url: '{{ !empty($bounty->publisher->avatar_url)
                                                        ? (Str::startsWith($bounty->publisher->avatar_url, ['http', 'https'])
                                                            ? $bounty->publisher->avatar_url
                                                            : Storage::url($bounty->publisher->avatar_url))
                                                        : '' }}',
                                                    deadline: '{{ \Carbon\Carbon::parse($bounty->deadline_at)->format('d F Y') }}',
                                                    hashtags: '{{ $bounty->required_hashtags ?? '-' }}',
                                                    mentions: '{{ $bounty->required_mentions ?? '-' }}',
                                                    strategy: '{{ strtolower($bounty->distribution_strategy) }}', 
                                                    rate: '{{ number_format($bounty->rate_per_view ?? 0, 2, ',', '.') }}', 
                                                    tiers: {{ json_encode($bounty->rules['tiers'] ?? []) }},
                                                    rawData: {{ \Illuminate\Support\Js::from($bounty) }},
                                                    min_views_raw: {{ $minViewsToClaim }}
                                                }
                                            "
                                            class="btn-neumorph-primary w-full py-2.5 rounded-xl font-bold text-[10px] uppercase tracking-wider flex items-center justify-center gap-1.5">
                                            Lihat Detail
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- [LOGIKA PENGISI SLOT KOSONG] --}}
                        {{-- Jika data kurang dari 4, buat card kosong (Placeholder) --}}
                        @for ($i = $latestBounties->count(); $i < 4; $i++)
                        @endfor
                    </div>
                </div>

                <div class="mt-12">
                    {{-- [TABLE] RIWAYAT PENARIKAN (WITHDRAW) --}}
                    <div
                        class="card-neumorphism overflow-hidden relative transform transition-all duration-300 cursor-default group">
                        <div
                            class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    Riwayat Penarikan Dana
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Catatan transaksi keluar (Withdrawal)</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto max-h-[250px] overflow-y-auto custom-scrollbar">
                            <table class="w-full text-left text-sm text-gray-900">
                                <thead
                                    class="bg-gray-50 text-gray-900 uppercase text-[10px] tracking-wider font-bold border-b border-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-8 py-4">Tanggal</th>
                                        <th class="px-8 py-4">Metode / Bank</th>
                                        <th class="px-8 py-4 text-right">Jumlah</th>
                                        <th class="px-8 py-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($withdrawals as $wd)
                                        <tr class="hover:bg-gray-50/80 transition duration-200">
                                            {{-- KOLOM 1: TANGGAL --}}
                                            <td class="px-8 py-4 whitespace-nowrap text-xs text-gray-500">
                                                <div class="font-bold text-gray-800">
                                                    {{ \Carbon\Carbon::parse($wd->created_at)->translatedFormat('d M Y') }}
                                                </div>
                                                <span
                                                    class="block mt-0.5 opacity-70">{{ \Carbon\Carbon::parse($wd->created_at)->format('H:i') }}
                                                    WIB</span>
                                            </td>

                                            {{-- KOLOM 2: BANK / METODE --}}
                                            <td class="px-8 py-5">
                                                <div class="font-medium text-gray-700 leading-tight">
                                                    {{ $wd->bank_info ?? 'Detail Bank Tidak Tersedia' }}
                                                </div>
                                            </td>

                                            {{-- KOLOM 3: JUMLAH (FINAL) --}}
                                            <td class="px-8 py-5 text-right">
                                                <div class="font-bold text-green-700 font-mono text-base">
                                                    Rp {{ number_format($wd->amount_final, 0, ',', '.') }}
                                                </div>
                                                <span class="text-[9px] text-gray-400 italic">Bersih diterima</span>
                                            </td>

                                            {{-- KOLOM 4: STATUS --}}
                                            <td class="px-8 py-5 text-center">
                                                @if ($wd->status === 'pending')
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-700 border border-amber-200 card-inset-panel">
                                                        Pending
                                                    </span>
                                                @elseif($wd->status === 'completed' || $wd->status === 'processing')
                                                    {{-- PROCESSING ditambahkan agar data yang disetujui admin tetap berwarna hijau --}}
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase bg-green-100 text-green-700 border border-green-200 card-inset-panel">
                                                        Berhasil
                                                    </span>
                                                @elseif($wd->status === 'rejected')
                                                    <div class="group/tooltip relative inline-block">
                                                        <span
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase bg-red-100 text-red-700 border border-red-200 cursor-help card-inset-panel">
                                                            Ditolak
                                                        </span>

                                                        {{-- Tooltip Detail Penolakan --}}
                                                        <div
                                                            class="absolute bottom-full mb-3 right-0 w-64 p-4 bg-gray-900 text-white text-[11px] rounded-2xl shadow-2xl opacity-0 group-hover/tooltip:opacity-100 transition-all duration-300 pointer-events-none z-50 transform translate-y-2 group-hover/tooltip:translate-y-0">
                                                            <p
                                                                class="font-bold text-red-400 mb-1 uppercase tracking-tighter text-[9px]">
                                                                Alasan Penolakan</p>
                                                            <p class="leading-relaxed opacity-90">
                                                                "{{ $wd->rejection_reason ?? 'Mohon periksa kembali data rekening Anda.' }}"
                                                            </p>
                                                            <div
                                                                class="absolute top-full right-4 border-[6px] border-transparent border-t-gray-900">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Tampilan saat tidak ada data sama sekali --}}
                                        <tr>
                                            <td colspan="4" class="px-8 py-20 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div
                                                        class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-4 border border-gray-200">
                                                        <i data-lucide="info" class="w-8 h-8 text-gray-400"></i>
                                                    </div>
                                                    <p class="text-gray-900 font-bold">Riwayat Masih Kosong</p>
                                                    <p class="text-xs text-gray-500 max-w-[200px] mx-auto">Anda belum
                                                        pernah melakukan penarikan dana sebelumnya.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            {{-- MODAL DETAIL MISI (PREMIUM NEUMORPH) --}}
            <div x-show="showDetailModal" style="display: none;"
                class="fixed inset-0 z-[100] flex items-center justify-center px-4"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-md" @click="showDetailModal = false"></div>

                {{-- Modal Panel --}}
                <div class="card-neumorphism w-full max-w-4xl rounded-[3rem] p-0 relative z-10 overflow-hidden flex flex-col max-h-[90vh] shadow-2xl border-none"
                    @click.stop x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="scale-95 translate-y-8"
                    x-transition:enter-end="scale-100 translate-y-0">

                    {{-- Header --}}
                    <div class="px-10 py-8 flex justify-between items-center bg-white/20">
                        <div class="flex items-center gap-6">
                            <div class="p-1 card-inset-panel rounded-2xl overflow-hidden shrink-0">
                                <img :src="detail.image_url" class="w-20 h-20 rounded-xl object-cover">
                            </div>
                            <div>
                                <h3 class="text-3xl font-black text-gray-800 tracking-tighter italic uppercase leading-none mb-2"
                                    x-text="detail.title"></h3>
                                <div class="flex items-center gap-3">
                                    <span
                                        class="card-timbul px-4 py-1.5 text-[10px] text-emerald-500 font-black tracking-[0.1rem]"
                                        x-text="detail.category">
                                    </span>
                                    <span
                                        class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Campaign
                                        Overview</span>
                                </div>
                            </div>
                        </div>

                        <button @click="showDetailModal = false"
                            class="w-12 h-12 btn-neumorph-primary rounded-full flex items-center justify-center text-gray-400 hover:text-rose-500 hover:card-inset-panel transition-all">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-10 pt-2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                            <div class="md:col-span-2 space-y-10">
                                <div>
                                    <h4
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-4 flex items-center gap-3">
                                        <span class="w-8 h-px bg-gray-200"></span> Instruksi Lengkap
                                    </h4>
                                    <div class="card-inset-panel text-gray-600 text-sm leading-relaxed p-6 max-h-64 overflow-y-auto custom-scrollbar"
                                        x-html="detail.description">
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-4">
                                        <h4
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] flex items-center gap-3">
                                            <span class="w-8 h-px bg-gray-200"></span> Skema Hadiah
                                        </h4>
                                        <span
                                            class="card-timbul px-4 py-1.5 text-[10px] text-emerald-500 font-black tracking-[0.1rem]"
                                            :class="detail.strategy === 'cpm' ?"
                                            x-text="detail.strategy === 'cpm' ? 'Pay Per View' : 'Tiered Performance'">
                                        </span>
                                    </div>

                                    <template x-if="detail.strategy === 'cpm'">
                                        <div
                                            class="card-inset-panel p-6 rounded-[1rem] flex items-center justify-between">
                                            <span
                                                class="text-xs font-black text-gray-400 uppercase tracking-widest">Rate
                                                per 1 View</span>
                                            <span class="text-2xl font-black text-gray-600 tracking-tighter">Rp.
                                                <span class="text-emerald-600" x-text="detail.rate"></span></span>
                                        </div>
                                    </template>

                                    <template x-if="detail.strategy === 'performance_tiered'">
                                        <div class="card-inset-panel rounded-[1rem] overflow-hidden">
                                            <table class="w-full text-left border-collapse">
                                                <thead
                                                    class="text-[9px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100/50">
                                                    <tr>
                                                        <th class="px-6 py-4">Target Views</th>
                                                        <th class="px-6 py-4 text-right">Hadiah</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-xs">
                                                    <template x-for="tier in detail.tiers">
                                                        <tr class="border-b border-gray-100/50 last:border-none">
                                                            <td class="px-4 py-4 font-black text-gray-700 italic">
                                                                <span
                                                                    x-text="parseInt(tier.target_views).toLocaleString('id-ID')"></span>
                                                                Views
                                                            </td>
                                                            <td
                                                                class="px-4 py-4 text-right font-black text-emerald-600">
                                                                Rp <span
                                                                    x-text="parseInt(tier.reward).toLocaleString('id-ID')"></span>
                                                            </td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </template>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div class="card-inset-panel p-5 rounded-2xl">
                                        <label
                                            class="block text-[8px] font-black text-gray-400 uppercase tracking-widest mb-2">Minimal
                                            Views untuk Claim Reward</label>
                                        <p class="text-[14px] font-black text-gray-700"><span
                                                class="font-black text-emerald-600" x-text="detail.min_views"></span>
                                            Views</p>
                                    </div>
                                    <div class="flex items-center gap-2 p-5 card-neumorphism rounded-2xl">
                                        <div
                                            class="w-10 h-10 flex items-center justify-center text-amber-500 rounded-xl">
                                            <i class="far fa-clock"></i>
                                        </div>
                                        <div>
                                            <p class="text-[8px] text-gray-400 font-black uppercase tracking-widest">
                                                Deadline</p>
                                            <p class="text-xs font-black text-gray-700 uppercase"
                                                x-text="detail.deadline"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sidebar Info --}}
                            <div class="space-y-8">
                                <div>
                                    <h4
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1rem] mb-4 flex items-center gap-3">
                                        <span class="w-8 h-px bg-gray-200"></span> Anggaran Campaign
                                    </h4>
                                    <div class="p-8 card-inset-panel rounded-[1.0rem] text-center">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                            Total
                                            Budget</p>
                                        <p
                                            class="text-2xl font-black text-gray-600 tracking-tighter leading-none mb-1">
                                            Rp. <span class="text-emerald-600" x-text="detail.budget"></span>
                                        </p>
                                    </div>
                                </div>

                                {{-- PLATFORM TARGET --}}
                                <div>
                                    <h4
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.1rem] mb-4 flex items-center gap-3">
                                        <span class="w-8 h-px bg-gray-200"></span> Platform Target
                                    </h4>
                                    <div class="flex flex-wrap gap-3">
                                        <template x-for="plat in detail.platform">
                                            <span
                                                class="px-4 py-2 card-timbul rounded-xl text-[10px] font-black text-emerald-600 uppercase tracking-widest transition-all"
                                                x-text="plat.replace('_', ' ')">
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div class="card-inset-panel p-5 rounded-2xl">
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Hashtag</label>
                                    <p class="text-[12px] font-black text-gray-700" x-text="detail.hashtags"></p>
                                    <br>
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Mention</label>
                                    <p class="text-[12px] font-black text-gray-700" x-text="detail.mentions"></p>
                                </div>

                                <a :href="detail.source_link" target="_blank"
                                    class="btn-neumorph-primary flex items-center justify-center gap-3 p-5 rounded-[1rem] group">
                                    <i
                                        class="fas fa-download text-emerald-500 group-hover:rotate-12 transition-transform"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-600">Ambil
                                        Materi</span>
                                </a>
                                <button type="button" {{-- Trigger menggunakan Dispatch --}}
                                    @click="$dispatch('set-bounty-data', { 
                                        id: detail.id, 
                                        min: detail.min_views_raw, 
                                        detail: detail.rawData 
                                    })"
                                    class="mt-4 w-full flex items-center justify-center gap-3 p-5 rounded-[1rem] bg-emerald-500 text-white font-black text-[10px] uppercase tracking-widest shadow-lg shadow-emerald-200 hover:bg-emerald-600 hover:scale-[1.02] transition-all duration-300 group">

                                    <i
                                        class="fas fa-paper-plane group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                                    <span>Kirim Submission</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-modal-submission :mySocials="$mySocials ?? collect()" />
        </div>
    @endif

</x-app-layout>
