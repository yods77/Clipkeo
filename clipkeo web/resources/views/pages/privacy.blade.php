<x-app-layout>
    <div class="pt-32 pb-20 bg-gray-100 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-[2rem] p-8 md:p-12 shadow-xl shadow-gray-200 border border-gray-100">
                <div class="border-b border-gray-100 pb-8 mb-8">
                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-2">Kebijakan Privasi</h1>
                    <p class="text-gray-500 text-sm">Terakhir diperbarui: {{ date('d F Y') }}</p>
                </div>

                <div class="prose prose-indigo prose-lg text-gray-600 max-w-none">
                    <p>Selamat datang di Clipkeo. Kami menghargai privasi Anda dan berkomitmen untuk melindungi data pribadi Anda. Dokumen ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda.</p>

                    <h3 class="text-gray-900 font-bold mt-8 mb-4">1. Informasi yang Kami Kumpulkan</h3>
                    <p>Kami mengumpulkan informasi yang Anda berikan secara langsung kepada kami, seperti saat Anda membuat akun, memperbarui profil, atau berkomunikasi dengan kami. Jenis informasi ini dapat mencakup:</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Nama lengkap dan alamat email.</li>
                        <li>Informasi akun media sosial (untuk verifikasi Clipper).</li>
                        <li>Informasi pembayaran (untuk penarikan dana).</li>
                    </ul>

                    <h3 class="text-gray-900 font-bold mt-8 mb-4">2. Penggunaan Informasi</h3>
                    <p>Kami menggunakan informasi yang kami kumpulkan untuk:</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Menyediakan, memelihara, dan meningkatkan layanan kami.</li>
                        <li>Memproses transaksi dan mengirimkan notifikasi terkait.</li>
                        <li>Mendeteksi dan mencegah penipuan (fraud detection).</li>
                    </ul>

                    <h3 class="text-gray-900 font-bold mt-8 mb-4">3. Keamanan Data</h3>
                    <p>Kami menerapkan langkah-langkah keamanan teknis dan organisasi yang sesuai untuk melindungi data pribadi Anda dari akses, penggunaan, atau pengungkapan yang tidak sah.</p>

                    <h3 class="text-gray-900 font-bold mt-8 mb-4">4. Hubungi Kami</h3>
                    <p>Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami melalui halaman <a href="{{ route('pages.contact') }}" class="text-indigo-600 font-bold hover:underline">Kontak Kami</a>.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>