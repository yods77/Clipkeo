<x-app-layout>
    @php
        $user = Auth::user();
        $payout = $user->payoutMethod;
    @endphp

    <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">

            {{-- HEADER & BACK BUTTON --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h2 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">
                        Pengaturan Profil
                    </h2>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mt-2">
                        Kelola informasi pribadi, sosial media, dan keamanan akun.
                    </p>
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

            {{-- ================================================= --}}
            {{-- BAGIAN HEAD: KARTU PROFIL UTAMA (FOTO & ROLE) --}}
            {{-- ================================================= --}}
            <div class="bg-gray-100 relative overflow-hidden mb-8 group">
                <div class="absolute top-0 left-0 w-full h-40 bg-gray-100"></div>

                {{-- Profile Header with Cropper Integration --}}
                <div x-data="avatarPicker()"
                    class="relative z-10 flex flex-col md:flex-row items-center md:items-center gap-8 p-10 mt-8 text-center md:text-left">

                    {{-- Avatar Section --}}
                    <div class="relative group/avatar shrink-0">
                        {{-- Frame Avatar Inset (Cekung) --}}
                        <div
                            class="w-40 h-40 rounded-full card-inset-panel p-2 flex items-center justify-center relative transition-all duration-500">
                            <div class="w-full h-full rounded-full overflow-hidden relative bg-gray-200 shadow-inner">
                                {{-- Preview Image --}}
                                <img :src="previewUrl ||
                                    '{{ $user->avatar_url ? (Str::startsWith($user->avatar_url, 'http') ? $user->avatar_url : asset('storage/' . $user->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&size=128' }}'"
                                    class="w-full h-full aspect-square object-cover" id="avatar_preview">

                                {{-- Overlay Hover --}}
                                <label for="avatar_input"
                                    class="absolute inset-0 bg-emerald-600/60 flex flex-col items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-all duration-300 cursor-pointer text-white backdrop-blur-[2px]">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-[8px] font-black uppercase tracking-widest">Ubah Foto</span>
                                </label>
                            </div>
                        </div>
                        {{-- Input File Asli (Hidden) --}}
                        <input type="file" id="avatar_input" class="hidden" @change="fileChosen" accept="image/*">
                    </div>

                    {{-- User Info Section --}}
                    <div class="pb-4 space-y-2">
                        <div
                            class="card-timbul inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black text-gray-600 border-none mb-2">
                            <span
                                class="w-2 h-2 mr-2 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                            <span class="tracking-[0.2em] uppercase">Status: {{ $user->role }}</span>
                        </div>

                        <h3 class="text-4xl font-black text-gray-900 tracking-tighter leading-none">
                            {{ $user->name }}
                        </h3>

                        <p
                            class="text-[12px] font-bold text-gray-400 flex items-center justify-center md:justify-start gap-2 italic tracking-wide">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $user->username ? '@' . $user->username : $user->email }}
                        </p>
                    </div>

                    {{-- MODAL CROPPER REFINED --}}
                    <template x-teleport="body">
                        <div x-show="showModal"
                            class="fixed inset-0 z-[999] flex items-center justify-center p-6 bg-black/60 backdrop-blur-md"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                            style="display: none;">

                            <div
                                class="card-neumorphism w-full max-w-md overflow-hidden rounded-[2.5rem] bg-[#f3f4f6] border-none shadow-2xl">

                                {{-- Header Modal --}}
                                <div
                                    class="px-8 py-6 border-b border-gray-200/50 flex justify-between items-center bg-white/30">
                                    <h3 class="text-[11px] font-black uppercase tracking-[0.3em] text-gray-800 italic">
                                        Setting Photo Profile
                                    </h3>
                                    <button @click="showModal = false"
                                        class="text-gray-400 hover:text-rose-500 transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="p-8 space-y-6">
                                    {{-- Area Crop - Dibuat Inset & Fix Aspect Ratio --}}
                                    <div class="card-inset-panel rounded-[2rem] overflow-hidden bg-gray-900 shadow-inner"
                                        style="height: 320px;">
                                        <img id="image_to_crop" class="max-w-full block mx-auto">
                                    </div>

                                    {{-- Instruksi Singkat --}}
                                    <p class="text-[9px] font-bold text-gray-400 uppercase text-center tracking-widest">
                                        Geser & Zoom foto sampai pas di dalam kotak
                                    </p>

                                    {{-- Action Buttons --}}
                                    <div class="flex gap-4 pt-2">
                                        <button @click="showModal = false" type="button"
                                            class="flex-1 py-4 btn-neumorph-primary rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-gray-600 transition-all">
                                            Cancel
                                        </button>
                                        <button @click="saveCrop" type="button"
                                            class="flex-[2] py-4 btn-neumorph-primary rounded-2xl text-[10px] font-black uppercase tracking-widest text-emerald-600">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Form Hidden untuk kirim data Base64 ke Server --}}
                    <form id="avatar-form" method="POST" action="{{ route('profile.update.avatar') }}">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="cropped_image" id="cropped_image_input">
                    </form>
                </div>

                {{-- SCRIPT CROPPER LOGIC --}}
                <script>
                    function avatarPicker() {
                        return {
                            showModal: false,
                            previewUrl: null,
                            cropper: null,

                            fileChosen(event) {
                                const file = event.target.files[0];
                                if (!file) return;

                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    this.showModal = true;
                                    this.$nextTick(() => {
                                        const image = document.getElementById('image_to_crop');
                                        image.src = e.target.result;

                                        if (this.cropper) this.cropper.destroy();

                                        this.cropper = new Cropper(image, {
                                            aspectRatio: 1, // Memaksa Crop Jadi Lingkaran/Kotak
                                            viewMode: 1,
                                            dragMode: 'move',
                                            autoCropArea: 1,
                                            guides: false,
                                            center: true,
                                            highlight: false,
                                            cropBoxMovable: false,
                                            cropBoxResizable: false,
                                            toggleDragModeOnDblclick: false,
                                        });
                                    });
                                };
                                reader.readAsDataURL(file);
                            },

                            async saveCrop() {
                                if (!this.cropper) return;

                                // 1. Ambil hasil crop dalam bentuk Canvas
                                const canvas = this.cropper.getCroppedCanvas({
                                    width: 400,
                                    height: 400
                                });

                                // 2. Ubah ke Base64
                                const base64Image = canvas.toDataURL('image/jpeg', 0.9);

                                try {
                                    // 3. Kirim via FETCH (AJAX), bukan via .submit()
                                    const response = await fetch("{{ route('profile.update.avatar') }}", {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Penting agar tidak 419
                                        },
                                        body: JSON.stringify({
                                            cropped_image: base64Image
                                        })
                                    });

                                    // 4. Baca respon JSON dari Controller
                                    const result = await response.json();

                                    if (result.status === 'success') {
                                        // Tutup modal dan refresh halaman
                                        this.showModal = false;
                                        window.location.reload();
                                    } else {
                                        alert('Gagal mengunggah: ' + result.message);
                                    }

                                } catch (error) {
                                    console.error("Kesalahan sistem:", error);
                                    alert('Terjadi kesalahan saat menghubungi server.');
                                }
                            }
                        }
                    }
                </script>
                {{-- ================================================= --}}
                {{-- BAGIAN 1: INFORMASI DASAR (GRID 2 KOLOM) --}}
                {{-- ================================================= --}}
                {{-- CARD: INFORMASI DASAR --}}
                <div class="card-neumorphism p-10 rounded-[3rem] border-none relative mt-8 !overflow-visible z-10">
                    {{-- Header Section dengan Icon Inset --}}
                    <div class="flex items-center gap-4 mb-10">
                        <div
                            class="w-12 h-12 card-inset-panel flex items-center justify-center rounded-2xl text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest">Informasi Dasar
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight mt-1">Lengkapi Data
                                Pribadi Anda</p>
                        </h2>

                    </div>

                    <div>
                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('profile.update') }}" class="space-y-8">
                            @csrf
                            @method('patch')

                            {{-- GRID SYSTEM: Tetap 1 Kolom di HP, 2 Kolom di Laptop --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                                {{-- NAMA --}}
                                <div class="space-y-2">
                                    <label for="name"
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                        {{ __('Nama Lengkap') }}
                                    </label>
                                    <input id="name" name="name" type="text"
                                        class="input-neumorph-inset w-full px-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                        value="{{ old('name', $user->name) }}" required autocomplete="name" />
                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                </div>

                                {{-- USERNAME --}}
                                <div class="space-y-2">
                                    <label for="username"
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                        {{ __('Username') }}
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute inset-y-0 left-0 pl-6 flex items-center text-gray-500 font-black text-sm">@</span>
                                        <input id="username" name="username" type="text"
                                            class="input-neumorph-inset w-full pl-10 pr-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                            value="{{ old('username', $user->username) }}" required />
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('username')" />
                                </div>

                                {{-- EMAIL --}}
                                <div class="space-y-2">
                                    <label for="email"
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                        {{ __('Email Address') }}
                                    </label>
                                    <input id="email" name="email" type="email"
                                        class="input-neumorph-inset w-full px-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                        value="{{ old('email', $user->email) }}" required autocomplete="username" />
                                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                                        <div class="mt-3 px-4">
                                            <p class="text-[11px] font-bold text-amber-600 italic">
                                                {{ __('Email belum verifikasi.') }}
                                                <button form="send-verification"
                                                    class="underline hover:text-amber-800 ml-1">
                                                    {{ __('Kirim ulang.') }}
                                                </button>
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- WHATSAPP --}}
                                <div class="space-y-2">
                                    <label for="phone_number"
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                        {{ __('Nomor WhatsApp') }}
                                    </label>
                                    <input id="phone_number" name="phone_number" type="text"
                                        class="input-neumorph-inset w-full px-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                        value="{{ old('phone_number', $user->phone_number) }}"
                                        placeholder="0812xxxx" />
                                    <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                                </div>

                                {{-- JENIS KELAMIN (CUSTOM DROPDOWN) --}}
                                <div class="space-y-2">
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                        {{ __('Jenis Kelamin') }}
                                    </label>
                                    <div class="relative" x-data="{
                                        open: false,
                                        selectedValue: '{{ old('gender', $user->gender) }}',
                                        selectedLabel: '{{ old('gender', $user->gender) ?: '-- Pilih Jenis Kelamin --' }}',
                                        options: ['Laki-laki', 'Perempuan']
                                    }">
                                        {{-- Tombol Trigger --}}
                                        <button type="button" @click="open = !open"
                                            class="input-neumorph-inset w-full px-6 py-4 flex items-center justify-between text-sm font-bold text-gray-700 outline-none transition-all">
                                            <span x-text="selectedLabel"></span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-300"
                                                :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        {{-- Input Hidden untuk Form Submit --}}
                                        <input type="hidden" name="gender" x-model="selectedValue">

                                        {{-- List Pilihan --}}
                                        <div x-show="open" @click.away="open = false" style="display: none;"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="absolute z-50 w-full mt-2 card-neumorphism p-3 shadow-2xl overflow-hidden">
                                            <div class="space-y-1">
                                                <template x-for="option in options" :key="option">
                                                    <button type="button"
                                                        @click="selectedValue = option; selectedLabel = option; open = false"
                                                        class="w-full text-left px-4 py-3 text-xs font-black uppercase tracking-widest text-gray-600 rounded-xl transition-all duration-200 hover:card-inset-panel hover:text-emerald-600">
                                                        <span x-text="option"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('gender')" />
                                </div>

                                {{-- TANGGAL LAHIR --}}
                                <div class="space-y-2">
                                    <label for="birthday"
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                        {{ __('Tanggal Lahir') }}
                                    </label>
                                    <input id="birthday" name="birthday" type="date"
                                        class="input-neumorph-inset w-full px-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                        value="{{ old('birthday', $user->birthday) }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('birthday')" />
                                </div>

                                {{-- WRAPPER PROVINSI & KOTA --}}
{{-- Kita tambahkan :class untuk mengatur z-index secara dinamis --}}
<div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-8 pb-10" 
     x-data="locationData()"
     :class="provOpen || cityOpen ? 'relative z-[100]' : 'relative z-1'">
    
    {{-- PROVINSI (CUSTOM DROPDOWN) --}}
    <div class="space-y-2 relative" :class="provOpen ? 'z-[110]' : 'z-0'">
        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
            {{ __('Provinsi') }}
        </label>
        <div class="relative">
            <button type="button" @click="provOpen = !provOpen; cityOpen = false"
                class="input-neumorph-inset w-full px-6 py-4 flex items-center justify-between text-sm font-bold text-gray-700 outline-none transition-all">
                <span x-text="selectedProvLabel"></span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-300" :class="provOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <input type="hidden" name="province_id" x-model="selectedProvValue">

            {{-- List Pilihan --}}
            <div x-show="provOpen" @click.away="provOpen = false" style="display: none;"
                x-transition:enter="transition ease-out duration-200" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100"
                {{-- Gunakan bg-white padat agar tidak tembus pandang --}}
                class="absolute left-0 right-0 z-[200] mt-2 bg-white card-neumorphism p-3 shadow-2xl border border-gray-100">
                <div class="max-h-64 overflow-y-auto pr-2 custom-scrollbar space-y-1">
                    <template x-for="prov in provinces" :key="prov.id">
                        <button type="button" @click="selectProvince(prov)"
                            class="w-full text-left px-4 py-3 text-[11px] font-black uppercase tracking-widest text-gray-600 rounded-xl transition-all duration-200 hover:card-inset-panel hover:text-emerald-600">
                            <span x-text="prov.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('province_id')" />
    </div>

    {{-- KOTA / KABUPATEN (CUSTOM DROPDOWN) --}}
    <div class="space-y-2 relative" :class="cityOpen ? 'z-[110]' : 'z-0'">
        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
            {{ __('Kota / Kabupaten') }}
        </label>
        <div class="relative">
            <button type="button" @click="if(!isLoadingCities && selectedProvValue) { cityOpen = !cityOpen; provOpen = false; }"
                :class="!selectedProvValue || isLoadingCities ? 'opacity-50 cursor-not-allowed' : ''"
                class="input-neumorph-inset w-full px-6 py-4 flex items-center justify-between text-sm font-bold text-gray-700 outline-none transition-all">
                
                <div class="flex items-center gap-2">
                    <svg x-show="isLoadingCities" class="animate-spin h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="selectedCityLabel"></span>
                </div>
                
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-300" :class="cityOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <input type="hidden" name="city_id" x-model="selectedCityValue">

            {{-- List Pilihan --}}
            <div x-show="cityOpen" @click.away="cityOpen = false" style="display: none;"
                x-transition:enter="transition ease-out duration-200" 
                x-transition:enter-start="opacity-0 scale-95" 
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute left-0 right-0 z-[200] mt-2 bg-white card-neumorphism p-3 shadow-2xl border border-gray-100">
                <div class="max-h-64 overflow-y-auto pr-2 custom-scrollbar space-y-1">
                    <template x-for="city in cities" :key="city.id">
                        <button type="button" @click="selectCity(city)"
                            class="w-full text-left px-4 py-3 text-[11px] font-black uppercase tracking-widest text-gray-600 rounded-xl transition-all duration-200 hover:card-inset-panel hover:text-emerald-600">
                            <span x-text="city.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('city_id')" />
    </div>
</div>

                            {{-- TOMBOL SIMPAN --}}
                            <div class="flex items-center gap-4 pt-4">
                                <button type="submit"
                                    class="btn-neumorph-primary text-white font-black py-4 px-10 rounded-2xl text-[10px] uppercase tracking-[0.2em]">
                                    {{ __('Simpan Profil') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- SCRIPT ALPINE.JS UNTUK LOKASI (PROVINSI & KOTA) --}}
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.data('locationData', () => ({
                            provinces: [],
                            cities: [],
                            provOpen: false,
                            cityOpen: false,
                            isLoadingCities: false,

                            // Ambil data dari old() saat gagal validasi, atau data database user
                            selectedProvValue: '{{ old('province_id', $user->province_id) }}',
                            selectedProvLabel: '-- Memuat Provinsi... --',

                            selectedCityValue: '{{ old('city_id', $user->city_id) }}',
                            selectedCityLabel: '-- Pilih Provinsi Dahulu --',

                            init() {
                                // Fetch Provinsi dari Emsifa
                                fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
                                    .then(res => res.json())
                                    .then(data => {
                                        this.provinces = data;
                                        this.selectedProvLabel = '-- Pilih Provinsi --';

                                        // Jika sudah ada provinsi tersimpan, set label & fetch kota
                                        if (this.selectedProvValue) {
                                            let found = this.provinces.find(p => p.id == this
                                            .selectedProvValue);
                                            if (found) this.selectedProvLabel = found.name;
                                            this.fetchCities(this.selectedProvValue, this.selectedCityValue);
                                        }
                                    });
                            },

                            selectProvince(prov) {
                                this.selectedProvValue = prov.id;
                                this.selectedProvLabel = prov.name;
                                this.provOpen = false;

                                // Reset kota
                                this.selectedCityValue = '';
                                this.selectedCityLabel = '-- Pilih Kota / Kabupaten --';

                                this.fetchCities(prov.id);
                            },

                            fetchCities(provId, presetCityId = null) {
                                this.isLoadingCities = true;
                                this.cities = [];
                                this.selectedCityLabel = 'Memuat Kota...';

                                fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`)
                                    .then(res => res.json())
                                    .then(data => {
                                        this.cities = data;
                                        this.isLoadingCities = false;
                                        this.selectedCityLabel = '-- Pilih Kota / Kabupaten --';

                                        if (presetCityId) {
                                            let found = this.cities.find(c => c.id == presetCityId);
                                            if (found) this.selectedCityLabel = found.name;
                                        }
                                    });
                            },

                            selectCity(city) {
                                this.selectedCityValue = city.id;
                                this.selectedCityLabel = city.name;
                                this.cityOpen = false;
                            }
                        }));
                    });
                </script>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                {{-- ================================================= --}}
                {{-- BAGIAN 2: LINKED ACCOUNTS (SOSMED) --}}
                {{-- ================================================= --}}
                {{-- CARD: LINKED ACCOUNTS --}}
                <div class="card-neumorphism p-10 rounded-[3rem] border-none relative overflow-hidden">
                    {{-- Header Section --}}
                    <div class="flex items-center gap-4 mb-10 pb-2">
                        <div
                            class="w-12 h-12 card-inset-panel flex items-center justify-center rounded-2xl text-pink-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest leading-none">
                                Linked Accounts</h2>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight mt-1">Verifikasi
                                Video Clip Otomatis</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" class="space-y-8">
                        @csrf
                        @method('patch')

                        {{-- Hidden Input Wajib --}}
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        <input type="hidden" name="username" value="{{ $user->username }}">
                        <input type="hidden" name="phone_number" value="{{ $user->phone_number }}">

                        {{-- TikTok --}}
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">TikTok
                                Username</label>
                            <div class="relative group">
                                <div
                                    class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-400 group-focus-within:text-gray-900 transition-colors">
                                    <i class="fab fa-tiktok text-sm"></i>
                                </div>
                                <input type="text" name="social_tiktok"
                                    value="{{ old('social_tiktok', $getSosmed->tiktok ?? '') }}"
                                    class="input-neumorph-inset w-full pl-12 pr-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                    placeholder="@username">
                            </div>
                        </div>

                        {{-- Instagram --}}
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">Instagram
                                Username</label>
                            <div class="relative group">
                                <div
                                    class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-400 group-focus-within:text-pink-500 transition-colors">
                                    <i class="fab fa-instagram text-sm"></i>
                                </div>
                                <input type="text" name="social_instagram"
                                    value="{{ old('social_instagram', $getSosmed->instagram ?? '') }}"
                                    class="input-neumorph-inset w-full pl-12 pr-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                    placeholder="@username">
                            </div>
                        </div>

                        {{-- YouTube --}}
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">YouTube
                                Channel</label>
                            <div class="relative group">
                                <div
                                    class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                    <i class="fab fa-youtube text-sm"></i>
                                </div>
                                {{-- PERBAIKAN: Variabel sudah diarahkan ke youtube --}}
                                <input type="text" name="social_youtube"
                                    value="{{ old('social_youtube', $getSosmed->youtube ?? '') }}"
                                    class="input-neumorph-inset w-full pl-12 pr-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                    placeholder="Channel Name / URL">
                            </div>
                        </div>

                        {{-- TOMBOL SIMPAN --}}
                        <div class="pt-4">
                            <button type="submit"
                                class="btn-neumorph-primary text-white font-black py-4 px-10 rounded-2xl text-[10px] uppercase tracking-[0.2em]">
                                Simpan Konfigurasi Sosmed
                            </button>
                        </div>
                    </form>
                </div>

                {{-- ================================================= --}}
                {{-- BAGIAN 3: METODE PEMBAYARAN --}}
                {{-- ================================================= --}}
                {{-- CARD: METODE PEMBAYARAN --}}
                {{-- CARD: METODE PEMBAYARAN (PREMIUM DROPDOWN VERSION) --}}
                <div class="card-neumorphism p-10 rounded-[3rem] border-none relative overflow-hidden">
                    {{-- Header Section --}}
                    <div class="flex items-center gap-4 mb-10 pb-2">
                        <div
                            class="w-12 h-12 card-inset-panel flex items-center justify-center rounded-2xl text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest leading-none">
                                Metode Pembayaran</h2>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight mt-1">Rekening
                                Pencairan Dana (Withdraw)</p>
                        </div>
                    </div>

                    @php
                        // Definisi Logo Bank agar konsisten dengan halaman index
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
                        $currentBank = old('bank_name', $payout?->bank_name);
                    @endphp

                    <form method="post" action="{{ route('profile.update') }}" class="space-y-8">
                        @csrf
                        @method('patch')

                        {{-- Hidden Input Wajib --}}
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        <input type="hidden" name="username" value="{{ $user->username }}">
                        <input type="hidden" name="phone_number" value="{{ $user->phone_number }}">

                        {{-- CUSTOM DROPDOWN BANK / E-WALLET (LOGIKA ALPINE.JS DARI INDEX) --}}
                        <div class="space-y-3" x-data="{
                            open: false,
                            selectedLabel: '{{ $currentBank ?: '-- Pilih Bank --' }}',
                            selectedValue: '{{ $currentBank ?: '' }}',
                            selectedLogo: '{{ $currentBank ? $bankLogos[$currentBank] ?? '' : '' }}'
                        }">
                            <label class="block text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] ml-2">
                                Bank / E-Wallet
                            </label>

                            <div class="relative">
                                {{-- TOMBOL TRIGGER (Taktil/Embossed) --}}
                                <button type="button" @click="open = !open"
                                    class="btn-neumorph-primary w-full p-3 flex items-center rounded-2xl justify-between outline-none group transition-all duration-300">

                                    <div class="flex items-center gap-4">
                                        {{-- LOGO CONTAINER (Cekung/Inset) --}}
                                        <div x-show="selectedLogo"
                                            class="w-10 h-10 card-timbul p-1.5 flex items-center justify-center overflow-hidden">
                                            <img :src="selectedLogo"
                                                class="max-w-full max-h-full object-contain transition-all duration-500">
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
                                                        @click="selectedLabel = '{{ $bank }}'; selectedValue = '{{ $bank }}'; selectedLogo = '{{ $bankLogos[$bank] }}'; open = false"
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
                                                        @click="selectedLabel = '{{ $wallet }}'; selectedValue = '{{ $wallet }}'; selectedLogo = '{{ $bankLogos[$wallet] }}'; open = false"
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

                        {{-- Account Number --}}
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">Nomor
                                Rekening / HP</label>
                            <input type="text" name="account_number"
                                value="{{ old('account_number', $payout?->account_number) }}"
                                class="input-neumorph-inset w-full px-6 py-4 text-sm font-mono font-bold text-gray-700 outline-none transition-all"
                                placeholder="Contoh: 08123xxx atau 1234567890">
                        </div>

                        {{-- Account Name --}}
                        <div class="space-y-2">
                            <label
                                class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">Atas
                                Nama</label>
                            <input type="text" name="account_name"
                                value="{{ old('account_name', $payout?->account_name) }}"
                                class="input-neumorph-inset w-full px-6 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                placeholder="Nama sesuai buku tabungan / aplikasi">
                        </div>

                        {{-- TOMBOL SIMPAN --}}
                        <div class="pt-4">
                            <button type="submit"
                                class="btn-neumorph-primary text-white font-black py-4 px-10 rounded-2xl text-[10px] uppercase tracking-[0.2em]">
                                Simpan Data Bank
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            {{-- ================================================= --}}
            {{-- BAGIAN 4: KEAMANAN AKUN (PASSWORD) --}}
            {{-- ================================================= --}}
            {{-- CARD GABUNGAN: SECURITY COMMAND CENTER (SIDE-BY-SIDE VERSION) --}}
            <div class="card-neumorphism p-10 rounded-[3rem] border-none relative overflow-hidden mt-8">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

                    {{-- KOLOM KIRI: UPDATE PASSWORD --}}
                    <div class="space-y-8">
                        <div class="flex items-center gap-4 mb-2">
                            <div
                                class="w-12 h-12 card-inset-panel flex items-center justify-center rounded-2xl text-amber-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-gray-800 uppercase tracking-widest leading-none">
                                    Password Update</h2>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tight mt-1">Ganti Kunci
                                    Akses</p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                            @csrf
                            @method('put')

                            @if (!Auth::user()->google_id)
                                <div class="space-y-2" x-data="{ show: false }">
                                    <label
                                        class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                        Password Saat Ini
                                    </label>

                                    <div class="relative group">
                                        {{-- Input Field --}}
                                        <input :type="show ? 'text' : 'password'" name="current_password"
                                            class="input-neumorph-inset w-full pl-6 pr-12 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                            placeholder="••••••••" autocomplete="current-password" />

                                        {{-- Tombol Toggle Mata --}}
                                        <button type="button" @click="show = !show"
                                            class="absolute inset-y-0 right-0 pr-6 flex items-center text-gray-400 hover:text-amber-500 transition-colors focus:outline-none">

                                            {{-- Logika Ikon --}}
                                            <i class="fas text-xs" :class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                                        </button>
                                    </div>

                                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 ml-2" />
                                </div>
                            @endif
                            <div class="space-y-2" x-data="{ show: false }">
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                    Password Baru
                                </label>

                                <div class="relative group">
                                    {{-- Input Field --}}
                                    <input :type="show ? 'text' : 'password'" name="password"
                                        class="input-neumorph-inset w-full pl-6 pr-12 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                        placeholder="Min. 8 Karakter" />

                                    {{-- Tombol Mata --}}
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 pr-6 flex items-center text-gray-400 hover:text-indigo-500 transition-colors focus:outline-none">

                                        {{-- Icon Mata Terbuka (Show) --}}
                                        <template x-if="!show">
                                            <i class="fas fa-eye-slash text-xs"></i>
                                        </template>

                                        {{-- Icon Mata Tertutup (Hide) --}}
                                        <template x-if="show">
                                            <i class="fas fa-eye text-xs"></i>
                                        </template>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-2" x-data="{ show: false }">
                                <label
                                    class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-2">
                                    {{ __('Konfirmasi Password') }}
                                </label>

                                <div class="relative group">
                                    {{-- Tipe input berubah dinamis tergantung state 'show' --}}
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation"
                                        class="input-neumorph-inset w-full pl-6 pr-12 py-4 text-sm font-bold text-gray-700 outline-none transition-all"
                                        placeholder="Ulangi Password" />

                                    {{-- Tombol Toggle --}}
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 pr-6 flex items-center text-gray-400 hover:text-indigo-500 transition-colors focus:outline-none">

                                        <i class="fas text-xs" :class="show ? 'fa-eye' : 'fa-eye-slash'"></i>
                                    </button>
                                </div>
                            </div>

                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />

                            <button type="submit"
                                class="btn-neumorph-primary text-white font-black py-4 px-10 rounded-2xl text-[10px] uppercase tracking-[0.2em]">
                                Simpan Password Baru
                            </button>
                        </form>
                    </div>

                    {{-- KOLOM KANAN: DANGER ZONE --}}
                    <div class="lg:border-l lg:border-gray-200/50 lg:pl-12 space-y-8">
                        <div class="flex items-center gap-4 mb-2">
                            <div
                                class="w-12 h-12 card-inset-panel flex items-center justify-center rounded-2xl text-rose-500">
                                <i class="fas fa-user-slash text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-rose-600 uppercase tracking-widest leading-none">
                                    Danger Zone</h2>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tight mt-1">
                                    Penghapusan Akun</p>
                            </div>
                        </div>

                        <div
                            class="p-8 bg-rose-50/50 rounded-[2.5rem] border border-rose-100/50 relative overflow-hidden group">
                            {{-- Background Icon Decoration --}}
                            <i
                                class="fas fa-exclamation-triangle absolute -right-4 -bottom-4 text-rose-100 text-8xl opacity-20 group-hover:rotate-12 transition-transform duration-500"></i>

                            <div class="relative z-10">
                                <h3 class="text-[11px] font-black text-rose-700 uppercase tracking-widest mb-4">
                                    Peringatan Kritis</h3>
                                <p
                                    class="text-[11px] font-bold text-rose-600/70 uppercase leading-relaxed mb-8 italic">
                                    {{ __('Menghapus akun akan melenyapkan semua data reward, riwayat clip, dan pengaturan secara permanen. Tindakan ini tidak bisa dibatalkan.') }}
                                </p>

                                <button x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                                    class="w-full bg-white text-rose-600 border border-rose-200 font-black py-4 rounded-2xl shadow-sm transition hover:bg-rose-600 hover:text-white active:scale-95 text-[10px] uppercase tracking-[0.2em]">
                                    {{ __('Hapus Akun Permanen') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-inset-panel p-6 rounded-2xl">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-loose">
                                <i class="fas fa-info-circle mr-2 text-indigo-400"></i>
                                Pastikan Anda telah mencairkan semua sisa saldo (Withdraw) sebelum melakukan penghapusan
                                akun.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- MODAL KONFIRMASI (SAMA SEPERTI SEBELUMNYA) --}}
                <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                    <form method="post" action="{{ route('profile.destroy') }}"
                        class="p-10 bg-[#f3f4f6] rounded-[3rem]">
                        @csrf
                        @method('delete')
                        <h2 class="text-xl font-black text-gray-900 uppercase italic tracking-tighter mb-4">Verifikasi
                            Hapus Akun</h2>
                        <p class="text-[10px] font-bold text-gray-500 uppercase leading-relaxed mb-8">Masukkan password
                            untuk konfirmasi final penghapusan akun ClipSquad Anda.</p>
                        <div class="space-y-2">
                            <input id="password" name="password" type="password"
                                class="input-neumorph-inset w-full px-6 py-4 text-sm font-bold text-gray-700 outline-none"
                                placeholder="Masukkan Password Anda..." />
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                        </div>
                        <div class="mt-10 flex gap-4">
                            <button type="button" x-on:click="$dispatch('close')"
                                class="flex-1 py-4 card-neumorphism rounded-2xl text-[10px] font-black uppercase text-gray-400">Batal</button>
                            <button type="submit"
                                class="flex-[2] py-4 bg-rose-600 text-white rounded-2xl text-[10px] font-black uppercase shadow-lg shadow-rose-200 hover:bg-rose-700">Konfirmasi
                                Hapus 🚮</button>
                        </div>
                    </form>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>
