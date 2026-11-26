<x-app-layout>
    {{-- HEADER BAWAAN DIHAPUS --}}

    <div class="pt-32 pb-12 bg-gray-100 min-h-screen" x-data="{
        timeLeft: 3600,
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            alert('Disalin: ' + text);
        },
        initTimer() {
            setInterval(() => {
                if (this.timeLeft > 0) this.timeLeft--;
            }, 1000);
        },
        formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return `${m}:${s < 10 ? '0' : ''}${s}`;
        }
    }" x-init="initTimer()">

        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- HEADER & BACK BUTTON --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Selesaikan Pembayaran</h2>
                    <p class="text-sm text-gray-500 mt-1">Lakukan transfer sebelum waktu habis.</p>
                </div>

                <a href="{{ route('deposits.create') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            {{-- TIMER BANNER (MODERN GRADIENT) --}}
            <div
                class="bg-gradient-to-r from-indigo-600 to-violet-600 rounded-[2.5rem] p-6 md:p-8 mb-10 shadow-2xl shadow-indigo-200 relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-left">

                {{-- Dekorasi --}}
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none">
                </div>

                <div class="relative z-10 flex items-center gap-4">
                    <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm">
                        <svg class="w-8 h-8 text-white animate-pulse" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-indigo-100 text-xs font-bold uppercase tracking-widest mb-1">Sisa Waktu
                            Pembayaran</p>
                        <p class="text-4xl font-black text-white font-mono tracking-wider"
                            x-text="formatTime(timeLeft)"></p>
                    </div>
                </div>

                <div class="relative z-10 bg-white/10 border border-white/20 px-5 py-3 rounded-xl backdrop-blur-md">
                    <p class="text-white text-xs font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        Harap transfer tepat sesuai nominal (3 digit terakhir).
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

                {{-- KIRI: DETAIL PEMBAYARAN --}}
                <div
                    class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="text-gray-900 font-bold text-lg">Instruksi Transfer</h3>
                        <span
                            class="px-3 py-1 bg-white border border-gray-200 rounded-lg text-[10px] font-mono text-gray-500 font-bold shadow-sm">#{{ $deposit->external_ref }}</span>
                    </div>

                    <div class="p-8 space-y-8">

                        {{-- TOTAL AMOUNT CARD --}}
                        <div
                            class="bg-white p-6 rounded-3xl border border-indigo-100 shadow-lg shadow-indigo-50/50 text-center relative group overflow-hidden">
                            <div
                                class="absolute inset-0 bg-indigo-50/30 opacity-0 group-hover:opacity-100 transition duration-500">
                            </div>
                            <p class="text-gray-400 text-[10px] uppercase font-black tracking-widest mb-2">Total
                                Transfer
                            </p>
                            <div x-data="{
                                copied: false,
                                copyToClipboard(text) {
                                    // Hapus 'Rp ' dan titik agar yang disalin hanya angka murni (Opsional, sesuaikan kebutuhan)
                                    // text = text.toString().replace(/\./g, ''); 
                            
                                    if (navigator.clipboard && window.isSecureContext) {
                                        navigator.clipboard.writeText(text).then(() => this.showSuccess());
                                    } else {
                                        // Fallback untuk browser lama
                                        let textArea = document.createElement('textarea');
                                        textArea.value = text;
                                        textArea.style.position = 'fixed';
                                        textArea.style.left = '-9999px';
                                        document.body.appendChild(textArea);
                                        textArea.focus();
                                        textArea.select();
                                        try { document.execCommand('copy');
                                            this.showSuccess(); } catch (e) {}
                                        document.body.removeChild(textArea);
                                    }
                                },
                                showSuccess() {
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 2000); // Balik normal setelah 2 detik
                                }
                            }" class="flex items-center justify-center gap-3 relative z-10">

                                {{-- TEKS NOMINAL --}}
                                <span
                                    class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 tracking-tight">
                                    Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}
                                </span>

                                {{-- TOMBOL COPY --}}
                                <button @click="copyToClipboard('{{ $deposit->total_amount }}')"
                                    class="p-2 rounded-lg transition-all duration-300 transform active:scale-95"
                                    :class="copied ? 'bg-green-100 text-green-600 scale-110' :
                                        'bg-gray-100 hover:bg-indigo-100 text-gray-400 hover:text-indigo-600'"
                                    :title="copied ? 'Berhasil Disalin!' : 'Salin Nominal'">

                                    {{-- Ikon Copy (Tampil saat normal) --}}
                                    <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                        </path>
                                    </svg>

                                    {{-- Ikon Centang (Tampil saat sukses) --}}
                                    <svg x-show="copied" style="display: none;" class="w-5 h-5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                            </div>
                            <p
                                class="text-[10px] text-indigo-400 mt-2 font-medium bg-indigo-50 inline-block px-3 py-1 rounded-full border border-indigo-100">
                                *Transfer nominal unik agar terverifikasi otomatis.
                            </p>
                        </div>

                        @if ($deposit->payment_gateway === 'qris')
                            <div class="text-center bg-gray-50 rounded-3xl p-6 border border-gray-100"
                                x-data="{ showZoom: false }">
                                <p class="text-gray-900 text-sm mb-4 font-bold">Scan QRIS di bawah ini:</p>

                                {{-- Container Gambar (Ditambah: relative, group, cursor-pointer, click event) --}}
                                <div class="bg-white p-4 rounded-2xl inline-block shadow-md border border-gray-100 relative group cursor-pointer transition-transform duration-300 hover:scale-[1.02]"
                                    @click="showZoom = true">

                                    {{-- Gambar Asli --}}
                                    <img src="{{ asset('images/qris.png') }}" alt="QRIS Code"
                                        class="w-48 h-48 object-contain">

                                    {{-- Tombol Zoom (Muncul saat Hover) --}}
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-black/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                        <button
                                            class="bg-white text-indigo-600 p-2 rounded-full shadow-lg transform scale-0 group-hover:scale-100 transition-transform duration-300 delay-75">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div
                                    class="flex justify-center gap-2 mt-6 opacity-60 grayscale hover:grayscale-0 transition duration-500">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Logo_QRIS.svg/1200px-Logo_QRIS.svg.png"
                                        class="h-6">
                                </div>

                                {{-- MODAL ZOOM (Hidden by default) --}}
                                <template x-teleport="body">
                                    <div x-show="showZoom" style="display: none;"
                                        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-8">

                                        {{-- Overlay Gelap --}}
                                        <div @click="showZoom = false" x-show="showZoom"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                            class="fixed inset-0 bg-black/90 backdrop-blur-sm"></div>

                                        {{-- Container Gambar Besar --}}
                                        <div x-show="showZoom"
                                            x-transition:enter="transition ease-out duration-300 delay-100"
                                            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="relative z-10 w-full max-w-2xl flex flex-col items-center">

                                            {{-- Tombol Close --}}
                                            <button @click="showZoom = false"
                                                class="absolute -top-12 right-0 p-2 text-white hover:text-gray-300 transition">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>

                                            {{-- Gambar Full HD --}}
                                            <img src="{{ asset('images/qris.png') }}"
                                                class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl border-4 border-white/10"
                                                @click.stop>

                                            <p class="text-white/80 mt-4 font-medium text-sm flex items-center gap-2">
                                                <svg class="w-4 h-4 animate-bounce" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                </svg>
                                                Silakan scan kode di atas
                                            </p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        @else
                            <div class="space-y-4">
                                {{-- BANK BCA --}}
                                <div
                                    class="flex items-center gap-5 bg-white p-5 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300">
                                    <div
                                        class="bg-white p-2 rounded-xl h-14 w-20 flex items-center justify-center border border-gray-100 shadow-sm">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/2560px-Bank_Central_Asia.svg.png"
                                            class="max-h-full max-w-full">
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Bank
                                            BCA</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-gray-900 font-mono text-xl font-bold tracking-tight">
                                                123-456-7890</p>
                                            <button @click="copyToClipboard('1234567890')"
                                                class="text-indigo-600 hover:text-indigo-800 text-[10px] font-bold bg-indigo-50 px-2 py-1 rounded border border-indigo-100 transition">SALIN</button>
                                        </div>
                                        <p class="text-xs text-gray-500 font-medium mt-0.5">a.n ClipSquad Official</p>
                                    </div>
                                </div>

                                {{-- BANK MANDIRI --}}
                                <div
                                    class="flex items-center gap-5 bg-white p-5 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300">
                                    <div
                                        class="bg-white p-2 rounded-xl h-14 w-20 flex items-center justify-center border border-gray-100 shadow-sm">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Bank_Mandiri_logo_2016.svg/2560px-Bank_Mandiri_logo_2016.svg.png"
                                            class="max-h-full max-w-full">
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Bank
                                            Mandiri</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-gray-900 font-mono text-xl font-bold tracking-tight">
                                                123-456-7890</p>
                                            <button @click="copyToClipboard('1234567890')"
                                                class="text-indigo-600 hover:text-indigo-800 text-[10px] font-bold bg-indigo-50 px-2 py-1 rounded border border-indigo-100 transition">SALIN</button>
                                        </div>
                                        <p class="text-xs text-gray-500 font-medium mt-0.5">a.n ClipSquad Official</p>
                                    </div>
                                </div>

                                {{-- BANK BRI --}}
                                <div
                                    class="flex items-center gap-5 bg-white p-5 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300">
                                    <div
                                        class="bg-white p-2 rounded-xl h-14 w-20 flex items-center justify-center border border-gray-100 shadow-sm">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/BANK_BRI_logo.svg/2560px-BANK_BRI_logo.svg.png"
                                            class="max-h-full max-w-full">
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Bank
                                            BRI</p>
                                        <div class="flex items-center gap-2">
                                            <p class="text-gray-900 font-mono text-xl font-bold tracking-tight">
                                                123-456-7890</p>
                                            <button @click="copyToClipboard('1234567890')"
                                                class="text-indigo-600 hover:text-indigo-800 text-[10px] font-bold bg-indigo-50 px-2 py-1 rounded border border-indigo-100 transition">SALIN</button>
                                        </div>
                                        <p class="text-xs text-gray-500 font-medium mt-0.5">a.n ClipSquad Official</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- KANAN: KONFIRMASI --}}
                <div class="space-y-6">
                    <div
                        class="bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/50 border border-gray-100 p-8 sticky top-32">
                        <div class="mb-6">
                            <h3 class="text-gray-900 font-black text-xl mb-2">Konfirmasi Pembayaran</h3>
                            <p class="text-gray-500 text-sm leading-relaxed">
                                Sudah transfer? Silakan upload bukti struk/screenshot pembayaran agar saldo bisa segera
                                kami proses.
                            </p>
                        </div>

                        <form action="{{ route('deposits.update', $deposit->id) }}" method="POST"
                            enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div
                                class="relative border-2 border-dashed border-indigo-200 bg-indigo-50/30 rounded-3xl p-8 text-center hover:bg-indigo-50 hover:border-indigo-400 transition group cursor-pointer overflow-hidden">
                                <input type="file" name="proof_image"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" required
                                    onchange="previewImage(event)">

                                <div class="space-y-3 relative z-10" id="upload-placeholder">
                                    <div
                                        class="mx-auto w-16 h-16 bg-white rounded-full flex items-center justify-center group-hover:scale-110 transition shadow-sm border border-indigo-100 text-indigo-400 group-hover:text-indigo-600">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-indigo-900 font-bold text-sm">Klik untuk upload bukti</p>
                                        <p class="text-indigo-400 text-xs mt-1">JPG, PNG (Max 2MB)</p>
                                    </div>
                                </div>
                                <img id="image-preview"
                                    class="hidden w-full h-64 object-contain rounded-2xl mx-auto relative z-10">
                            </div>

                            <button type="submit"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-200 transition transform active:scale-95 flex items-center justify-center gap-2 text-sm uppercase tracking-widest">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Saya Sudah Bayar
                            </button>
                        </form>
                    </div>

                    <div class="text-center">
                        <a href="#"
                            class="text-gray-400 text-xs font-bold hover:text-indigo-600 transition flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            Butuh bantuan? Hubungi CS
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('image-preview');
                const placeholder = document.getElementById('upload-placeholder');
                output.src = reader.result;
                output.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</x-app-layout>
