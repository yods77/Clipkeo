<x-app-layout>
    {{-- HEADER BAWAAN DIHAPUS --}}
    
    <div class="pt-32 pb-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-4">
            
            {{-- HEADER SECTION --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Manajemen User</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola data pengguna, role, dan status akun.</p>
                </div>
                
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            {{-- CONTAINER TABEL --}}
            <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-lg shadow-gray-500/50">
                
                {{-- TABLE HEADER DECORATION --}}
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <h3 class="font-bold text-gray-800">Daftar Pengguna Aktif</h3>
                    </div>
                    
                    {{-- Search / Filter bisa ditambahkan disini nanti --}}
                    <span class="text-xs font-bold px-3 py-1 bg-white border border-gray-200 rounded-full text-gray-500">
                        Total: {{ $users->total() }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-gray-400 uppercase text-[10px] tracking-wider font-bold border-b border-gray-100">
                            <tr>
                                <th class="px-8 py-4">User Info</th>
                                <th class="px-8 py-4">Role</th>
                                <th class="px-8 py-4">Email</th>
                                <th class="px-8 py-4">Saldo Dompet</th>
                                <th class="px-8 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50 transition duration-150 group">
                                    {{-- USER INFO --}}
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-4">
                                            
                                            {{-- [UPDATE] LOGIKA AVATAR PINTAR (GOOGLE VS LOKAL) --}}
                                            <img class="w-10 h-10 rounded-full object-cover border border-white shadow-sm"
                                                 src="{{ $user->avatar_url ? (str_starts_with($user->avatar_url, 'http') ? $user->avatar_url : asset('storage/' . $user->avatar_url)) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&color=fff' }}"
                                                 alt="{{ $user->name }}">
                                            
                                            <div>
                                                <div class="font-bold text-gray-900 text-sm group-hover:text-indigo-600 transition">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-400 font-medium">@ {{ $user->username }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- ROLE BADGE --}}
                                    <td class="px-8 py-5">
                                        @if($user->role == 'publisher')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100 uppercase tracking-wide">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                Publisher
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 uppercase tracking-wide">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                Clipper
                                            </span>
                                        @endif
                                    </td>

                                    {{-- EMAIL --}}
                                    <td class="px-8 py-5 text-gray-500 font-medium">
                                        {{ $user->email }}
                                    </td>

                                    {{-- SALDO --}}
                                    <td class="px-8 py-5 font-mono font-bold text-emerald-600">
                                        Rp {{ number_format($user->wallet_balance, 0, ',', '.') }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="px-8 py-5 text-right">
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mem-banned user ini? Mereka tidak akan bisa login lagi.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white hover:bg-rose-50 text-rose-500 hover:text-rose-600 border border-rose-100 rounded-xl transition shadow-sm text-[10px] font-bold uppercase tracking-wider group/btn">
                                                <svg class="w-3 h-3 group-hover/btn:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                Ban User
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-900">Tidak Ada User Lain</h3>
                                            <p class="text-gray-500 text-sm mt-1">Saat ini hanya Anda yang terdaftar di sistem.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION (Light) --}}
                <div class="px-8 py-6 border-t border-gray-100 bg-gray-50">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>