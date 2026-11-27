# Clipkeo — Project Handoff & Status

> Dokumen ini adalah catatan induk untuk melanjutkan pengembangan dan pengujian Clipkeo lintas percakapan. Dokumen ini menyimpan posisi teknis terakhir. Untuk visi produk, evaluasi UX, rekomendasi fitur, prioritas skala, dan cara AI memberi saran proaktif, baca juga `docs/PRODUCT_ROADMAP.md`. Perbarui keduanya saat ada perubahan kode, keputusan produk, atau hasil pengujian baru.
>
> **Terakhir diperbarui:** 2026-11-27  
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
