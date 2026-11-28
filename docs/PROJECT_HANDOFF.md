# Clipkeo — Project Handoff & Status

> Dokumen ini adalah catatan induk untuk melanjutkan pengembangan dan pengujian Clipkeo lintas percakapan. Dokumen ini menyimpan posisi teknis terakhir. Untuk visi produk, evaluasi UX, rekomendasi fitur, prioritas skala, dan cara AI memberi saran proaktif, baca juga `docs/PRODUCT_ROADMAP.md`. Perbarui keduanya saat ada perubahan kode, keputusan produk, atau hasil pengujian baru.
>
> **Terakhir diperbarui:** 2026-11-28  
> **Repository:** https://github.com/yods77/Clipkeo  
> **Branch kerja:** `fix/security-authentication`  
> **Aturan penting:** jangan merge ke `main` tanpa instruksi eksplisit dari pemilik proyek.

---

## 1. Gambaran proyek

Repository merupakan monorepo yang berisi:
- **Flutter mobile app:** folder `clipkeo app/`
- **Laravel web/backend:** folder `clipkeo web/`

Fokus pekerjaan yang tercatat dalam sesi ini adalah menstabilkan autentikasi, skema database, alur Publisher/Admin, campaign, wallet, refund, dan pembayaran hadiah Clipper.

## 2. Lingkungan pengembangan lokal

| Komponen | Nilai terakhir yang dilaporkan |
|---|---|
| OS | Windows |
| Project lokal | `E:\\Clipkeo` |
| Laravel folder | `E:\\Clipkeo\\clipkeo web` |
| PHP | 8.3.37 (Laragon) |
| MySQL | 8.4.3 |
| Database | `clipsquad_db` |
| DB host/port | `127.0.0.1:3306` |
| PHP CLI | Sudah ditambahkan ke PATH; `php artisan ...` berhasil |

Jangan mencatat isi `.env`, password database, password akun, PIN, token, private key, atau secret ke dokumen ini maupun Git.

## 3. Aturan Git dan status sinkronisasi

- Seluruh pengembangan dan pengujian dilakukan di branch `fix/security-authentication`.
- `main` harus tetap stabil dan **tidak boleh di-merge** tanpa persetujuan eksplisit.
- Pada pemeriksaan terminal 2026-11-27, `git fetch origin` menunjukkan branch lokal bergerak dari `8786709` dan `origin/fix/security-authentication` maju ke `36d668e`; `git status -sb` menunjukkan lokal **behind 1 commit**. Verifikasi SHA aktual dengan `git log -1 --oneline` sebelum sinkronisasi.
- Sebelumnya pernah terlihat perubahan lokal belum di-commit pada `clipkeo app/lib/config.dart`. Periksa `git status --short` sebelum pull, reset, checkout, atau perubahan massal. Jangan menghapus perubahan lokal yang belum diperiksa.
- Snapshot terminal terbaru menunjukkan tiga file lokal berubah dan belum di-commit: `clipkeo app/lib/config.dart`, `clipkeo web/app/Http/Controllers/AdminBountyController.php`, dan `clipkeo web/app/Http/Controllers/DashboardController.php`.
- Ketiga diff lokal sudah diperiksa:
  - `clipkeo app/lib/config.dart`: IP LAN berubah dari `192.168.1.102` menjadi `192.168.18.3`. Ini konfigurasi mesin/jaringan lokal; jangan commit sebagai konfigurasi universal. Rekomendasikan `--dart-define` atau environment build untuk dev/staging/production.
  - `AdminBountyController.php`: tipe transaksi refund penolakan Admin berubah dari `refund` menjadi `bounty_refund`, sesuai ENUM database. Perubahan masih lokal; file remote pada branch acuan masih memakai `refund`.
  - `DashboardController.php`: perhitungan `$lockedFunds` berubah dari status `active, completed` menjadi `pending_review, active`. Ini lebih tepat untuk dana campaign yang masih di-hold, tetapi perlu menuntaskan semantik refund/remaining_budget.
- Jangan pull/merge/rebase/reset/restore sebelum ketiga diff diamankan. Setelah aman, sinkronkan commit dokumentasi remote tanpa menimpa perubahan lokal.

Perintah awal setiap sesi kerja:

```powershell
git branch --show-current
git status --short
git log -1 --oneline
git fetch origin
git status -sb
```

Jangan menjalankan `git reset --hard`, `git clean`, atau `git restore` pada perubahan yang belum diperiksa.

## 4. Riwayat autentikasi dan perbaikan database

### Google Sign-In dan Sanctum
- Aplikasi Android berhasil di-install dan awalnya menampilkan halaman login.
- Google Login sempat gagal dengan pesan: “Google Sign-in tidak dapat diverifikasi saat ini. Silahkan coba lagi.”
- Log Laravel menunjukkan beberapa jenis exception, termasuk `Firebase\\JWT\\BeforeValidException` dan `Illuminate\\Database\\QueryException`.
- Waktu perangkat HP dan laptop diperiksa dan sudah hampir sinkron.
- Koneksi Laravel dipastikan menggunakan database `clipsquad_db`.
- Ditemukan bahwa tabel `personal_access_tokens` belum ada di database aktif meskipun migration tercatat pending.
- Database dibackup, kemudian migration Sanctum dijalankan secara terarah:
  `2026_01_24_114034_create_personal_access_tokens_table.php`.
- Setelah tabel tersedia, Google Login berhasil dan sesi login bertahan saat aplikasi dibuka kembali.

### Migration dan seeder
- Status migration diperiksa dan SQL dari migration pending ditinjau menggunakan `php artisan migrate --pretend`.
- Backup database dibuat sebelum migration penuh.
- Migration yang tertunda kemudian tercatat sudah berjalan, termasuk kolom reward, Xendit, email verification, security PIN, FCM token, linked account, region, dan unique reference transaksi.
- Seeder wilayah awalnya menghasilkan 34 provinsi dan 514 kabupaten/kota.
- Branch diperbarui dengan RegionSeeder untuk 38 provinsi. Seeder dijalankan kembali setelah backup.
- Validasi terakhir: 38 provinsi, 514 kabupaten/kota, 0 orphan regencies, dan tidak ditemukan referensi wilayah pengguna yang invalid. Enam provinsi Papua dengan ID 91–96 telah terdaftar.

### Backup yang tercatat
- `C:\\laragon\\tmp\\clipsquad_db_20261126_234322.sql`
- `C:\\laragon\\tmp\\clipsquad_db_before_full_migration_20261127_082508.sql`
- `C:\\laragon\\tmp\\clipsquad_db_before_region_update_20261127_092033.sql`

File backup berada di mesin lokal pengguna, bukan di repository.

## 5. Pengujian aplikasi yang sudah dilakukan

### Mobile app / Clipper
- Instalasi aplikasi: berhasil.
- Google Login: berhasil setelah migration Sanctum.
- Dashboard: berhasil dibuka.
- Persistensi login: berhasil; dashboard tetap terbuka tanpa login ulang.
- Profile: informasi tampil dengan benar.
- Wallet: saldo Rp0 dan riwayat kosong pada saat itu, sesuai karena belum ada transaksi.
- Submissions: halaman dan tab Pending, Approved, Rejected berhasil dibuka.
- Pull-to-refresh belum dapat divalidasi karena daftar belum memiliki data.
- Tidak ada error atau loading tanpa akhir pada halaman Submissions.
- Search Campaign: halaman dan kategori berhasil; daftar campaign kosong karena belum ada campaign aktif. API mengembalikan `{"status":"success","data":[],"meta":{"current_page":1,"last_page":1,"total":0}}`.
- Filter, pencarian, hapus pencarian, dan detail campaign belum tervalidasi saat daftar kosong.

### Test suite Laravel
Perintah `php artisan test` terakhir yang dilaporkan menghasilkan:
- **42 tests passed**
- **125 assertions**
- Termasuk pengujian authentication/security, wallet authorization, registration, profile, dan submission API.

Hasil ini adalah baseline sebelum perbaikan transaksi berikutnya. Jalankan ulang seluruh test suite setelah perubahan kode.

## 6. Akun dan data pengujian lokal

Data berikut dibuat langsung di database lokal menggunakan Tinker. Ini bukan akun produksi.

| Peran | User ID | Identitas |
|---|---:|---|
| Clipper | 1 | Akun Google yang digunakan saat login awal |
| Publisher | 2 | `publisher-test-uxvsyaiw@example.test` |
| Admin | 3 | `admin-test-a20ejz7u@example.test` |

Password dan PIN tidak disimpan di dokumen ini. Jangan meng-commit kredensial pengujian.

### Campaign uji
- Campaign ID: 1
- Judul: `Campaign Uji Validasi`
- Publisher ID: 2
- Anggaran/reward awal: Rp1.000.000
- Status awal: `pending_review`
- Transaksi hold: ID 1, tipe `bounty_hold`, Rp1.000.000, status `success`
- Saldo Publisher setelah hold: Rp0

Publisher berhasil membuat campaign melalui website. Validasi field wajib berjalan dan campaign berhasil disimpan. Admin berhasil login dan membuka halaman Admin.

