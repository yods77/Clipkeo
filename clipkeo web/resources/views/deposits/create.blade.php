<x-app-layout>
    {{-- 
    |--------------------------------------------------------------------------
    | HALAMAN DEPOSIT (TOP UP)
    |--------------------------------------------------------------------------
    | Menggunakan Xendit Payment Gateway.
    | User input nominal -> Redirect ke halaman bayar Xendit.
    --}}

    <div class="pt-32 pb-12 bg-gray-100 min-h-screen" x-data="depositSystem()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: HEADER & NAVIGATION
            |--------------------------------------------------------------------------
            --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Isi Saldo (Top Up)</h2>
                    <p class="text-sm text-gray-500 mt-1">Tambah saldo dompet Anda dengan aman dan instan via Xendit.</p>
                </div>

                <a href="{{ route('dashboard') }}"
                    class="btn-neumorph-primary px-5 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Batal & Kembali
                </a>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: ERROR ALERT
            |--------------------------------------------------------------------------
            --}}
            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mb-8 bg-rose-50 border border-rose-100 text-rose-700 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 flex-shrink-0 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm font-medium">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button @click="show = false" class="ml-auto text-rose-400 hover:text-rose-600"><svg class="w-4 h-4"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
            @endif

            {{-- 
            |--------------------------------------------------------------------------
            | SECTION: FORM UTAMA
            |--------------------------------------------------------------------------
            --}}
            <form action="{{ route('deposits.store') }}" method="POST">
                @csrf
                {{-- [UPDATE] Hapus input hidden unique_code karena Xendit tidak butuh --}}

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                    <div class="lg:col-span-2 space-y-8">

                        {{-- 
                        |-------------------------------------------------------
                        | CARD 1: PILIH NOMINAL
                        |-------------------------------------------------------
                        --}}
                        <div class="card-timbul !rounded-[2.5rem] p-8 relative overflow-hidden border-none">

                            {{-- Dekorasi Angka --}}
                            <div
                                class="absolute -top-6 -right-6 text-[10rem] font-black text-gray-200 opacity-20 select-none pointer-events-none">
                                1
                            </div>

                            <h3 class="text-gray-800 font-extrabold text-xl mb-8 flex items-center gap-4 relative z-10">
                                <span
                                    class="card-inset-panel flex items-center justify-center w-10 h-10 !rounded-xl text-emerald-600 text-sm font-black">
                                    1
                                </span>
                                Pilih Nominal Deposit
                            </h3>

                            {{-- Preset Buttons --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 mb-8 relative z-10">
                                <template x-for="amount in presets" :key="amount">
                                    <button type="button" @click="setAmount(amount)"
                                        :class="currentAmount == amount ?
                                            'bg-#f3f4f6 text-gray-800 !shadow-[inset_4px_4px_8px_rgba(0,0,0,0.2),inset_-4px_-4px_8px_rgba(255,255,255,0.1)] scale-[0.97]' :
                                            'btn-neumorph-primary'"
                                        class="py-5 px-4 rounded-2xl font-black transition-all duration-300 text-sm flex flex-col items-center justify-center border-none">

                                        <span x-text="formatRupiah(amount)"></span>
                                    </button>
                                </template>
                            </div>

                            {{-- Manual Input --}}
                            <div class="relative group z-10">
                                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                                    <span
                                        class="text-gray-400 font-black text-lg group-focus-within:text-emerald-500 transition-colors">Rp</span>
                                </div>
                                <input type="number" name="amount" x-model="currentAmount" required min="10000"
                                    class="input-neumorph-inset w-full pl-16 py-5 text-gray-800 font-black text-2xl tracking-tight outline-none border-none placeholder-gray-300"
                                    placeholder="Ketik nominal manual...">
                            </div>

                            <div class="flex items-center gap-2 mt-4 ml-2 opacity-60">
                                <div class="progress-track p-1 rounded-full">
                                    <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider">
                                    Minimal deposit Rp 10.000
                                </p>
                            </div>
                        </div>

                        {{-- 
                        |-------------------------------------------------------
                        | CARD 2: METODE PEMBAYARAN (UKURAN COMPACT & ELEGANT)
                        |-------------------------------------------------------
                        --}}
                        <div class="card-timbul !rounded-[2.5rem] p-8 relative overflow-hidden group border-none">

                            {{-- Dekorasi Angka --}}
                            <div
                                class="absolute -top-6 -right-6 text-[10rem] font-black text-gray-200 opacity-20 select-none pointer-events-none group-hover:text-indigo-100/30 transition-colors duration-500">
                                2
                            </div>

                            {{-- Header --}}
                            <div class="relative z-10 mb-8">
                                <h3 class="text-gray-800 font-extrabold text-xl flex items-center gap-4">
                                    <span
                                        class="card-inset-panel flex items-center justify-center w-10 h-10 !rounded-xl text-emerald-600 text-sm font-black">
                                        2
                                    </span>
                                    Metode Pembayaran
                                </h3>
                                <p class="text-[11px] text-gray-400 mt-3 ml-14 font-medium leading-relaxed">
                                    Secured by <strong class="text-gray-600">Xendit</strong>. Transaksi terenkripsi
                                    dan terverifikasi otomatis 24/7.
                                </p>
                            </div>

                            <div class="space-y-8 ml-0 md:ml-14 relative z-10">

                                {{-- Kategori 1: BANK TRANSFER --}}
                                <div>
                                    <h4
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                        <div class="progress-track p-1 rounded-md">
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z">
                                                </path>
                                            </svg>
                                        </div>
                                        Virtual Account
                                    </h4>

                                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                                        @php
                                            $banks = [
                                                [
                                                    'name' => 'BCA',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg',
                                                ],
                                                [
                                                    'name' => 'MANDIRI',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg',
                                                ],
                                                [
                                                    'name' => 'BRI',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/6/68/BANK_BRI_logo.svg',
                                                ],
                                                [
                                                    'name' => 'BNI',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/c/ca/Bank_BNI_Logo.png',
                                                ],
                                                [
                                                    'name' => 'PERMATA',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/f/ff/Permata_Bank_%282024%29.svg',
                                                ],
                                                [
                                                    'name' => 'CIMB',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/3/38/CIMB_Niaga_logo.svg',
                                                ],
                                                [
                                                    'name' => 'BSI',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg',
                                                ],
                                                [
                                                    'name' => 'BJB',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/7/7b/Danamon.svg',
                                                ],
                                            ];
                                        @endphp

                                        @foreach ($banks as $bank)
                                            <div
                                                class="card-timbul h-12 flex items-center justify-center p-2.5 !rounded-xl border-none hover:scale-105 transition-transform duration-300 cursor-pointer group/bank">
                                                <img src="{{ $bank['logo'] }}" alt="{{ $bank['name'] }}"
                                                    class="h-full w-auto group-hover/bank:grayscale-0 transition-all duration-300">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Kategori 2: E-WALLET & QRIS --}}
                                <div>
                                    <h4
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                        <div class="progress-track p-1 rounded-md">
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                                </path>
                                            </svg>
                                        </div>
                                        E-Wallet / QRIS
                                    </h4>

                                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
                                        {{-- Special QRIS --}}
                                        <div
                                            class="card-timbul h-12 flex items-center justify-center p-2.5 !rounded-xl border-none hover:scale-105 transition-transform duration-300 cursor-pointer group/qris">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a2/Logo_QRIS.svg"
                                                alt="QRIS"
                                                class="h-6 w-auto group-hover/qris:grayscale-0 transition-all duration-300">
                                        </div>

                                        @php
                                            $ewallets = [
                                                [
                                                    'name' => 'OVO',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg',
                                                ],
                                                [
                                                    'name' => 'DANA',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg',
                                                ],
                                                [
                                                    'name' => 'GOPAY',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg',
                                                ],
                                                [
                                                    'name' => 'SHOPEE',
                                                    'logo' =>
                                                        'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg',
                                                ],
                                            ];
                                        @endphp

                                        @foreach ($ewallets as $wallet)
                                            <div
                                                class="card-timbul h-12 flex items-center justify-center p-2.5 !rounded-xl border-none hover:scale-105 transition-transform duration-300 cursor-pointer group/wallet">
                                                <img src="{{ $wallet['logo'] }}" alt="{{ $wallet['name'] }}"
                                                    class="h-full w-auto group-hover/wallet:grayscale-0 transition-all duration-300">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Kategori 3: RETAIL --}}
                                <div>
                                    <h4
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                                        <div class="progress-track p-1 rounded-md">
                                            <svg class="w-3 h-3 text-emerald-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                                </path>
                                            </svg>
                                        </div>
                                        Gerai Retail
                                    </h4>

                                    <div class="flex gap-4">
                                        {{-- Alfamart --}}
                                        <div
                                            class="card-timbul h-12 w-28 flex items-center justify-center p-3 !rounded-xl border-none hover:scale-105 transition-transform duration-300 cursor-pointer group/retail">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Alfamart_logo.svg"
                                                alt="Alfamart"
                                                class="h-full w-auto group-hover/retail:grayscale-0 transition-all duration-300">
                                        </div>
                                        {{-- Indomaret --}}
                                        <div
                                            class="card-timbul h-12 w-28 flex items-center justify-center p-3 !rounded-xl border-none hover:scale-105 transition-transform duration-300 cursor-pointer group/retail">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/9d/Logo_Indomaret.png"
                                                alt="Indomaret"
                                                class="h-full w-auto group-hover/retail:grayscale-0 transition-all duration-300">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- 
                    |-------------------------------------------------------
                    | SIDEBAR: RINGKASAN PEMBAYARAN (UPDATED)
                    |-------------------------------------------------------
                    --}}
                    <div class="lg:col-span-1">
                        <div class="card-neumorphism !rounded-[2.5rem] sticky top-32 overflow-hidden border-none">
                            {{-- Header Summary: Dibuat lebih soft namun tetap berkelas --}}
                            <div class="p-8 bg-emerald-600 relative overflow-hidden">
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-10 -mt-10 blur-2xl">
                                </div>
                                <h3 class="text-white font-black text-xl relative z-10">Ringkasan</h3>
                                <p
                                    class="text-indigo-100 text-[10px] uppercase tracking-[0.2em] relative z-10 mt-1 font-bold">
                                    Detail transaksi Anda
                                </p>
                            </div>

                            <div class="p-8 space-y-6">
                                {{-- Baris Rincian --}}
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-500 font-bold text-xs uppercase tracking-wider">Nominal
                                            Top Up</span>
                                        <div class="card-inset-panel px-3 py-1.5 rounded-xl">
                                            <span class="text-gray-800 font-mono font-black text-sm"
                                                x-text="formatRupiah(currentAmount || 0)"></span>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-500 font-bold text-xs uppercase tracking-wider">Biaya
                                            Admin</span>
                                        <div class="card-inset-panel px-3 py-1.5 rounded-xl">
                                            <span class="text-emerald-600 font-mono font-black text-sm">Rp 0</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Total Bayar: Area Fokus Utama --}}
                                <div class="card-inset-panel p-5 !rounded-2xl bg-gray-50/50">
                                    <div class="flex justify-between items-end">
                                        <span
                                            class="text-gray-400 font-black text-[10px] uppercase tracking-[0.2em] mb-1">Total
                                            Bayar</span>
                                        <span
                                            class="text-2xl font-black text-gray-600 font-mono leading-none tracking-tighter"
                                            x-text="formatRupiah(currentAmount || 0)"></span>
                                    </div>
                                </div>

                                {{-- Info Box --}}
                                <div class="progress-track p-4 !rounded-2xl flex items-start gap-3 border-none">
                                    <div class="bg-emerald-100 p-1.5 rounded-lg shadow-inner">
                                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-[10px] text-emerald-700 leading-relaxed font-bold uppercase tracking-tight">
                                        Otomatis diarahkan ke halaman Xendit. Saldo masuk instan setelah pembayaran
                                        berhasil.
                                    </span>
                                </div>

                                {{-- Button Submit --}}
                                <button type="submit" :disabled="!currentAmount || currentAmount < 10000"
                                    class="w-full bg-emerald-600 text-white font-black py-5 rounded-2xl shadow-[6px_6px_12px_#c9c8c8] hover:bg-emerald-600 transition-all duration-300 transform active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none flex items-center justify-center gap-3 text-xs uppercase tracking-[0.2em] group border-none">
                                    <span>Konfirmasi & Bayar</span>
                                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    {{-- 
    |--------------------------------------------------------------------------
    | ALPINE JS SCRIPT (UPDATED)
    |--------------------------------------------------------------------------
    | - Hapus variabel uniqueCode.
    | - Payment Method default 'va'.
    --}}
    <script>
        function depositSystem() {
            return {
                currentAmount: '',
                paymentMethod: 'va', // Default Virtual Account
                presets: [50000, 100000, 200000, 500000, 1000000, 2500000],

                setAmount(value) {
                    this.currentAmount = value;
                },
                formatRupiah(number) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(number);
                }
            }
        }
    </script>
</x-app-layout>
