<x-app-layout>
    {{-- Background Dashboard Otomatis --}}
    <div class="pt-32 pb-12 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 px-4">
            
            {{-- HEADER & BACK BUTTON --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h2 class="text-3xl font-black text-gray-800 tracking-tighter">Hubungkan Sosmed</h2>
                    <p class="text-[10px] text-gray-400 uppercase font-black tracking-[0.2em] mt-1">Verifikasi Identitas Creator Anda</p>
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

            {{-- MAIN CONTENT CARD (Embossed) --}}
            <div class="card-neumorphism p-8 md:p-12 relative overflow-hidden">
                {{-- Dekorasi Glow Halus --}}
                <div class="absolute -top-24 -left-24 w-64 h-64 bg-emerald-500/15 rounded-full blur-3xl"></div>

                <div class="relative z-10 mb-12 flex flex-col items-center text-center">
                    <div class="w-20 h-20 card-inset-panel flex items-center justify-center mb-6 text-emerald-500">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-gray-800 tracking-tighter">Kelola Identitas Creator</h3>
                    <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-2 max-w-sm leading-relaxed">
                        Verifikasi akun sosial mediamu agar Publisher bisa memvalidasi karya yang kamu buat.
                    </p>
                </div>

                {{-- LOOP PROVIDER --}}
                <div class="space-y-16">
                    @foreach (['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube'] as $key => $label)
                        <div class="relative">
                            <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.3em] mb-6 flex items-center gap-4">
                                {{-- Icon Provider --}}
                                @if ($key == 'instagram')
                                    <div class="w-10 h-10 card-inset-panel flex items-center justify-center text-pink-500">
                                        <i class="fab fa-instagram text-lg"></i>
                                    </div>
                                @elseif($key == 'tiktok')
                                    <div class="w-10 h-10 card-inset-panel flex items-center justify-center text-gray-800">
                                        <i class="fab fa-tiktok text-lg"></i>
                                    </div>
                                @else
                                    <div class="w-10 h-10 card-inset-panel flex items-center justify-center text-red-600">
                                        <i class="fab fa-youtube text-lg"></i>
                                    </div>
                                @endif
                                Akun {{ $label }}
                            </h4>

                            {{-- LIST AKUN TERHUBUNG (Inset Cards) --}}
                            <div class="grid grid-cols-1 gap-4 mb-6">
                                @foreach ($groupedAccounts[$key] as $account)
                                    <div class="card-inset-panel p-5 flex items-center justify-between group/acc transition-all">
                                        <div class="flex items-center gap-5">
                                            {{-- Avatar Placeholder (Embossed inside Inset) --}}

                                            <div>
                                                <div class="text-gray-800 font-black text-sm tracking-tight">@ {{ $account->username }}</div>
                                                <div class="text-[9px] font-black uppercase tracking-widest mt-1 flex items-center gap-1.5 {{ $account->verified_at ? 'text-emerald-500' : 'text-amber-500' }}">
                                                    @if($account->verified_at)
                                                        <i class="fas fa-check-circle"></i> Terverifikasi
                                                    @else
                                                        <i class="fas fa-clock"></i> Menunggu Verifikasi
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <form action="{{ route('linked-accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-10 h-10 btn-neumorph-primary flex items-center justify-center text-gray-400 hover:text-rose-500 transition-all rounded-xl">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>

                            {{-- FORM TAMBAH AKUN (Inset Input + Indigo Button) --}}
                            <form action="{{ route('linked-accounts.store') }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                                @csrf
                                <input type="hidden" name="provider_name" value="{{ $key }}">

                                <div class="relative flex-1 group">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-black text-sm transition group-focus-within:text-gray-500">@</span>
                                    </div>
                                    <input type="text" name="username" required
                                        class="input-neumorph-inset w-full pl-10 pr-5 py-4 text-xs font-black uppercase tracking-widest text-gray-700 placeholder-gray-300 outline-none"
                                        placeholder="Username {{ $label }}">
                                </div>

                                <button type="submit"
                                    class="btn-neumorph-primary px-8 py-4 bg-gradient-to-br from-[#f3f4f6] to-green-50 text-emerald-600 font-black text-[10px] uppercase tracking-[0.2em] rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- FOOTER INFO --}}
            <div class="mt-10 px-4 text-center">
                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-relaxed">
                    <i class="fas fa-info-circle text-emerald-400 mr-1"></i> 
                    Pastikan akun sosial media Anda tidak dalam mode private saat proses verifikasi.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>