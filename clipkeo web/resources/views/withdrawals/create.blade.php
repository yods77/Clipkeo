<x-app-layout>
    {{-- 
    |--------------------------------------------------------------------------
    | HALAMAN REQUEST WITHDRAWAL (PENARIKAN DANA)
    |--------------------------------------------------------------------------
    | File ini menangani antarmuka pengajuan penarikan dana oleh user.
    | 
    | FITUR UTAMA:
    | 1. Input Nominal & Kalkulasi Fee Realtime (AlpineJS).
    | 2. Validasi Saldo (Frontend).
    | 3. Modal Verifikasi PIN (Sebelum submit).
    | 4. Toast Notification untuk pesan sukses/gagal.
    --}}

    <div class="pt-32 pb-12 bg-gray-100 min-h-screen relative">

        {{-- 
        |--------------------------------------------------------------------------
        | 🔔 TOAST NOTIFICATION (POJOK KANAN ATAS)
        |--------------------------------------------------------------------------
        | Menampilkan notifikasi floating jika ada error atau success dari server.
        --}}
        <div class="fixed top-24 right-5 z-[100] space-y-3 pointer-events-none">
            @if (session('error') || $errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    x-transition:enter="transform ease-out duration-300 transition"
                    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 border-l-4 border-red-500">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-bold text-gray-900">Gagal</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ session('error') ?? 'Terjadi kesalahan pada input.' }}</p>
                            </div>
                            <button @click="show = false"
                                class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500"><svg class="h-5 w-5"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                </svg></button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- 
            |--------------------------------------------------------------------------
            | 1. HEADER & NAVIGASI
            |--------------------------------------------------------------------------
            --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                {{-- Judul Halaman --}}
                <div>
                    <h2 class="text-3xl font-black text-gray-800 tracking-tighter">Tarik Dana</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mt-1">Konversi Saldo Anda
                        Menjadi Rupiah</p>
                </div>

                {{-- Tombol Kembali (Style Timbul/Embossed) --}}
                <a href="{{ route('wallet.index') }}"
                    class="btn-neumorph-primary inline-flex items-center px-6 py-2.5 rounded-2xl group transition-all duration-300">

                    <svg class="w-4 h-4 mr-2 text-gray-600 group-hover:-translate-x-1 transition-transform duration-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>

                    <span
                        class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-600 group-hover:text-gray-600 transition-colors">
                        Kembali
                    </span>
                </a>
            </div>

            {{-- 
|--------------------------------------------------------------------------
| 2. KARTU SALDO (NEUMORPH INSET VERSION)
|--------------------------------------------------------------------------
--}}
            <div class="card-inset-panel p-10 mb-10 relative overflow-hidden group">

                {{-- Dekorasi Cahaya Halus --}}
                <div
                    class="absolute -top-20 -right-20 w-48 h-48 bg-emerald-500/15 rounded-full blur-3xl group-hover:bg-emerald-500/10 transition-all duration-1000">
                </div>

                <div class="relative z-10 text-center">
                    {{-- Label --}}
                    <p
                        class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-4 flex items-center justify-center gap-2">
                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                        Saldo Tersedia Saat Ini
                    </p>

                    {{-- Angka Saldo --}}
                    <h3
                        class="text-6xl font-black text-gray-800 tracking-tighter flex items-start justify-center font-sans">
                        <span class="text-gray-400 text-2xl mt-2 mr-2 font-bold">R p .</span>
                        <span class="font-mono">{{ number_format(auth()->user()->wallet_balance, 0, ',', '.') }}</span>
                    </h3>

                    {{-- Info Tambahan (Opsional) --}}
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-4 opacity-60">
                        Dapat ditarik ke Rekening atau E-Wallet pilihan Anda
                    </p>
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | 3. FORMULIR PENARIKAN (ALPINE JS)
            |--------------------------------------------------------------------------
            --}}
            <div class="card-inset-panel p-10 mb-10 relative overflow-hidden group">
                {{-- [PENTING] Tambahkan ID "withdraw-form" agar bisa dipanggil oleh JS --}}
                <form id="withdraw-form" action="{{ route('withdrawals.store') }}" method="POST" class="space-y-8"
                    x-data="{
                        amount: '',
                        balance: {{ auth()->user()->wallet_balance ?? 0 }},
                        fee_percent: 0.10,
                        // State untuk Modal PIN (Otomatis true jika ada error 'pin' dari server)
                        showPinModal: {{ $errors->has('pin') ? 'true' : 'false' }},
                        pin: '',
                    
                        // Getter Helper
                        get fee() { return Math.round(parseInt(this.amount || 0) * this.fee_percent); },
                        get final() { return parseInt(this.amount || 0) - this.fee; },
                        get isOverBudget() { return parseInt(this.amount || 0) > parseInt(this.balance); },
                        get isValid() { return parseInt(this.amount || 0) >= 50000 && !this.isOverBudget; },
                    
                        // [FUNGSI UTAMA] Submit Form
                        submitForm() {
                            if (this.pin.length === 6) {
                                // Trik Jitu: Panggil form berdasarkan ID dan submit paksa
                                document.getElementById('withdraw-form').submit();
                            }
                        }
                    }">
                    @csrf

                    {{-- A. INPUT REKENING (CUSTOM DROPDOWN NEUMORPH) --}}
                    {{-- DEFINISI LOGO BANK --}}
                    @php
                        $bankLogos = [
                            'BCA' => 'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg',
                            'BRI' => 'https://upload.wikimedia.org/wikipedia/commons/6/68/BANK_BRI_logo.svg',
                            'Mandiri' =>
                                'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg',
                            'BNI' => 'https://upload.wikimedia.org/wikipedia/commons/c/ca/Bank_BNI_Logo.png',
                            'CIMB' => 'https://upload.wikimedia.org/wikipedia/commons/3/38/CIMB_Niaga_logo.svg',
                            'Permata' =>
                                'https://upload.wikimedia.org/wikipedia/commons/f/ff/Permata_Bank_%282024%29.svg',
                            'BSI' => 'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg',
                            'Danamon' => 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Danamon.svg',
                            'GoPay' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg',
                            'Dana' => 'https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg',
                            'OVO' => 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg',
                            'ShopeePay' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg',
                            'LinkAja' => 'https://upload.wikimedia.org/wikipedia/commons/8/85/LinkAja.svg',
                        ];
                    @endphp

                    <div class="space-y-4" x-data="{
                        open: false,
                        selectedLabel: '{{ old('payout_method_id') ? $myBanks->where('id', old('payout_method_id'))->first()->bank_name : ' Pilih Rekening Tujuan ' }}',
                        selectedSub: '{{ old('payout_method_id') ? $myBanks->where('id', old('payout_method_id'))->first()->account_number : 'Pastikan data sudah benar' }}',
                        selectedId: '{{ old('payout_method_id') ?? '' }}',
                        {{-- Logika Logo Awal --}}
                        selectedLogo: '{{ old('payout_method_id') ? $bankLogos[$myBanks->where('id', old('payout_method_id'))->first()->bank_name] ?? '' : '' }}'
                    }">
                        <label class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] ml-1">
                            Transfer Ke
                        </label>

                        @if ($myBanks->isEmpty())
                            {{-- State Kosong --}}
                            <div class="card-inset-panel p-6 text-center">
                                <p class="text-xs text-rose-500 font-black uppercase tracking-widest mb-3">Rekening
                                    belum ada</p>
                                <a href="{{ route('payout-methods.index') }}"
                                    class="btn-neumorph-primary px-4 py-2 text-[10px] font-black uppercase tracking-widest text-gray-600 rounded-xl inline-block">
                                    + Tambah Baru
                                </a>
                            </div>
                        @else
                            <div class="relative">
                                {{-- TRIGGER BUTTON (Timbul) --}}
                                <button type="button" @click="open = !open"
                                    class="btn-neumorph-primary w-full p-5 rounded-[2rem] flex items-center justify-between outline-none group transition-all duration-300">

                                    <div class="flex items-center gap-4 text-left">
                                        {{-- Logo Container (Cekung) --}}
                                        <div
                                            class="w-12 h-12 card-inset-panel flex items-center justify-center p-2 overflow-hidden shadow-sm">
                                            <template x-if="selectedLogo">
                                                <img :src="selectedLogo"
                                                    class="max-w-full max-h-full object-contain filter grayscale group-hover:grayscale-0 transition-all duration-500">
                                            </template>
                                            <template x-if="!selectedLogo">
                                                <i class="fas fa-university text-gray-400"></i>
                                            </template>
                                        </div>

                                        <div>
                                            <span
                                                class="block text-xs font-black uppercase tracking-widest text-gray-700"
                                                x-text="selectedLabel"></span>
                                            <span class="block text-[10px] font-bold text-gray-400 opacity-70"
                                                x-text="selectedSub"></span>
                                        </div>
                                    </div>

                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-300"
                                        :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                {{-- Hidden Input --}}
                                <input type="hidden" name="payout_method_id" x-model="selectedId" required>

                                {{-- LIST DROPDOWN --}}
                                <div x-show="open" @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="absolute z-50 w-full mt-4 card-neumorphism p-3 shadow-2xl overflow-hidden">

                                    <div class="max-h-64 overflow-y-auto custom-scrollbar space-y-2">
                                        @foreach ($myBanks as $method)
                                            @php $currentLogo = $bankLogos[$method->bank_name] ?? ''; @endphp
                                            <button type="button"
                                                @click="
                                selectedId = '{{ $method->id }}';
                                selectedLabel = '{{ $method->bank_name }}';
                                selectedSub = '{{ $method->account_number }} (a/n {{ $method->account_name }})';
                                selectedLogo = '{{ $currentLogo }}';
                                open = false;
                            "
                                                class="w-full text-left p-3 rounded-2xl transition-all duration-200 group flex items-center justify-between hover:card-inset-panel">

                                                <div class="flex items-center gap-4">
                                                    {{-- Logo Inset di dalam List --}}
                                                    <div
                                                        class="w-10 h-10 bg-white/50 rounded-xl p-1.5 flex items-center justify-center border border-white/50">
                                                        @if ($currentLogo)
                                                            <img src="{{ $currentLogo }}"
                                                                class="max-w-full max-h-full object-contain">
                                                        @else
                                                            <i class="fas fa-university text-gray-300 text-xs"></i>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <span
                                                            class="block text-[10px] font-black uppercase tracking-widest text-gray-600 group-hover:text-emerald-600 transition-colors">
                                                            {{ $method->bank_name }}
                                                        </span>
                                                        <span class="block text-[10px] font-mono text-gray-400">
                                                            {{ $method->account_number }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <i class="fas fa-check text-[10px] text-emerald-400 transition-opacity"
                                                    :class="selectedId == '{{ $method->id }}' ? 'opacity-100' : 'opacity-0'"></i>
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="mt-3 pt-3 border-t border-gray-100 text-center">
                                        <a href="{{ route('payout-methods.index') }}"
                                            class="text-[9px] font-black uppercase tracking-widest text-emerald-500 hover:text-emerald-700 transition">
                                            + Kelola Rekening
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[9px] text-gray-400 ml-2 mt-1 italic font-medium">* Saldo akan dikirimkan ke
                                rekening yang Anda pilih di atas.</p>
                        @endif
                    </div>

                    {{-- B. INPUT NOMINAL (FIX PADDING) --}}
                    <div class="space-y-4">
                        <label class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] ml-1">
                            Jumlah Penarikan (IDR)
                        </label>

                        <div class="relative group">
                            {{-- Simbol Rp: Tetap di kiri --}}
                            <div
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-14 h-12 flex items-center justify-center text-gray-400 font-black text-sm group-focus-within:text-emerald-500 transition-colors duration-300">
                                Rp
                            </div>

                            {{-- Input: Padding-left ditambah ke pl-20 (80px) atau pl-24 (96px) --}}
                            <input type="number" name="amount" x-model="amount" required min="50000" step="1"
                                class="input-neumorph-inset w-full pl-20 pr-6 py-5 text-gray-800 font-black text-3xl tracking-tighter outline-none transition-all duration-300 placeholder-gray-300"
                                :class="{
                                    'text-rose-500 shadow-[inset_6px_6px_10px_#fecaca,inset_-6px_-6px_10px_#ffffff]': isOverBudget,
                                    'focus:text-emerald-600': !isOverBudget
                                }"
                                placeholder="0">
                        </div>

                        {{-- Info Minimal Penarikan --}}
                        <div x-show="!isOverBudget"
                            class="flex items-center gap-2 text-[9px] text-gray-400 ml-1 font-black uppercase tracking-widest opacity-70">
                            <i class="fas fa-info-circle text-emerald-400"></i>
                            <span>Minimal penarikan Rp 50.000</span>
                        </div>
                    </div>

                    {{-- C. KALKULATOR RINCIAN (NEUMORPH EMBOSSED) --}}
                    <div x-show="isValid" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="card-neumorphism p-8 space-y-5 border-none mt-6">

                        {{-- Baris: Jumlah Ditarik --}}
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em]">Jumlah
                                Ditarik</span>
                            <span class="font-mono font-bold text-gray-700 text-sm"
                                x-text="'Rp ' + parseInt(amount).toLocaleString('id-ID')"></span>
                        </div>

                        {{-- Baris: Biaya Layanan --}}
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em]">Biaya Layanan
                                (10%)</span>
                            <span class="font-mono font-bold text-rose-500 text-sm"
                                x-text="'- Rp ' + parseInt(fee).toLocaleString('id-ID')"></span>
                        </div>

                        {{-- Garis Pemisah (Kita bikin efek Inset tipis biar ada tekstur) --}}
                        <div class="progress-track h-[2px] w-full rounded-full opacity-50"></div>

                        {{-- Baris: Dana Bersih --}}
                        <div class="flex justify-between items-end pt-2">
                            <div>
                                <span
                                    class="text-[10px] font-black uppercase text-emerald-600 tracking-[0.2em] block mb-1">Dana
                                    Bersih</span>
                                <span
                                    class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter italic">Total
                                    yang akan Anda terima</span>
                            </div>
                            <div class="text-right">
                                <span class="text-3xl font-black text-emerald-500 font-mono tracking-tighter"
                                    x-text="'Rp ' + parseInt(final).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>
                    {{-- D. TOMBOL TRIGGER MODAL (NEUMORPH + SUBTLE EMERALD GRADIENT) --}}
                    @if (!$myBanks->isEmpty())
                        <div class="mt-10">
                            <button type="button" @click="if(isValid) showPinModal = true" :disabled="!isValid"
                                :class="isValid
                                    ?
                                    'btn-neumorph-primary bg-gradient-to-br from-[#f3f4f6] via-[#f3f4f6] to-emerald-100/50 text-emerald-700 scale-100 opacity-100 shadow-lg border border-emerald-50/30' :
                                    'card-inset-panel text-gray-300 opacity-60 scale-[0.98] cursor-not-allowed shadow-none'"
                                class="w-full py-5 rounded-[2.5rem] font-black uppercase tracking-[0.2em] text-[11px] transition-all duration-500 flex items-center justify-center gap-3 group relative overflow-hidden">

                                {{-- Efek Cahaya Shimmer (Opsional, kalau mau diaktifkan lagi tinggal uncomment) --}}
                                {{-- <template x-if="isValid">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite] pointer-events-none"></div>
            </template> --}}

                                {{-- State Tidak Valid --}}
                                <span x-show="!isValid" class="flex items-center gap-2">
                                    <i class="fas fa-lock text-[10px]"></i>
                                    Lengkapi Nominal Dulu
                                </span>

                                {{-- State Valid (Siap Klik) --}}
                                <span x-show="isValid" class="flex items-center gap-2 animate-pulse">
                                    Ajukan Penarikan Sekarang
                                    {{-- Ikon Panah dengan stroke lebih tegas --}}
                                    <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </span>
                            </button>

                            {{-- Note tambahan di bawah tombol --}}
                            <p x-show="isValid" x-transition
                                class="text-center text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-5 opacity-80">
                                <i class="fas fa-shield-alt mr-1 text-emerald-500"></i> Transaksi Aman & Terenkripsi
                            </p>
                        </div>
                    @endif

                    {{-- 4. MODAL PIN SECURITY (NEUMORPH VERSION) --}}
                    <div x-show="showPinModal" style="display: none;"
                        class="fixed inset-0 z-[60] flex items-center justify-center px-4"
                        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

                        {{-- Overlay dengan Blur Mewah --}}
                        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-md" @click="showPinModal = false">
                        </div>

                        {{-- Modal Body --}}
                        <div class="card-neumorphism w-full max-w-sm rounded-[3rem] p-10 relative z-10 border-none"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-90 translate-y-8"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                            {{-- Icon Status (Cekung Panel) --}}
                            <div class="mx-auto w-20 h-20 rounded-[2rem] card-inset-panel flex items-center justify-center mb-8 transition-colors duration-500"
                                :class="'{{ $errors->has('pin') }}' ? 'text-rose-500' : 'text-emerald-500'">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>

                            <div class="text-center mb-8">
                                <h3 class="text-2xl font-black text-gray-800 tracking-tighter">Verifikasi Keamanan</h3>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] mt-2">
                                    Masukkan 6 Digit PIN Anda</p>
                            </div>

                            {{-- Input PIN (Gaya Inset Mewah) --}}
                            <div class="relative mb-10">
                                <input type="password" name="pin" x-model="pin" maxlength="6"
                                    inputmode="numeric" pattern="[0-9]*"
                                    class="input-neumorph-inset w-full py-6 text-center text-4xl font-mono font-black tracking-[0.5em] text-gray-800 outline-none transition-all duration-300 placeholder-gray-200"
                                    :class="{ 'text-rose-500 shadow-[inset_4px_4px_8px_#fecaca]': '{{ $errors->has('pin') }}' }"
                                    placeholder="••••••" required>

                                @error('pin')
                                    <div class="flex items-center justify-center gap-2 mt-4 text-rose-500 animate-bounce">
                                        <i class="fas fa-times-circle text-[10px]"></i>
                                        <p class="text-[10px] font-black uppercase tracking-widest">{{ $message }}
                                        </p>
                                    </div>
                                @enderror
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-col gap-4">
                                {{-- Tombol Konfirmasi (Emerald Gradient Neumorph) --}}
                                <button type="button" @click="submitForm()" :disabled="pin.length < 6"
                                    :class="pin.length === 6 ?
                                        'btn-neumorph-primary bg-gradient-to-br from-[#f3f4f6] via-[#f3f4f6] to-emerald-100/50 text-emerald-700 scale-100 opacity-100' :
                                        'card-inset-panel text-gray-300 opacity-60 scale-[0.98] cursor-not-allowed shadow-none'"
                                    class="w-full py-5 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] transition-all duration-300">
                                    Konfirmasi Penarikan
                                </button>

                                {{-- Tombol Batal --}}
                                <button type="button" @click="showPinModal = false"
                                    class="w-full py-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-rose-500 transition-colors">
                                    Batalkan
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            {{-- FOOTER --}}
            <div class="mt-8 flex items-center justify-center gap-2 text-gray-400 opacity-80">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                    </path>
                </svg>
                <span class="text-[10px] font-bold tracking-widest">Transaksi Aman & Terenkripsi Xendit.</span>
            </div>
        </div>
    </div>
</x-app-layout>