## 7. Insiden refund Admin dan hasil perbaikannya

Saat Admin memilih **Tolak & Refund**, Laravel sempat gagal dengan SQLSTATE warning 1265 karena kode menyimpan tipe `refund`, sementara kolom `transactions.type` menggunakan ENUM yang tidak mengizinkannya.

Definisi ENUM yang diperiksa dari database:

```text
deposit
withdrawal
bounty_hold
bounty_prize
bounty_refund
platform_fee
```

Kode lokal pada alur refund Admin kemudian disesuaikan menjadi `bounty_refund`. Pengujian ulang berhasil:
- Campaign ID 1 berubah menjadi `rejected`.
- Saldo Publisher ID 2 menjadi Rp1.000.000.
- Satu transaksi refund tercatat: transaction ID 2, tipe `bounty_refund`, nominal Rp1.000.000, status `success`.
- Reference: `REF-ADM-1795754618-1`.

**Jangan mengulangi refund campaign uji ini.** Refund telah berhasil satu kali. Hindari refund ganda.

## 8. Audit tipe transaksi — pekerjaan yang masih terbuka

### ENUM yang diizinkan database

| Tipe | Makna |
|---|---|
| `deposit` | Deposit |
| `withdrawal` | Penarikan |
| `bounty_hold` | Dana campaign ditahan |
| `bounty_prize` | Hadiah campaign |
| `bounty_refund` | Pengembalian dana campaign |
| `platform_fee` | Biaya platform |

### Hasil pencarian lokal terakhir

| File | Baris lokal terakhir | Nilai ditemukan | Status |
|---|---:|---|---|
| `AdminBountyController.php` | 106 | `bounty_refund` | Benar di salinan lokal; sinkronisasi GitHub perlu dicek |
| `AdminBountyController.php` | 319 | `reward` | Tidak cocok ENUM |
| `AdminBountyController.php` | 333 | `payout` | Tidak cocok ENUM |
| `BountyController.php` | 332 | `refund` | Tidak cocok ENUM |
| `SubmissionController.php` | 484 | `reward` | Tidak cocok ENUM |
| `SubmissionController.php` | 496 | `payout` | Tidak cocok ENUM |

Pemanggilan `deposit`, `withdrawal`, `bounty_hold`, dan `platform_fee` yang ditemukan dalam controller sudah cocok dengan ENUM.

### Aturan pemetaan yang perlu diterapkan dengan hati-hati
- `refund` diarahkan menjadi `bounty_refund`.
- `reward` dan `payout` berhubungan dengan satu pembayaran hadiah campaign, tetapi dicatat pada dua akun berbeda: Clipper menerima uang dan Publisher mencatat biaya hadiah. Keduanya berpotensi menggunakan tipe ENUM `bounty_prize`, **namun tampilan tanda plus/minus wajib dibedakan berdasarkan pemilik transaksi/role**.
- Jangan melakukan penggantian massal sebelum logika saldo, filter wallet, riwayat transaksi, dan pengujian diperiksa.

### File wallet yang juga perlu diperbaiki
`resources/views/wallet/index.blade.php` pada salinan branch yang diperiksa masih memakai nilai filter dan badge lama:
- `reward`
- `payout`
- `refund`

Perlu audit juga query filter, tampilan nominal masuk/keluar, label badge, dan tampilan transaksi Admin. Pastikan:
- Clipper: `bounty_prize` tampil sebagai pemasukan.
- Publisher: `bounty_prize` tampil sebagai pengeluaran.
- Publisher: `bounty_refund` tampil sebagai pemasukan.
- Publisher: `bounty_hold` dan `platform_fee` tampil sebagai pengeluaran.
- Admin: arah transaksi dibedakan berdasarkan pemilik transaksi dan makna transaksi, bukan hanya nama tipe.

## 9. Urutan pekerjaan berikutnya

1. **Amankan working tree:** periksa `git diff -- app/Http/Controllers/AdminBountyController.php`, `git diff -- app/Http/Controllers/DashboardController.php`, dan `git diff -- "../clipkeo app/lib/config.dart"`. Pahami ketiga perubahan dan simpan aman (commit terpilah atau patch/stash yang sudah diverifikasi) sebelum sinkronisasi remote. Jangan menimpa perubahan lokal.
2. **Audit seluruh pemakaian tipe:** cari di seluruh folder aplikasi, bukan hanya `app/`; periksa controller, service, model, query wallet, Blade, API, Flutter wallet, dan test.
3. **Perbaiki refund Publisher:** `BountyController.php` agar menggunakan `bounty_refund`.
4. **Standarisasi hadiah:** sesuaikan pencatatan Clipper dan Publisher pada `SubmissionController.php` dan `AdminBountyController.php` dengan `bounty_prize`, setelah memastikan arah saldo dan ledger benar.
5. **Perbaiki wallet:** filter, label, badge, warna, tanda nominal, dan query agar cocok dengan ENUM baru.
6. **Tambah/perbarui automated tests:** refund pembatalan campaign, pembayaran hadiah normal, Admin Takeover, isolasi saldo antar pengguna, dan tampilan/filter transaksi.
7. Jalankan `php artisan test`, lint file PHP yang berubah, lalu uji alur end-to-end dengan data pengujian baru yang tidak menyebabkan refund/payout ganda.
8. Periksa `git diff`, commit hanya file yang relevan ke `fix/security-authentication`, dan jangan merge ke `main`.

## 10. Strategi produk dan pengembangan jangka panjang

