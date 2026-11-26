<x-app-layout>
    <div class="pt-32 pb-12 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- HEADER & BACK BUTTON --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
                <div>
                    <h2 class="text-3xl font-black text-gray-800 tracking-tighter">Rekening & E-Wallet</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mt-1">Kelola Metode
                        Pencairan Penghasilan</p>
                </div>

                <a href="{{ route('withdrawals.create') }}"
                    class="btn-neumorph-primary inline-flex items-center px-6 py-2.5 rounded-2xl group transition-all duration-300">
                    <svg class="w-4 h-4 mr-2 text-gray-600 group-hover:-translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Kembali</span>
                </a>
            </div>

            {{-- ALERT MESSAGES (Neumorphic Style) --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                    x-transition:leave="transition ease-in duration-300"
                    class="mb-10 card-neumorphism border-l-4 border-emerald-500 p-5 flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 card-inset-panel flex items-center justify-center text-emerald-500">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <span
                            class="text-xs font-black uppercase tracking-wide text-gray-700">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-gray-400 hover:text-rose-500 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">

                {{-- KOLOM KIRI: DAFTAR REKENING --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-gray-400">Daftar Tersimpan</h3>
                        <span class="text-[10px] font-black text-emerald-500 card-inset-panel px-4 py-1 rounded-full">
                            {{ $methods->count() }} Akun Bank / E-Wallet
                        </span>
                    </div>

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

                    @forelse($methods as $method)
                        <div
                            class="card-neumorphism p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 transition-all duration-300 relative overflow-hidden group">

                            {{-- Dekorasi Background --}}
                            <div
                                class="absolute -right-10 -top-10 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all duration-500">
                            </div>

                            <div class="flex items-center gap-6 relative z-10">
                                {{-- Logo Bank (Inset Style) --}}
                                <div
                                    class="w-16 h-16 card-inset-panel flex items-center justify-center p-3 overflow-hidden">
                                    @if (isset($bankLogos[$method->bank_name]))
                                        <img src="{{ $bankLogos[$method->bank_name] }}" alt="{{ $method->bank_name }}"
                                            class="max-w-full max-h-full object-contain filter grayscale group-hover:grayscale-0 transition-all duration-500">
                                    @else
                                        <span class="text-emerald-600 font-black text-2xl uppercase">
                                            {{ substr($method->bank_name, 0, 1) }}
                                        </span>
                                    @endif {{-- PENUTUP IF LOGO --}}
                                </div>

                                <div>
                                    <h4
                                        class="text-gray-800 font-black text-lg tracking-tight uppercase flex items-center gap-3">
                                        {{ $method->bank_name }}
                                    </h4>
                                    <p class="text-emerald-600 text-sm font-mono font-bold tracking-widest mt-1">
                                        {{ $method->account_number }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mt-1">a/n
                                        {{ $method->account_name }}</p>
                                </div>
                            </div>

                            <form action="{{ route('payout-methods.destroy', $method->id) }}" method="POST"
                                onsubmit="return confirm('Hapus rekening ini?')" class="relative z-10 w-full sm:w-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="btn-neumorph-primary w-full sm:w-auto px-5 py-3 rounded-2xl text-rose-500 group/del transition-all">
                                    <i class="fas fa-trash-alt group-hover/del:scale-110 transition-transform mr-2"></i>
                                    <span class="text-[10px] font-black uppercase tracking-widest">Hapus</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="card-inset-panel py-16 text-center text-gray-400">
                            <p class="text-xs font-bold uppercase tracking-widest italic">Belum ada rekening tersimpan
                            </p>
                        </div>
                    @endforelse {{-- PENUTUP FORELSE --}}
                </div>

                {{-- KOLOM KANAN: FORM TAMBAH --}}
                <div class="lg:col-span-1">
                    <div class="card-neumorphism p-8 sticky top-32">

                        <div class="flex items-center gap-4 mb-8 pb-6 border-b border-gray-100">
                            <div class="w-12 h-12 card-inset-panel flex items-center justify-center text-emerald-500">
                                <i class="fas fa-plus text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Rekening Baru
                                </h3>
                                <p class="text-[9px] text-gray-400 font-bold uppercase mt-1">Lengkapi data valid</p>
                            </div>
                        </div>

                        {{-- DEFINISI LOGO BANK (PHP ARRAY) --}}
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
                                'BSI' =>
                                    'https://upload.wikimedia.org/wikipedia/commons/a/a0/Bank_Syariah_Indonesia.svg',
                                'Danamon' => 'https://upload.wikimedia.org/wikipedia/commons/7/7b/Danamon.svg',
                                'GoPay' => 'https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg',
                                'Dana' => 'https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg',
                                'OVO' => 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Logo_ovo_purple.svg',
                                'ShopeePay' => 'https://upload.wikimedia.org/wikipedia/commons/f/fe/Shopee.svg',
                                'LinkAja' => 'https://upload.wikimedia.org/wikipedia/commons/8/85/LinkAja.svg',
                            ];
                        @endphp

                        <form action="{{ route('payout-methods.store') }}" method="POST" class="space-y-6">
                            @csrf

                            {{-- CUSTOM DROPDOWN BANK / E-WALLET DENGAN LOGO --}}
                            <div class="space-y-3" x-data="{
                                open: false,
                                selectedLabel: '{{ old('bank_name') ?: '-- Pilih Bank --' }}',
                                selectedValue: '{{ old('bank_name') ?: '' }}',
                                {{-- Logika untuk load logo saat ada 'old' value --}}
                                selectedLogo: '{{ old('bank_name') ? $bankLogos[old('bank_name')] ?? '' : '' }}'
                            }">
                                <label
                                    class="block text-[9px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2 ml-1">
                                    Bank / E-Wallet
                                </label>

                                <div class="relative">
                                    {{-- TOMBOL TRIGGER (Timbul/Embossed) --}}
                                    <button type="button" @click="open = !open"
                                        class="btn-neumorph-primary w-full p-4 flex items-center rounded-2xl justify-between outline-none group transition-all duration-300">

                                        <div class="flex items-center gap-3">
                                            {{-- LOGO CONTAINER (Cekung/Inset) --}}
                                            <div x-show="selectedLogo"
                                                class="w-8 h-8 card-inset-panel p-1.5 flex items-center justify-center overflow-hidden">
                                                <img :src="selectedLogo"
                                                    class="max-w-full max-h-full object-contain filter grayscale group-hover:grayscale-0 transition-all duration-500">
                                            </div>
                                            <span class="text-xs font-black uppercase tracking-widest text-gray-700"
                                                x-text="selectedLabel"></span>
                                        </div>

                                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-300"
                                            :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    {{-- INPUT HIDDEN --}}
                                    <input type="hidden" name="bank_name" x-model="selectedValue" required>

                                    {{-- LIST DROPDOWN --}}
                                    <div x-show="open" @click.away="open = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="absolute z-50 w-full mt-4 card-neumorphism p-3 shadow-2xl overflow-hidden">

                                        <div class="max-h-64 overflow-y-auto custom-scrollbar space-y-4">
                                            {{-- Kategori: Bank Transfer --}}
                                            <div>
                                                <span
                                                    class="block text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-2 px-2">Bank
                                                    Transfer</span>
                                                <div class="space-y-1">
                                                    @foreach (['BCA', 'BRI', 'Mandiri', 'BNI', 'Permata', 'CIMB', 'BSI', 'Danamon'] as $bank)
                                                        <button type="button"
                                                            @click="
                                                selectedLabel = '{{ $bank }}'; 
                                                selectedValue = '{{ $bank }}'; 
                                                selectedLogo = '{{ $bankLogos[$bank] }}';
                                                open = false"
                                                            class="w-full text-left px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-600 rounded-xl transition-all duration-200 hover:card-inset-panel hover:text-emerald-600 flex items-center gap-3 group/item">
                                                            <div
                                                                class="w-8 h-8 bg-white/50 rounded-lg p-1.5 flex items-center justify-center border border-white/50">
                                                                <img src="{{ $bankLogos[$bank] }}"
                                                                    class="max-w-full max-h-full object-contain">
                                                            </div>
                                                            {{ $bank }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Kategori: E-Wallet --}}
                                            <div class="border-t border-gray-100 pt-3">
                                                <span
                                                    class="block text-[9px] font-black text-emerald-400 uppercase tracking-widest mb-2 px-2">E-Wallet</span>
                                                <div class="space-y-1">
                                                    @foreach (['GoPay', 'Dana', 'OVO', 'ShopeePay', 'LinkAja'] as $wallet)
                                                        <button type="button"
                                                            @click="
                                                selectedLabel = '{{ $wallet }}'; 
                                                selectedValue = '{{ $wallet }}'; 
                                                selectedLogo = '{{ $bankLogos[$wallet] }}';
                                                open = false"
                                                            class="w-full text-left px-3 py-2 text-[10px] font-black uppercase tracking-widest text-gray-600 rounded-xl transition-all duration-200 hover:card-inset-panel hover:text-emerald-600 flex items-center gap-3 group/item">
                                                            <div
                                                                class="w-8 h-8 bg-white/50 rounded-lg p-1.5 flex items-center justify-center border border-white/50">
                                                                <img src="{{ $bankLogos[$wallet] }}"
                                                                    class="max-w-full max-h-full object-contain">
                                                            </div>
                                                            {{ $wallet }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-[9px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2 ml-1">No.
                                    Rekening / HP</label>
                                <input type="number" name="account_number" placeholder="08xxxxxxxx"
                                    class="input-neumorph-inset w-full p-4 text-xs font-black uppercase tracking-widest text-gray-700 outline-none placeholder-gray-300"
                                    required>
                            </div>

                            <div>
                                <label
                                    class="block text-[9px] font-black uppercase text-gray-400 tracking-[0.2em] mb-2 ml-1">Atas
                                    Nama</label>
                                <input type="text" name="account_name" placeholder="Sesuai Buku Tabungan"
                                    class="input-neumorph-inset w-full p-4 text-xs font-black uppercase tracking-widest text-gray-700 outline-none placeholder-gray-300"
                                    required>
                            </div>

                            {{-- Tombol Submit --}}
                            <button type="submit"
                                class="btn-neumorph-primary w-full py-4 rounded-2xl bg-gradient-to-br from-[#f3f4f6] to-emerald-50 text-emerald-600 font-black uppercase tracking-[0.2em] text-[10px] flex items-center justify-center gap-3 mt-4 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                <i class="fas fa-save"></i>
                                Simpan Rekening
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