Dokumen strategi terpisah tersedia di [`docs/PRODUCT_ROADMAP.md`](https://github.com/yods77/Clipkeo/blob/fix/security-authentication/docs/PRODUCT_ROADMAP.md). Dokumen tersebut memuat peta fitur Flutter/Laravel yang terlihat, temuan audit awal, journey Clipper/Publisher/Admin, rekomendasi UI/UX, admin operations, keamanan, integritas finansial, kesiapan skala, metrik produk, serta roadmap prioritas P0–P3.

AI yang melanjutkan proyek harus membaca kedua dokumen: handoff untuk posisi teknis dan roadmap untuk arah produk. Jangan hanya meneruskan bug terakhir; selalu evaluasi prioritas berikutnya berdasarkan dampak pengguna, risiko keuangan/keamanan, efisiensi Admin, dan dependensi teknis. Bedakan fitur yang ada di kode, fitur yang sudah diuji, dan ide yang masih berupa rekomendasi.

## 11. Cara melanjutkan pada percakapan baru

Gunakan instruksi ini:

> Lanjutkan Clipkeo dari repository https://github.com/yods77/Clipkeo. Baca `docs/PROJECT_HANDOFF.md` dan `docs/PRODUCT_ROADMAP.md` pada branch `fix/security-authentication`. Audit branch, commit, dan perubahan lokal terbaru sebelum mengubah kode. Lanjutkan pekerjaan terakhir, tetapi tetap bertindak sebagai partner product engineering: evaluasi UX Clipper, Publisher, Admin, keamanan, keuangan, skalabilitas, dan rekomendasikan prioritas pengembangan berikutnya. Bedakan fitur yang sudah ada, yang sudah diuji, dan yang baru berupa rekomendasi. Jangan merge ke `main`, jangan menghapus perubahan lokal, dan jangan mengubah kebijakan finansial tanpa persetujuan.

Setiap kali ada revisi repository atau hasil pengujian baru:
1. Periksa commit/diff yang berubah.
2. Perbarui bagian status dan pekerjaan berikutnya dalam dokumen ini.
3. Catat hasil test dan verifikasi yang benar-benar dijalankan.
4. Catat SHA commit baru jika sudah tersedia.
5. Jangan mencatat password, PIN, token, atau secret.

**Catatan pembaruan:** dokumen ini tidak otomatis mengetahui hasil pengujian manual di komputer pengguna. Riwayat commit GitHub dapat diperiksa setiap sesi, sedangkan hasil uji lokal perlu dilaporkan pengguna atau disimpan dalam catatan ini setelah diverifikasi.

### Pembaruan 2026-11-27 — dokumentasi produk
- Dibuat `docs/PRODUCT_ROADMAP.md` (commit `d0deb4335f1424f46f4e8e003c0637289e9c6750`) sebagai dokumen arah produk dan rekomendasi pengembangan jangka panjang.
- Commit dokumentasi handoff sebelumnya: `36d668e84a32b77adaf129e1d4dfa58150d39175`.
- Perubahan working tree lokal terbaru dari pengguna: tiga file modified; hanya diff `AdminBountyController.php` yang sudah ditinjau. `config.dart` dan `DashboardController.php` masih menunggu pemeriksaan.
- Roadmap dibuat berdasarkan pembacaan kode branch `fix/security-authentication` pada commit `36d668e`; temuan dan rekomendasi harus diverifikasi ulang terhadap kode lokal terbaru sebelum implementasi.


## 12. Review diff lokal terbaru — 2026-11-27

### A. Konfigurasi Flutter
Diff lokal pada `clipkeo app/lib/config.dart` hanya mengubah IP LAN dari `192.168.1.102` ke `192.168.18.3`. Ini boleh dipakai untuk menjalankan aplikasi pada jaringan laptop saat ini, tetapi jangan dijadikan konfigurasi bersama untuk seluruh pengguna. Rencana perbaikan jangka panjang: pisahkan base URL dev/staging/production menggunakan `--dart-define` atau build flavor, dan pastikan URL production memakai HTTPS/domain publik.

### B. Dashboard escrow
Diff lokal pada `DashboardController.php` mengubah filter dana tertahan dari `['active', 'completed']` menjadi `['pending_review', 'active']`.

Pembacaan kode branch:
- `BountyController::store` memotong saldo Publisher, membuat campaign berstatus `pending_review`, mengisi `remaining_budget`, dan mencatat `bounty_hold`.
- `AdminBountyController::approve` mengubah `pending_review` menjadi `active`.
- `AdminBountyController::reject` mengembalikan `remaining_budget` ke Publisher dan mengubah status menjadi `rejected`, tetapi belum mengosongkan kolom `remaining_budget`.
- `BountyController::destroy` mengembalikan sisa budget dan mengubah status menjadi `completed`, tetapi belum mengosongkan kolom `remaining_budget`. Dengan demikian status `completed` juga dipakai untuk pembatalan/refund, bukan hanya campaign yang anggarannya habis.
- Alur pembayaran menandai campaign `completed` saat sisa budget <= 100.

Kesimpulan: filter baru `pending_review, active` lebih tepat untuk dashboard escrow yang masih tertahan dan menghindari menghitung ulang campaign completed yang mungkin sudah direfund. Namun, perbaikan permanen perlu menyetel `remaining_budget = 0` saat dana direfund dan mempertimbangkan status khusus `cancelled` agar status selesai karena budget habis tidak tercampur dengan pembatalan. Definisikan juga apakah sisa <= Rp100 dianggap dust/residual atau harus direfund.

### C. Tipe transaksi refund/reward
Perubahan lokal `bounty_refund` di AdminBountyController benar menurut ENUM. Audit branch yang dibaca masih menemukan pemakaian lama:
- `BountyController::destroy`: `refund` (perlu `bounty_refund`).
- `SubmissionController`: `reward` dan `payout`.
- `AdminBountyController::takeoverApprove`: `reward` dan `payout`.
- `AdminBountyController::reject` pada remote branch masih `refund`; perubahan lokal pengguna sudah mengubahnya menjadi `bounty_refund`.

Jangan lakukan replace massal. Standarkan berdasarkan ENUM dan makna transaksi, lalu uji arah pemasukan/pengeluaran untuk Clipper dan Publisher, reference ID, dan konsistensi wallet/ledger. Untuk hadiah, gunakan pemetaan yang disetujui setelah memeriksa transaksi di kedua sisi; jangan sekadar mengganti string tanpa menguji dampak laporan.

### D. Sinkronisasi Git
- Pada terminal pengguna, branch lokal terakhir terlihat di `8786709`, sedangkan remote sudah mencapai `36d668e` sebelum dua commit dokumentasi roadmap/handoff ditambahkan.
- Remote branch kini memiliki tiga commit dokumentasi setelah `8786709`: `36d668e`, `d0deb43`, dan `98a92cd`. Tidak ada perubahan kode aplikasi pada ketiga commit tersebut.
- Sebelum pull, simpan patch dan stash hanya tiga file lokal. Lakukan fast-forward, lalu pop stash dan pastikan ketiga perubahan kembali. Jangan commit perubahan IP lokal.
- Hasil test lokal setelah perubahan ini belum dilaporkan; jangan klaim terverifikasi.


### Pembaruan 2026-11-27 — sinkronisasi dan pemulihan perubahan lokal
- Pengguna berhasil membuat patch backup di `%TEMP%\clipkeo-local-20261127.patch` (2.275 byte), yang memuat tepat tiga file: `clipkeo app/lib/config.dart`, `clipkeo web/app/Http/Controllers/AdminBountyController.php`, dan `clipkeo web/app/Http/Controllers/DashboardController.php`.
- Ketiga perubahan disimpan dengan stash `WIP Clipkeo local changes 2026-11-27`.
- `git pull --ff-only origin fix/security-authentication` berhasil fast-forward dari `8786709` ke `de6b9c1`; commit dokumentasi remote tersinkron tanpa perubahan kode aplikasi.
- `git stash pop` berhasil tanpa konflik. Ketiga file kembali modified di working tree dan stash `d01feb9dfddabe2633fa6c3c4e05907e4a6ab7ba` dihapus otomatis setelah penerapan berhasil.
- Status terakhir berdasarkan output terminal pengguna: branch up to date dengan origin; tiga file lokal modified. Belum ada hasil pemeriksaan ulang `git diff --stat` dan nilai isi file setelah pemulihan. Minta pengguna menjalankan verifikasi sebelum mengubah kode berikutnya.
- Jangan commit IP LAN di `config.dart` sebagai konfigurasi universal. Audit dan pisahkan konfigurasi environment pada pekerjaan berikutnya.


## 13. Pembaruan 2026-11-27 — Financial Integrity & Security Hardening

### Status Git dan branch
- Seluruh implementasi lanjutan dikerjakan hanya pada branch `fix/security-authentication`. Tidak ada commit atau merge ke `main`.
- Setelah implementasi, `fix/security-authentication` berada 65 commit di depan dan 0 commit di belakang `main`.
- Posisi lokal terakhir yang diketahui dari terminal pengguna adalah `de6b9c1`. Remote kini 28 commit di depan posisi tersebut. Daftar file pada 28 commit baru tidak mencakup tiga file lokal yang sebelumnya modified: `clipkeo app/lib/config.dart`, `AdminBountyController.php`, dan `DashboardController.php`.
- Branch `fix/security-cloudinary-config` tetap terpisah, 1 commit di depan `main`. Diff-nya menghapus endpoint diagnostik publik `/cek-config` dan `/cek-koneksi-cloud`. Branch itu tidak di-merge; penghapusan `/cek-config` dipindahkan secara manual ke branch pengembangan utama, sementara `/cek-koneksi-cloud` memang sudah tidak ada di branch tersebut.
- Commit kode terakhir sebelum pembaruan dokumen ini: `9b9c172ae1e33245996a5b9c0a036424df50022a`.

### Implementasi yang sudah di-commit
1. **Ledger transaksi**
   - Menambahkan konstanta tipe transaksi sesuai ENUM: `deposit`, `withdrawal`, `bounty_hold`, `bounty_prize`, `bounty_refund`, dan `platform_fee`.
   - Menambahkan compatibility mapping: `reward` dan `payout` menjadi `bounty_prize`; `refund` menjadi `bounty_refund`.
   - `SubmissionController` kini mencatat kedua sisi pembayaran hadiah sebagai `bounty_prize`.
   - Filter dan tampilan wallet web, tampilan wallet Flutter, statistik publik, dan grafik Admin membedakan pemasukan/pengeluaran berdasarkan role pemilik transaksi.
2. **Refund campaign**
   - `BountyController::destroy` memakai transaksi database dan row lock pada campaign serta Publisher.
   - Refund menggunakan `bounty_refund`; `remaining_budget` diset ke 0 setelah refund agar tidak dapat dihitung atau dikembalikan dua kali.
   - Kebijakan residual <= Rp100 dan penggunaan status `completed` untuk pembatalan belum diubah.
3. **Withdrawal**
   - API withdrawal sekarang mewajibkan PIN 6 digit dan memverifikasinya di server; endpoint PIN dan withdrawal diberi throttle.
   - Flutter mengirim PIN bersama request withdrawal.
   - Saldo dan payout method dikunci ulang di dalam transaksi API.
   - Withdrawal web memvalidasi kepemilikan payout method dan memproses pembuatan request serta debit saldo dalam satu transaksi; notifikasi dikirim setelah commit.
   - Webhook withdrawal memakai row lock dan hanya menerima transisi dari status pending/processing. Callback gagal duplikat atau callback gagal yang datang setelah sukses tidak mengembalikan saldo lagi.
4. **Route security**
   - Menghapus endpoint diagnostik publik `/cek-config`.
   - Menghapus deklarasi duplikat route approve/reject withdrawal yang berada di luar group admin.
5. **Test yang ditambahkan/diubah**
   - `TransactionTypeNormalizationTest`
   - `WalletWithdrawalPinTest`
   - `WithdrawalOwnershipTest`
   - `WithdrawalWebhookIdempotencyTest`
   - `AdminWithdrawalAuthorizationTest`
   - `AuthenticationSecurityTest` diperluas untuk memastikan endpoint diagnostik tidak terbuka.

### Status verifikasi
- Test otomatis telah ditambahkan, tetapi **belum dijalankan** pada lingkungan Laravel project.
- Pemeriksaan GitHub untuk commit terakhir melaporkan tidak ada status checks dan tidak ada workflow run terkait. Karena itu, jangan menyebut perubahan ini sudah lulus test atau terverifikasi end-to-end.
- Setelah branch disinkronkan di komputer pengguna, jalankan `php artisan test`, `php artisan test --filter=TransactionTypeNormalizationTest`, `php artisan test --filter=WalletWithdrawalPinTest`, `php artisan test --filter=WithdrawalOwnershipTest`, `php artisan test --filter=WithdrawalWebhookIdempotencyTest`, `php artisan test --filter=AdminWithdrawalAuthorizationTest`, serta `flutter analyze`.

### Risiko P0 yang masih terbuka
- `WithdrawalController::approve` masih perlu idempotency untuk mencegah dua admin memicu transfer Xendit ganda. Status request belum dikunci dan external reference belum stabil per withdrawal.
- Siklus ledger withdrawal web dan API belum sepenuhnya seragam. API membuat transaksi pending saat pengajuan, sedangkan web tidak; proses approve dapat menambahkan catatan transaksi lain. Tetapkan satu lifecycle dan satu referensi ledger sebelum rilis.
- Kebijakan refund saat transfer gagal perlu dipastikan: webhook saat ini mengembalikan nilai dari transaksi yang direferensikan; pastikan apakah fee 10% juga harus dikembalikan.
- `AdminBountyController` belum diubah karena file tersebut memiliki diff lokal pengguna. Compatibility mapping di model melindungi write lama untuk sementara; setelah perubahan lokal disinkronkan, refactor call site ke konstanta canonical dan uji kedua sisi ledger.
- Status `completed` masih mencampur campaign yang anggarannya habis dan campaign yang dibatalkan/refund. Pertimbangkan status `cancelled` melalui migration terencana, bukan perubahan enum mendadak.
- Perubahan lokal `DashboardController.php` untuk escrow `pending_review` dan `active` belum masuk remote. Jangan menimpa diff lokal tersebut.

### Langkah berikutnya
1. Di komputer pengguna, verifikasi `git status -sb` dan backup tiga diff lokal. Remote 28 commit di depan HEAD lokal terakhir yang diketahui; fast-forward hanya setelah status/diff diperiksa.
2. Setelah sinkronisasi, periksa kembali tiga diff lokal dan pertahankan perubahan yang dimaksud.
3. Jalankan seluruh test Laravel dan `flutter analyze`; perbaiki setiap kegagalan sebelum fitur baru.
4. Lanjutkan idempotency approval Xendit dan satukan lifecycle ledger withdrawal.
5. Setelah itu, audit ulang dashboard escrow, refund Admin, dan konsistensi saldo/ledger dengan data staging.


## 14. Pembaruan 2026-11-27 — API Withdrawal Idempotency dan Mobile Retry

### Posisi branch
- Pekerjaan dilanjutkan pada `fix/security-authentication`; tidak ada perubahan atau merge ke `main`.
- HEAD saat catatan ini dibuat: `9761aa563e422a0e9863577eaa67a3e4e1ccc379`.
- Branch dibandingkan dengan `main`: 174 commit ahead, 0 behind.

### Verifikasi lokal yang dilaporkan pengguna sebelum perubahan idempotency API
- `php artisan test`: 64 tests passed, 249 assertions.
- `WithdrawalApprovalIdempotencyTest`: 7 tests passed.
- `WithdrawalWebhookIdempotencyTest`: 4 tests passed.
- `WithdrawalOwnershipTest`: 2 tests passed.
- `WalletWithdrawalPinTest`: 1 test passed.
- Flutter `pub get`: berhasil; 105 paket memiliki versi lebih baru yang tidak cocok dengan constraint saat ini.
- Flutter `analyze`: 227 temuan kategori info; tidak ada error analyzer yang terlihat pada output yang dibagikan.
- Flutter `test` saat itu belum dapat dijalankan karena direktori `test` belum tersedia.
- APK debug belum dilaporkan berhasil dibangun.

### Implementasi baru di branch — BELUM diverifikasi di komputer pengguna
1. **API idempotency**
   - Migration baru: `2026_11_27_000002_add_idempotency_key_to_withdrawal_requests.php`.
   - API `POST /wallet/withdraw` sekarang mewajibkan header UUID `Idempotency-Key`.
   - Kunci unik per user pada `withdrawal_requests`; legacy/web requests tetap dapat menyimpan NULL.
   - Retry dengan key dan payload sama mengembalikan withdrawal lama tanpa debit atau notifikasi kedua.
   - Key sama dengan nominal/rekening berbeda menghasilkan HTTP 409.
   - Pencarian withdrawal lama dilakukan sebelum pemeriksaan saldo, sehingga replay tetap bisa dipulihkan setelah debit pertama mengurangi saldo.
   - Response mengembalikan `withdrawal_status` aktual, termasuk untuk replay.

2. **Flutter retry safety**
   - `WalletService` membuat UUID v4 dan menyimpan key serta payload sebelum request dikirim.
   - Data pending dipisahkan berdasarkan `user_id`, sehingga tidak bercampur antar akun dan tetap dikenali setelah token diperbarui.
   - `AuthService.logout` mempertahankan catatan idempotency yang belum terselesaikan; PIN dan bearer token tidak disimpan di catatan tersebut.
   - Timeout, 401/422, 409, 429, dan 5xx mempertahankan key untuk rekonsiliasi. Respons 400 yang definitif dan respons 200 membersihkan key.
   - Layar withdrawal memulihkan nominal/rekening yang tertunda, mengunci perubahan payload sampai status diperiksa, dan mengizinkan retry walau saldo saat ini sudah berkurang.
   - Layar hasil menampilkan status withdrawal dari server, bukan mengasumsikan semua response adalah pending.

3. **Test baru**
   - Backend: `WalletWithdrawalIdempotencyTest` berisi 4 skenario: replay tidak mendebit dua kali, payload berbeda ditolak, key wajib, dan key yang sama tetap terisolasi per user.
   - `WalletWithdrawalPinTest` diperbarui untuk mengirim header idempotency.
   - Flutter: `test/services/wallet_service_test.dart` mencakup retry setelah timeout, token renewal, payload mismatch, PIN salah, dan penolakan saldo yang definitif.

### Risiko dan kompatibilitas
- API baru mewajibkan `Idempotency-Key`; versi aplikasi lama yang belum mengirim header akan menerima HTTP 422. Deploy backend dan rilis Flutter harus dikoordinasikan.
- Catatan pending disimpan di SharedPreferences. Uninstall aplikasi atau penghapusan data aplikasi dapat menghilangkan kunci lokal; lakukan uji staging dan pertimbangkan rekonsiliasi server-side tambahan sebelum produksi.
- Implementasi dan test baru di atas belum boleh disebut lulus sampai pengguna menjalankan test di komputer lokal.

### Langkah berikutnya
1. Sinkronkan branch secara fast-forward; jangan apply/pop stash lokal lama.
2. Jalankan `php artisan test --filter=WalletWithdrawalIdempotencyTest`, `php artisan test --filter=WalletWithdrawalPinTest`, lalu seluruh `php artisan test`.
3. Di folder Flutter jalankan `flutter pub get`, `dart format lib/services/wallet_service.dart lib/screens/withdraw_input_screen.dart lib/screens/withdraw_success_screen.dart lib/auth_service.dart test/services/wallet_service_test.dart`, `flutter analyze`, `flutter test`, dan `flutter build apk --debug`.
4. Setelah test lolos, uji di staging: request sukses, timeout setelah commit, retry dengan key sama, token renewal/logout-login, PIN salah lalu benar, key berbeda payload, saldo rendah, dan status Xendit pending/completed/failed.
5. Backup database sebelum menjalankan migration pada environment staging/production. Jangan gunakan `migrate:fresh`.
6. Pertahankan branch `fix/security-authentication`; jangan merge ke `main` tanpa persetujuan.


### Follow-up setelah catatan bagian 14
- Perubahan lanjutan sampai HEAD `9cdf2a157d3994f65b0d99a2b5cb8910b01df774` menambahkan status withdrawal aktual pada response API dan UI Flutter, termasuk tampilan berbeda untuk status rejected.
- Flutter mempertahankan idempotency key setelah HTTP 401/422 karena pemeriksaan PIN/validasi terjadi sebelum lookup idempotency di server; hal ini mencegah retry yang sah kehilangan key setelah request sebelumnya mungkin sudah commit.
- Catatan test Flutter mencakup token renewal untuk user ID yang sama dan retry setelah timeout. Seluruh perubahan lanjutan tetap menunggu eksekusi lokal.
- Posisi branch saat pembaruan ini: 179 commit ahead dan 0 behind dari `main`.

## 15. 2026-11-27 — Submission API: missing video_id column

### Temuan dari pengujian Android
- Saat Clipper mengirim submission campaign dari aplikasi Android, API gagal dengan SQLSTATE[42S22], Unknown column 'video_id' in 'where clause'.
- Query yang gagal adalah pencarian submissions.video_id menggunakan ID video TikTok.
- Audit migration 2026_01_12_094226_create_clipsquad_full_schema.php menunjukkan tabel submissions dibuat tanpa kolom video_id.
- App\Models\Submission, SubmissionController, dan handler POST /api/campaigns/{id}/submit sudah memakai video_id. Jadi penyebabnya adalah ketidaklengkapan migration schema, bukan kegagalan upload video atau koneksi Android.

### Perbaikan yang ditambahkan ke branch
1. Migration baru: 2026_11_27_000003_add_video_id_to_submissions_table.php.
   - Menambahkan video_id sebagai VARCHAR(100), nullable agar submission lama tetap valid.
   - Menambahkan index submissions_video_id_index untuk pencarian video.
   - Tidak memakai unique index karena ID lintas platform belum dinamespace oleh platform; unique global berisiko menolak ID yang kebetulan sama di platform berbeda.
2. Test baru: tests/Feature/SubmissionVideoIdSchemaTest.php.
   - Menjalankan query Eloquent Submission::where('video_id', ...)->first() yang sebelumnya gagal.
3. Keamanan respons API:
   - Handler submission API tidak lagi mengirim pesan exception SQL mentah, nama database, host, port, atau query ke aplikasi.
   - Detail exception dicatat di Laravel log; aplikasi menerima pesan server yang umum.

### Status Git dan verifikasi
- Semua perubahan dibuat hanya di fix/security-authentication.
- Commit implementasi kode: 452812ed9e4fd205a972ff1ae8a22afeb7844965. HEAD branch setelah pembaruan handoff: 14b181a37a5ed2b6de96b9e22a2f6f5b17eb1a2b.
- main tetap pada 3d291f00024df157218b03b2983478bd5d61e826; tidak ada merge ke main.
- Test baru dan migration belum dijalankan pada komputer lokal pengguna. Jangan menyatakan submission sudah berhasil sampai migration diterapkan dan test lokal lulus.
- Sebelum sinkronisasi di Windows, periksa git status -sb dan amankan perubahan lokal clipkeo app/lib/config.dart (IP LAN 192.168.18.3) serta perubahan lain. Jangan menimpa konfigurasi jaringan lokal pengguna.

### Langkah verifikasi lokal berikutnya
1. Pastikan working tree aman; stash hanya setelah memeriksa file yang berubah.
2. Fast-forward fix/security-authentication ke remote HEAD.
3. Jalankan php artisan migrate:status dan pastikan migration 2026_11_27_000003_add_video_id_to_submissions_table masih pending.
4. Backup database lokal, lalu jalankan php artisan migrate (bukan migrate:fresh).
5. Jalankan php artisan test --filter=SubmissionVideoIdSchemaTest, lalu seluruh php artisan test.
6. Build dan install ulang APK bila diperlukan, lalu uji submission satu kali dengan data uji.
7. Pastikan submission tersimpan satu kali, statusnya pending, bukti analytics tersimpan, dan campaign tidak mengurangi budget sampai approval sesuai desain produk.
8. Pertahankan branch fix/security-authentication; jangan merge ke main tanpa instruksi eksplisit.


## 16. Pembaruan 2026-11-27 — Rekonsiliasi awal Submission #1

### Hasil SQL lokal yang dilaporkan pengguna
Database: `clipsquad_db` (MySQL lokal).

Query pertama berhasil dan menghasilkan:
- Submission ID: `1`
- Bounty/Campaign ID: `2`
- Clipper ID: `1`
- `views_count`: `10000`
- `last_paid_views`: `10000`
- `prize_amount`: `50000.00`
- `payout_status`: `partial`
- `status`: `approved`
- `approved_at`: `2026-11-27 21:07:42`

Interpretasi:
- Record submission sudah berstatus approved dan menyimpan 10.000 views serta prize Rp50.000.
- Keberadaan nilai `approved_at` dan timestamp di atas membuktikan kolom tersebut tersedia dan terisi pada database lokal pengguna.
- Ini belum membuktikan saldo Clipper, transaksi `bounty_prize` pada sisi Clipper/Publisher, atau pengurangan `remaining_budget` sudah konsisten. Rekonsiliasi keuangan masih terbuka.
- Pada implementasi `SubmissionController::approve` di branch yang diperiksa, `payout_status` di-set menjadi `paid` hanya jika `remaining_budget <= 100`; selain itu di-set menjadi `partial`. Jadi nilai `partial` pada record ini tidak boleh langsung dianggap bukti bahwa reward Rp50.000 belum dibayarkan. Saat ini field tersebut lebih mencerminkan kondisi sisa budget campaign; semantik ini perlu dievaluasi dan diuji, bukan diubah tanpa persetujuan.

### Query lanjutan yang gagal
Query kedua berhenti pada error:
`Unknown column 'budget' in 'field list'`.

Penyebab: kolom `budget` tidak ada pada model Bounty di branch yang diperiksa. Model menggunakan `reward_amount` sebagai total max budget dan `remaining_budget` untuk sisa budget. Karena MySQL berhenti pada statement tersebut, query campaign, saldo user, dan transaksi yang ditulis setelahnya belum menghasilkan data.

### Query read-only berikutnya
Jalankan dari folder `E:\\Clipkeo\\clipkeo web`:

```sql
SELECT id, title, publisher_id, status, reward_amount, remaining_budget
FROM bounties
WHERE id = 2;

SELECT id, name, role, wallet_balance
FROM users
WHERE id IN (1, 2);

SELECT id, user_id, type, amount, status, reference_id, description, created_at
FROM transactions
WHERE user_id IN (1, 2)
  AND type = 'bounty_prize'
ORDER BY id DESC
LIMIT 10;
```

Tujuan: cocokkan sisa budget campaign, saldo Clipper/Publisher, dan kedua sisi ledger hadiah. Jangan approve ulang Submission #1 dan jangan membuat transaksi manual.

### Catatan migration
- Database lokal terbukti memiliki kolom `approved_at` dan nilainya sudah terisi.
- Pastikan migration yang menambahkan `approved_at` ikut tersimpan di branch/repository. Jangan menganggap kolom lokal otomatis tersedia pada instalasi baru atau server lain.
- Migration `2026_11_27_000003_add_video_id_to_submissions_table.php` sudah ada pada branch; status eksekusi migration tersebut di database lokal tetap harus diverifikasi melalui `php artisan migrate:status`.

### Status pengujian setelah pembaruan ini
- [x] Submission #1 tersimpan dengan status approved.
- [x] Views 10.000 dan last_paid_views 10.000 tersimpan.
- [x] Prize amount Rp50.000 tersimpan pada record submission.
- [x] Kolom approved_at tersedia dan terisi pada database lokal.
- [ ] Rekonsiliasi saldo Clipper dan Publisher.
- [ ] Rekonsiliasi transaksi bounty_prize pada kedua sisi ledger.
- [ ] Verifikasi reward_amount dan remaining_budget Campaign #2.
- [ ] Audit semantik payout_status partial/paid.
- [ ] Pastikan migration approved_at tercatat dan dapat dijalankan pada database baru.


## 17. Pembaruan 2026-11-27 — Rekonsiliasi reward Publisher approval selesai

Pengguna menjalankan query read-only untuk Campaign #2, user ID 1 dan 2, serta transaksi `bounty_prize`. Hasil lokal yang dilaporkan:

### Campaign #2
- Title: `Campaign Uji Validasi`
- Publisher ID: 2
- Status: `active`
- `reward_amount`: Rp1.000.000
- `remaining_budget`: Rp950.000

### Saldo pengguna
- Clipper ID 1 (`Yoga Dwi`): `wallet_balance = Rp50.000`
- Publisher ID 2 (`Publisher Testing`): `wallet_balance = Rp0`

Publisher wallet Rp0 konsisten dengan dana campaign Rp1.000.000 yang telah di-hold sebelumnya; jangan menyimpulkan Publisher kembali didebit Rp50.000 dari wallet bebas. Biaya hadiah tercermin pada pengurangan `remaining_budget` dan ledger Publisher.

### Ledger reward
- Transaction ID 4, user ID 1 (Clipper), `bounty_prize`, Rp50.000, `success`, reference `RWD-20261127-1-4LEg`.
- Transaction ID 5, user ID 2 (Publisher), `bounty_prize`, Rp50.000, `success`, reference `PAY-20261127-1-jixw`.

### Kesimpulan pengujian
- Reward Rp50.000 sudah benar-benar masuk ke saldo Clipper.
- Sisa budget berkurang dari Rp1.000.000 menjadi Rp950.000, tepat sebesar reward.
- Ledger hadiah tercatat pada sisi Clipper dan Publisher dengan nominal sama.
- Query menunjukkan satu transaksi reward per sisi untuk Submission #1; tidak terlihat pembayaran ganda pada hasil query.
- Alur Publisher approve → hitung reward → transfer saldo Clipper → pencatatan ledger dinyatakan lulus untuk skenario uji ini.

### Catatan yang masih terbuka
- `payout_status` pada Submission #1 masih `partial` walau seluruh reward yang terhitung pada persetujuan ini sudah dibayarkan. Pada implementasi saat ini, nilai tersebut ditentukan berdasarkan `remaining_budget <= 100`, yaitu kondisi budget campaign, bukan pelunasan reward submission. Jangan ubah kebijakan pembayaran; evaluasi dan perbaiki definisi/status UI secara terpisah setelah persetujuan.
- Migration untuk kolom `approved_at` belum berhasil diidentifikasi pada file migration repository. Database lokal memang memiliki kolom itu, tetapi database baru belum boleh diasumsikan akan mendapatkannya.

### Next test
Buat submission baru dengan status `pending` pada campaign aktif yang memiliki budget cukup, biarkan Publisher tidak memprosesnya, tunggu minimal 5 menit sesuai aturan monitoring, lalu uji Admin Takeover pada submission baru tersebut. Jangan gunakan ulang Submission #1 yang sudah approved dan dibayar.


## 18. Pembaruan 2026-11-28 — Pengujian Manual Admin Takeover Lulus

### Skenario yang diuji
Pengguna melaporkan seluruh langkah berikut berhasil pada environment lokal Clipkeo dengan akun Clipper kedua yang dibuat khusus untuk pengujian takeover:

- [x] Clipper kedua mengirim submission ke campaign aktif.
- [x] Publisher membiarkan submission tetap berstatus pending.
- [x] Menunggu minimal 5 menit sesuai aturan monitoring.
- [x] Admin membuka halaman Review & Takeover dan menemukan submission tersebut.
- [x] Admin mengambil alih proses dan menyetujui submission.
- [x] Saldo, reward, dan transaksi diperiksa; pengguna melaporkan tidak terjadi pembayaran/pencatatan reward ganda.

### Hasil dan batas bukti
- Skenario Admin Takeover manual dinyatakan lulus berdasarkan laporan pengguna pada 2026-11-28.
- Pengujian ini mencakup jalur moderasi alternatif ketika Publisher tidak memproses submission tepat waktu.
- Pengguna tidak menyertakan output SQL, submission ID, nominal reward, ID transaksi, atau saldo sebelum/sesudah pada laporan ini. Karena itu, handoff tidak mengarang angka atau identifier. Simpan hasil query/database berikutnya jika diperlukan sebagai bukti audit yang dapat direproduksi.
- Jangan mengulang approval pada submission yang sudah final. Gunakan submission baru untuk regression test berikutnya.

### Status checklist
- [x] Pembuatan dan penggunaan akun Clipper kedua untuk skenario takeover.
- [x] Submission tetap pending saat Publisher tidak memproses.
- [x] Batas tunggu minimal 5 menit terpenuhi.
- [x] Admin menemukan dan mengambil alih submission.
- [x] Approval oleh Admin berhasil.
- [x] Pemeriksaan saldo, reward, dan transaksi dilaporkan berhasil tanpa payout ganda.

### Pengujian berikutnya — Withdrawal Web/API dan Xendit
Lanjutkan ke pengujian lifecycle withdrawal secara terkontrol menggunakan sandbox/mock atau environment test:
1. Ajukan withdrawal dari aplikasi/API dan dari website, masing-masing dengan data uji terpisah.
2. Pastikan saldo didebit satu kali, request dan ledger memiliki referensi yang konsisten, dan retry tidak membuat withdrawal/debit kedua.
3. Uji approval Admin dengan idempotency key yang stabil; timeout ambigu harus direkonsiliasi sebelum transfer dicoba ulang.
4. Simulasikan webhook Xendit completed, failed, duplikat, dan urutan callback yang terlambat.
5. Untuk kegagalan transfer Xendit, verifikasi kebijakan Clipkeo: seluruh biaya admin 10% dikembalikan kepada pengguna, satu kali saja. Pastikan jumlah refund, saldo akhir, status fee, dan ledger sesuai.
6. Pastikan callback failed yang datang setelah status completed tidak membatalkan pembayaran atau mengembalikan saldo lagi.

**Batasan:** jangan melakukan transfer uang sungguhan atau mengubah tarif/kebijakan finansial tanpa persetujuan eksplisit. Catat test yang benar-benar dijalankan beserta hasil aktual; skenario sandbox/mock tidak boleh disebut sebagai transfer produksi.


## 19. Pembaruan 2026-11-28 — Baseline Pengujian Withdrawal

### Lingkup dan sumber bukti
Pengguna menjalankan query read-only pada database lokal `clipsquad_db` dan melaporkan bahwa dua permintaan withdrawal masuk ke halaman Admin. Admin menguji satu permintaan dengan persetujuan dan satu permintaan dengan penolakan. Output SQL yang diberikan menjadi bukti untuk status lokal saat query dijalankan.

**Catatan penting:** output ini belum mengidentifikasi permintaan mana yang diajukan melalui Android dan mana yang melalui website. Karena itu, pengujian kanal Android dan website belum boleh ditandai selesai secara terpisah.

### Withdrawal Request #1 — Clipper ID 4 (Clipper Takeover Test)
- Withdrawal request ID: 1.
- Payout method ID: 2 (Dana; empat digit terakhir rekening 0651).
- Nominal bruto: Rp50.000.
- Biaya admin 10%: Rp5.000.
- Nominal bersih yang semula akan diterima: Rp45.000.
- Status withdrawal: `rejected`.
- Saldo Clipper ID 4 pada hasil query: Rp50.000.
- Ledger withdrawal: satu transaksi `withdrawal` Rp45.000 berstatus `failed`.
- Ledger biaya: satu transaksi `platform_fee` Rp5.000 berstatus `failed`.
- Ledger refund: satu transaksi `withdrawal_refund` Rp50.000 berstatus `success`.

**Kesimpulan:** jalur penolakan withdrawal dan refund penuh terverifikasi pada output database yang dilaporkan. Nominal refund sama dengan nominal bruto, termasuk biaya admin. Tidak terlihat duplikasi pada agregasi transaksi yang diberikan.

### Withdrawal Request #2 — Clipper ID 1 (Yoga Dwi)
- Withdrawal request ID: 2.
- Payout method ID: 1 (BCA; empat digit terakhir rekening 8007).
- Nominal bruto: Rp50.000.
- Biaya admin 10%: Rp5.000.
- Nominal bersih yang diharapkan diterima: Rp45.000.
- Saldo Clipper ID 1 pada hasil query: Rp0.
- Status withdrawal: `processing`.
- Ledger withdrawal: satu transaksi `withdrawal` Rp45.000 berstatus `pending`.
- Ledger biaya: satu transaksi `platform_fee` Rp5.000 berstatus `pending`.
- Pengguna melaporkan belum menerima notifikasi keberhasilan dan belum melihat transaksi berhasil pada riwayat.

**Kesimpulan:** permintaan sudah tercatat dan dana bruto telah ditahan, tetapi penyelesaian transfer belum terbukti. Status `processing` pada withdrawal dan `pending` pada kedua baris ledger konsisten dengan proses yang masih menunggu konfirmasi. Jangan mengulang approval atau membuat withdrawal baru untuk request ID 2 sebelum status provider direkonsiliasi.

### Pembacaan implementasi terkait
- `WithdrawalController::approve` mengubah request ke `processing`, memakai external ID dan idempotency key deterministik, memeriksa disbursement yang sudah ada di Xendit, dan menunggu status provider.
- Jika status provider ditemukan `COMPLETED`, controller dapat menyelaraskan ledger menjadi sukses dan mengirim notifikasi.
- `WebhookController::handleWithdrawal` memproses status `COMPLETED` melalui `WithdrawalLedgerService::markCompleted`, kemudian mengirim `FinancialStatusNotification`.
- Status `FAILED` memanggil `markFailed`, mengembalikan seluruh `amount_requested`, dan mengirim notifikasi penolakan/refund.
- Webhook memerlukan header `x-callback-token` yang cocok dengan konfigurasi `services.xendit.verification_token`.
- Implementasi yang dibaca hanya menangani status withdrawal persis `COMPLETED` dan `FAILED`; status lain tidak mengubah ledger. Konfirmasi payload aktual dari Xendit masih diperlukan.

### Status pengujian withdrawal
- [x] Dua permintaan withdrawal tercatat dan muncul pada alur Admin menurut laporan pengguna.
- [x] Penolakan request ID 1: withdrawal dan fee ditandai failed, refund bruto Rp50.000 tercatat success.
- [~] Request ID 2 disetujui/diproses: saldo telah ditahan dan ledger withdrawal/fee masih pending.
- [ ] Buktikan status disbursement ID 2 di dashboard/API Xendit dan cocokkan external ID serta provider ID.
- [ ] Pastikan webhook Xendit untuk ID 2 diterima endpoint lokal, lolos verifikasi callback token, dan tercatat di log.
- [ ] Pastikan request ID 2 berubah menjadi completed, transaksi withdrawal dan fee menjadi success, `paid_at` terisi, dan notifikasi database/email/FCM terkirim.
- [ ] Jalankan pengujian withdrawal Android secara terpisah dan simpan request ID serta bukti hasilnya.
- [ ] Jalankan pengujian withdrawal website secara terpisah dan simpan request ID serta bukti hasilnya.
- [ ] Uji webhook duplikat dan urutan callback terlambat tanpa transfer/refund ganda.

### Langkah diagnosis berikutnya
Gunakan query read-only untuk memeriksa `reference_id`, `external_ref`, `paid_at`, dan notifikasi untuk withdrawal request ID 2. Cocokkan external ID yang tersimpan dengan disbursement di dashboard Xendit. Periksa juga log Laravel untuk event webhook masuk, reference mismatch, dan konfirmasi completion. Jangan mengirim secret, PIN, API key, callback token, atau nomor rekening lengkap ke handoff.

### Aturan keselamatan pengujian
Jangan mengulang approval request ID 2, jangan menghapus atau mengubah ledger secara manual, dan jangan melakukan transfer produksi tambahan untuk keperluan pengujian tanpa persetujuan eksplisit. Jika provider menunjukkan status completed tetapi ledger lokal tetap pending, lakukan rekonsiliasi manual sebelum ada tindakan keuangan lain.


## 20. Bukti Xendit COMPLETED dan callback ngrok gagal — 2026-11-28

### Bukti dashboard Xendit
Pengguna mengirim screenshot detail disbursement untuk withdrawal request ID 2. Screenshot menunjukkan:
- Nominal disbursement: IDR 45,000.
- Status provider: `Completed`.
- External ID: `WD-local-2`.
- Waktu dibuat: 28 Nov 2026, 08:57:34 GMT+7.
- Waktu selesai: 28 Nov 2026, 09:00:03 GMT+7.
- Detail callback: `Failed`, HTTP `404 Client Error`.
- URL callback menggunakan domain ngrok `moist-smothered-miriam.ngrok-free.dev`, path `/webhooks/xendit`.
- Body respons adalah halaman error HTML dari ngrok, bukan respons JSON dari Laravel. Pesan halaman menunjukkan endpoint ngrok tidak aktif (ERR_NGROK_3200).

### Kesimpulan yang diperbarui
Status provider kini **terkonfirmasi COMPLETED**. Pernyataan sebelumnya bahwa status transfer Xendit belum diketahui sudah tidak berlaku. Penyebab perbedaan status lokal/provider yang terlihat pada bukti adalah callback Xendit gagal mencapai aplikasi lokal karena tunnel ngrok lama tidak aktif saat callback dikirim.

Status lokal terakhir yang dilaporkan tetap:
- Withdrawal request ID 2: `processing`.
- Ledger `withdrawal` Rp45.000: `pending`.
- Ledger `platform_fee` Rp5.000: `pending`.
- Belum ada notifikasi keberhasilan yang diterima pengguna.

### Kesesuaian dengan implementasi
- Route publik webhook Laravel: `POST /webhooks/xendit`.
- Controller memvalidasi header `x-callback-token` terhadap `services.xendit.verification_token` (env `XENDIT_VERIFICATION_TOKEN`).
- Handler mengenali external ID `WD-local-2` untuk withdrawal ID 2 selama `APP_ENV=local`, lalu menangani status `COMPLETED`.
- `WithdrawalLedgerService::markCompleted` mengubah request menjadi `completed`, transaksi withdrawal menjadi `success`, transaksi platform fee menjadi `success`, dan mengisi `paid_at`.
- Webhook kemudian mengirim `FinancialStatusNotification` untuk database notification, email, dan FCM.
- Jangan mengubah ledger atau saldo secara manual. Gunakan callback resmi Xendit yang dikirim ulang setelah tunnel publik tersambung kembali.

### Prosedur pemulihan
1. Jalankan kembali aplikasi Laravel lokal dan pastikan dapat diakses.
2. Jalankan ngrok ke port yang benar (misalnya port 80 untuk Apache Laragon atau port 8000 jika memakai `php artisan serve`). Jika memakai virtual host Laragon, sesuaikan Host Header.
3. Perbarui callback URL Xendit menjadi `https://<domain-ngrok-aktif>/webhooks/xendit`.
4. Pastikan konfigurasi `XENDIT_VERIFICATION_TOKEN` di Laravel sama dengan callback verification token pada konfigurasi webhook Xendit. Jangan pernah mencatat token ke Git atau membagikannya.
5. Uji endpoint dengan POST tanpa token; respons yang diharapkan adalah HTTP 401 JSON dari Laravel (membuktikan route tercapai dan validasi token aktif), bukan halaman 404 ngrok.
6. Setelah tunnel stabil, gunakan tombol **Kirim Ulang** pada detail callback Xendit untuk event disbursement yang sudah COMPLETED. Jangan membuat disbursement atau withdrawal baru.
7. Periksa log Laravel dan query database. Kriteria selesai: request `completed`, withdrawal dan platform fee `success`, `paid_at` terisi, dan notifikasi tercatat/terkirim.

### Status checklist terbaru
- [x] Xendit menunjukkan disbursement ID 2 berstatus `COMPLETED` untuk IDR 45.000.
- [x] Screenshot membuktikan callback gagal HTTP 404 pada domain ngrok lama.
- [~] Diagnosis akar masalah: tunnel ngrok tidak aktif saat callback dikirim.
- [ ] Aktifkan ulang tunnel dan perbarui URL callback Xendit.
- [ ] Kirim ulang callback yang gagal dari dashboard Xendit.
- [ ] Verifikasi perubahan status ledger lokal dan notifikasi setelah callback diterima.

### Catatan keselamatan
Disbursement sudah berstatus `COMPLETED`. Jangan mengulang approval Admin, membuat payout baru, mengubah saldo manual, mengubah status SQL manual, atau menjalankan refund. Hanya rekonsiliasi callback dari event yang sama. Simpan bukti provider dan hasil database setelah pemulihan.


### Kendala lanjutan: ngrok CLI belum tersedia di PowerShell
Pada 28 Nov 2026, pengguna menjalankan `ngrok http 8000` dan `ngrok http 80` dari PowerShell (working directory `E:\\Clipkeo`). Keduanya gagal dengan `CommandNotFoundException`: Windows tidak menemukan executable `ngrok`. Pengguna mengonfirmasi Laravel lokal berjalan/diakses melalui `http://127.0.0.1:8000`.

Langkah berikutnya:
1. Instal ngrok CLI untuk Windows (jalur resmi yang direkomendasikan: `winget install ngrok -s msstore`, atau unduh executable Windows dari situs resmi ngrok).
2. Buka PowerShell baru dan verifikasi `ngrok version`.
3. Tambahkan authtoken dari dashboard akun ngrok dengan `ngrok config add-authtoken <TOKEN>`; token tidak boleh ditempel ke chat, log, atau Git.
4. Jalankan `ngrok http 8000`, biarkan terminal tetap terbuka, lalu uji URL HTTPS publik.
5. Perbarui callback Xendit ke `https://<domain-ngrok-aktif>/webhooks/xendit` dan kirim ulang event COMPLETED yang sama.

Status: instalasi CLI, tunnel aktif, callback resend, dan rekonsiliasi database masih belum diverifikasi. Jangan membuat payout baru atau mengubah saldo/ledger secara manual.


### Status tunnel terbaru: ngrok sudah online
Pada 28 Nov 2026 pengguna mengirim screenshot ngrok v3.39.13. Session berstatus `online`, region `Asia Pacific (ap)`, Web Interface `http://127.0.0.1:4040`, forwarding `https://moist-smothered-miriam.ngrok-free.dev -> http://localhost:8000`. Ini sesuai alamat Laravel lokal yang dikonfirmasi pengguna (`http://127.0.0.1:8000`). Tunnel sudah aktif pada saat screenshot, tetapi endpoint webhook belum diuji dari luar dan callback Xendit belum dikirim ulang.

Verifikasi kode pada branch ini: `routes/web.php` mendaftarkan `POST /webhooks/xendit` ke `WebhookController::handle`; `bootstrap/app.php` mengecualikan path webhook dari CSRF; controller membandingkan header `x-callback-token` dengan `services.xendit.verification_token` dan mengembalikan JSON HTTP 401 `Unauthorized` bila token tidak ada/tidak cocok. Maka POST uji tanpa token ke domain aktif seharusnya menghasilkan 401 JSON Laravel, bukan 404 HTML ngrok.

Perintah uji (jalankan di PowerShell kedua, jangan tutup terminal ngrok):
```powershell
curl.exe -i -X POST "https://moist-smothered-miriam.ngrok-free.dev/webhooks/xendit" -H "Content-Type: application/json" -d "{}"
```

Langkah berikut: catat output uji; bila HTTP 401 JSON, endpoint sudah mencapai Laravel. Selanjutnya perbarui callback URL di dashboard Xendit ke `https://moist-smothered-miriam.ngrok-free.dev/webhooks/xendit`, pastikan verification token sesuai tanpa membagikan nilainya, lalu resend callback untuk disbursement `WD-local-2` yang sudah COMPLETED. Jangan membuat payout baru atau mengubah saldo/ledger manual.


### 28 Nov 2026 — Hasil uji pertama endpoint melalui ngrok
Pada 28 Nov 2026 pengguna menjalankan POST ke `https://moist-smothered-miriam.ngrok-free.dev/webhooks/xendit` dengan body `{}`. Respons: HTTP 404 HTML dengan header `Ngrok-Error-Code: ERR_NGROK_3200` dan halaman menyatakan endpoint offline. Ini adalah respons dari ngrok, bukan HTTP 404 dari Laravel; request belum mencapai aplikasi, sehingga pengujian route dan callback-token belum terjadi. Screenshot sebelumnya menunjukkan tunnel online, tetapi pada saat pengujian endpoint sudah offline (kemungkinan proses ngrok berhenti atau alamat tidak lagi aktif).

Tindakan berikutnya: (1) pastikan Laravel berjalan di `http://127.0.0.1:8000` dengan `php artisan serve --host=0.0.0.0 --port=8000`; (2) di terminal kedua jalankan `ngrok http 8000` dan biarkan tetap terbuka; (3) gunakan hostname HTTPS yang tercetak pada sesi ngrok aktif, jangan mengasumsikan hostname lama; (4) uji lokal `POST http://127.0.0.1:8000/webhooks/xendit` tanpa callback token dan harapkan HTTP 401 JSON dari Laravel; (5) ulangi POST yang sama ke hostname ngrok yang sedang aktif dan harapkan HTTP 401 JSON. Jangan resend callback Xendit atau membuat payout baru sebelum endpoint terverifikasi. Jangan membagikan callback token atau kredensial.


### 28 Nov 2026 — Verifikasi endpoint lokal webhook Xendit
Pada 28 Nov 2026 dilakukan `POST http://127.0.0.1:8000/webhooks/xendit` dengan header `Content-Type: application/json` dan body `{}` tanpa callback token. Hasil: `HTTP/1.1 401 Unauthorized` dengan JSON `{"message":"Unauthorized"}`. Ini hasil yang diharapkan untuk request tanpa token: Laravel menerima request pada route webhook dan proteksi autentikasi menolaknya. Dengan demikian server lokal/route dapat dijangkau dan middleware/controller token guard merespons. Ini belum membuktikan callback Xendit yang sah berhasil diproses, belum membuktikan konektivitas publik ngrok, dan belum mengubah status payout.

Langkah berikutnya: aktifkan kembali `ngrok http 8000`, ambil hostname HTTPS dari sesi yang sedang `online`, lalu kirim POST kosong yang sama ke `https://<hostname-aktif>/webhooks/xendit`. Harapan: respons JSON `401 Unauthorized` dari Laravel, bukan halaman HTML `ERR_NGROK_3200`. Setelah uji publik berhasil, baru verifikasi callback URL pada dashboard Xendit dan lakukan resend untuk disbursement pengujian yang sudah ada. Jangan membuat payout baru atau mengubah saldo/ledger manual.

Catatan keamanan: output curl berisi header `Set-Cookie`; jangan membagikan nilai cookie atau token pada log berikutnya. Nilai yang terlihat berasal dari request curl ini, tetapi tetap perlakukan cookie sebagai data sesi sensitif.


### 28 Nov 2026 — Endpoint webhook publik terverifikasi melalui ngrok
Pada 28 Nov 2026 pengguna menguji endpoint publik dengan POST ke `https://moiest-smotheredly-miriam.ngrok-free.dev/webhooks/xendit`, header `Content-Type: application/json`, dan body `{}` tanpa `x-callback-token`. Hasil: JSON `{"message":"Unauthorized"}`, HTTP 401. Ini konsisten dengan hasil uji lokal sebelumnya. Kesimpulan: tunnel ngrok aktif pada saat pengujian, request publik diteruskan ke Laravel, route webhook merespons, dan pemeriksaan token menolak request tanpa token. Ini belum membuktikan callback asli dengan token sah diproses atau status withdrawal lokal diperbarui.

Kode pada branch memvalidasi header `x-callback-token` terhadap `services.xendit.verification_token` yang dibaca dari `XENDIT_VERIFICATION_TOKEN`. Handler withdrawal memproses status `COMPLETED` dan `FAILED` serta mengandalkan external ID yang cocok dengan withdrawal lokal. Xendit mengonfirmasi di dashboard bahwa disbursement uji `WD-local-2` sebelumnya sudah berstatus `COMPLETED`, sedangkan callback sebelumnya gagal saat tunnel offline.

Langkah berikutnya: di Xendit Dashboard pilih environment Test Mode yang sama dengan disbursement uji; buka Settings > Developers > Webhook/Callbacks (label dapat berbeda), atur callback produk Disbursements ke `https://moiest-smotheredly-miriam.ngrok-free.dev/webhooks/xendit`, dan pastikan webhook verification token Xendit pada environment tersebut sama dengan nilai `XENDIT_VERIFICATION_TOKEN` di `.env` lokal tanpa membagikan nilainya. Simpan pengaturan. Setelah URL dan token terkonfirmasi, buka Log Webhook dan resend hanya callback gagal yang terkait external ID `WD-local-2`; jangan buat disbursement atau withdrawal baru. Setelah resend, verifikasi respons callback, log Laravel, status withdrawal #2, transaksi ledger, saldo, dan notifikasi.

Jangan mengubah saldo/ledger manual, approve ulang, membuat payout baru, atau melakukan refund. Jika `.env` diubah, restart proses `php artisan serve` agar konfigurasi baru terbaca.


### 28 Nov 2026 — Identifikasi jenis webhook yang sesuai dengan integrasi withdrawal
Screenshot Xendit Developer > Webhooks (Test data) pada 28 Nov 2026 menunjukkan URL ngrok `https://moiest-smotheredly-miriam.ngrok-free.dev/webhooks/xendit` telah dimasukkan pada event **Payment Links → Invoices paid**, bukan callback withdrawal. Riwayat event Payment Links memperlihatkan HTTP 200 dengan pesan `Ignored - Kode tidak dikenali`; ini adalah event pembayaran yang tidak relevan untuk withdrawal dan bukan bukti webhook disbursement berhasil.

Verifikasi source code branch `fix/security-authentication`: `WithdrawalController::approve()` mengirim pencairan melalui endpoint legacy `POST https://api.xendit.co/disbursements`. `WebhookController::handle()` mengharapkan payload legacy dengan `external_id` dan `status` pada level root serta memproses status `COMPLETED`/ `FAILED`. Dokumentasi resmi Xendit membedakan webhook legacy `disbursement` (status COMPLETED/FAILED; Indonesia) dari `payout` untuk Payouts V2, dan dari Payouts V3. Karena itu jangan gunakan event **Payouts v2** atau **Payouts v3** untuk kode yang sekarang.

Pada tampilan Webhooks terbaru, tombol **Ubah ke Versi Lama** terlihat. Langkah yang tepat adalah membuka tampilan versi lama, mencari konfigurasi **Disbursement** (Money-Out), lalu memasukkan URL aktif `https://moiest-smotheredly-miriam.ngrok-free.dev/webhooks/xendit` pada callback Disbursement dan menyimpan. Pastikan environment Test Mode. Ambil token verifikasi callback dari Xendit Test Mode dan cocokkan dengan `XENDIT_VERIFICATION_TOKEN` di `.env` lokal; jangan membagikan token. Jangan mengubah event Payment Links kecuali memang diperlukan untuk alur invoice/deposit.

Setelah callback Disbursement legacy tersimpan dan token cocok, buka Webhook Log dan resend hanya event callback disbursement gagal yang terkait withdrawal `WD-local-2` yang sudah COMPLETED. Jangan membuat payout/withdrawal baru, approve ulang, refund, atau mengubah saldo/ledger manual. Setelah resend, verifikasi status withdrawal #2, ledger withdrawal dan platform fee, `paid_at`, notifikasi, dan log Laravel.


### 28 Nov 2026 — Dashboard Xendit sudah beralih ke tampilan webhook legacy
Screenshot Xendit Developer pada 28 Nov 2026 menunjukkan halaman Webhooks kini berada pada tampilan legacy (tombol di kanan atas berubah menjadi opsi untuk kembali ke versi baru). Daftar callback legacy dan bagian Money-Out/Disbursement terlihat. Ini mengonfirmasi perpindahan tampilan berhasil, tetapi screenshot resolusi rendah sehingga URL pada field Disbursement tidak dapat diverifikasi dengan pasti.

Langkah berikutnya: pada baris **DISBURSEMENT** (legacy Money-Out; bukan Payouts V2/V3), periksa URL secara langsung dan pastikan persis `https://moiest-smotheredly-miriam.ngrok-free.dev/webhooks/xendit`, lalu klik **Test dan Simpan** untuk baris itu. Xendit mendokumentasikan tipe callback legacy `disbursement` untuk notifikasi status `COMPLETED` atau `FAILED` pada akun Indonesia. Clipkeo `WebhookController::handle()` memvalidasi header `x-callback-token`, membaca `external_id` dan `status` dari payload root, lalu mengarahkan referensi `WD-` ke handler withdrawal. Cocokkan verification token dashboard dengan `services.xendit.verification_token` di konfigurasi Laravel / `XENDIT_VERIFICATION_TOKEN` lokal. Jangan mengirim token di chat.

Setelah URL tersimpan, verifikasi melalui Webhook Log dan resend hanya event Disbursement yang memang berkaitan dengan withdrawal #2 / `WD-local-2` yang sudah berstatus Completed di Xendit. Jangan membuat transfer baru, approve ulang, refund, atau mengubah ledger/saldo manual. Setelah callback diterima, periksa status withdrawal, ledger, `paid_at`, notifikasi, dan log Laravel.
