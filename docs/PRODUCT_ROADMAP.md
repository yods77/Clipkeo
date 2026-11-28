# Clipkeo — Product Vision, UX Strategy & Development Roadmap

> Dokumen strategi produk dan backlog pengembangan jangka menengah/panjang. Dokumen ini melengkapi `docs/PROJECT_HANDOFF.md` yang menyimpan posisi teknis terakhir.
>
> **Tanggal baseline:** 2026-11-27  
> **Repository:** https://github.com/yods77/Clipkeo  
> **Branch acuan:** `fix/security-authentication`  
> **Baseline kode yang ditinjau:** commit `36d668e84a32b77adaf129e1d4dfa58150d39175`  
> **Penting:** item roadmap adalah rekomendasi/temuan audit, bukan klaim bahwa fitur sudah tersedia atau sudah diuji. Verifikasi ulang kode dan perilaku aktual sebelum implementasi.

---

## 1. Arah produk

Clipkeo dapat diposisikan sebagai marketplace campaign video pendek yang mempertemukan:

- **Publisher/Brand:** membuat campaign, menetapkan anggaran, platform, target/peraturan, dan hadiah; memantau submission serta hasil campaign.
- **Clipper/Creator:** menemukan campaign, membuat konten video, mengirim tautan dan bukti analytics, memperoleh hadiah, serta menarik saldo.
- **Administrator/Operations:** menjaga kualitas campaign dan submission, memproses deposit/withdrawal, menangani laporan, menjaga keamanan, dan memastikan keuangan dapat direkonsiliasi.

Tujuan produk bukan hanya membuat semua menu tersedia. Pengguna harus selalu memahami:
1. Apa langkah berikutnya?
2. Apa syarat dan konsekuensinya?
3. Apakah tindakan berhasil?
4. Kapan status akan berubah?
5. Siapa yang harus dihubungi jika ada masalah?

**Prinsip utama:** sederhana bagi pengguna, terukur bagi Publisher, efisien bagi Admin, dan dapat diaudit untuk seluruh proses yang menyentuh uang atau moderasi.

## 2. Peta kemampuan yang terlihat pada kode saat ini

Daftar berikut berasal dari pemeriksaan kode branch acuan. Keberadaan kode tidak otomatis berarti setiap alur sudah lulus pengujian end-to-end.

### Flutter mobile app
File/screen yang terlihat:
- Onboarding, welcome, login, register, OTP, lupa password, setup PIN.
- Dashboard, pencarian campaign, detail campaign.
- Form submission dengan tautan video dan upload bukti analytics.
- Riwayat misi dengan tab pending/approved/rejected.
- Wallet, metode payout, input dan hasil withdrawal.
- Profile, linked social account, notification, leaderboard.

### Laravel web/backend
Route dan controller yang terlihat:
- Landing page, login Google, profile, linked accounts, dashboard.
- Publisher campaign/bounty, daftar submission, approval/rejection, analytics.
- Deposit, withdrawal, payout methods, wallet/transactions, invoice, notification.
- Admin dashboard, manajemen user, moderasi campaign, monitoring submission, admin takeover, deposit/withdrawal approval, analytics.
- API autentikasi, campaign, profile, wallet, notification, leaderboard, submission.
- Xendit webhook dan upload bukti analytics ke layanan penyimpanan eksternal.

### Integrasi yang terlihat
- Google Sign-In.
- Laravel Sanctum untuk API mobile.
- Xendit untuk proses pembayaran/penarikan.
- Cloudinary untuk media/bukti analytics.
- Firebase Cloud Messaging untuk notifikasi push.
- Platform konten yang dikenali oleh alur submission: TikTok, Instagram, dan YouTube.

## 3. Temuan audit awal yang perlu diprioritaskan

Temuan ini adalah pembacaan kode branch acuan, bukan hasil eksploitasi atau pengujian produksi. Konfirmasi ulang pada branch dan working tree terbaru sebelum mengubah kode.

### P0 — Integritas saldo dan transaksi
1. Database memiliki ENUM transaksi: `deposit`, `withdrawal`, `bounty_hold`, `bounty_prize`, `bounty_refund`, `platform_fee`. Beberapa controller dan wallet view masih memakai istilah lama seperti `reward`, `payout`, dan `refund`. Seluruh pencatatan, filter, label, dan arah nominal perlu distandarkan.
2. Alur withdrawal dari Flutter API dan website memakai controller yang berbeda. API wallet mencatat transaksi ketika request diajukan; website mencatat transaksi lain ketika admin memproses pencairan. Telusuri apakah terjadi dua baris ledger untuk satu withdrawal, pastikan satu lifecycle dan satu sumber kebenaran.
3. API wallet dashboard mengirim `service_fee_pct = 0.05`, sedangkan controller withdrawal yang diperiksa menghitung fee 10%. Pastikan fee yang ditampilkan, disetujui secara bisnis, dan benar-benar dipotong selalu sama. Jangan mengubah tarif tanpa keputusan pemilik produk.
4. Withdrawal dan perubahan saldo harus atomik, menggunakan database transaction dan row locking yang tepat agar dua request bersamaan tidak menyebabkan saldo negatif atau pencairan ganda.
5. Webhook Xendit harus idempotent untuk setiap status/event. Verifikasi signature/token, transisi status yang sah, pencocokan reference, dan refund satu kali. Deposit sudah memiliki pemeriksaan status sukses; withdrawal perlu pengujian duplikasi dan transisi status yang setara.
6. Buat satu lifecycle transaksi yang jelas: requested → processing → success/failed/reversed (sesuaikan domain). Jangan mengubah status final secara sembarang.
7. Pertahankan unique reference ID yang sudah ditambahkan melalui migration. Tambahkan idempotency key untuk aksi yang dapat dikirim ulang, terutama deposit, withdrawal, approval, dan payout.
8. Uji konsistensi wallet balance terhadap ledger dan saldo dana campaign yang di-hold. Rencanakan ledger debit/kredit yang immutable sebagai evolusi terkontrol; jangan mengganti skema finansial tanpa rekonsiliasi dan migration plan.

### P0 — Security dan kontrol akses
1. Audit semua route admin dengan `php artisan route:list`: setiap aksi approve/reject, takeover, moderasi, manajemen user, dan transaksi harus berada di middleware/Policy admin yang tepat. Kode route yang diperiksa memiliki deklarasi approval/rejection withdrawal berulang; pastikan tidak ada route yang efektif tanpa middleware admin dan hapus duplikasi setelah test.
2. Audit endpoint API berdasarkan role dan ownership. Pengguna hanya boleh membaca/mengubah resource miliknya; admin-only action harus memiliki role/policy server-side, bukan sekadar menyembunyikan tombol UI.
3. Endpoint submission menerima URL video dari pengguna dan melakukan resolusi redirect untuk link pendek. Terapkan allowlist domain, pembatasan redirect, blokir IP privat/loopback/link-local pada setiap hop, timeout, serta perlindungan SSRF/DNS rebinding.
4. Validasi file analytics berdasarkan MIME/content, ukuran, ekstensi, dan izin file. Pertimbangkan private object storage dan signed URL untuk bukti yang tidak boleh publik.
5. Pastikan PIN memiliki throttling, lockout bertahap, audit event, dan tidak pernah masuk log. Terapkan rate limit pada autentikasi, reset OTP, upload, dan aksi keuangan.
6. Verifikasi webhook dengan secret yang benar dan jangan mencatat token, credential, data rekening lengkap, atau informasi sensitif ke log.
7. Audit penggunaan environment/configuration: tidak ada secret di repository atau client app. URL API dan Google client configuration harus memakai environment/build flavor, bukan IP lokal hard-coded untuk build produksi.

### P1 — Kualitas pengalaman pengguna
1. Flutter NotificationScreen memiliki aksi “tandai semua dibaca” yang pada kode acuan hanya mengubah state lokal; sambungkan ke endpoint read-all dan sinkronkan unread count.
2. Beberapa service Flutter mengembalikan list/map kosong saat request gagal. Bedakan kondisi loading, data kosong, gagal jaringan, sesi habis, dan server error agar saldo Rp0 tidak disalahartikan sebagai kegagalan API.
3. Samakan istilah status antara aplikasi, website, API, dan database. Contoh: campaign pending review, active, rejected, completed; submission pending, approved/partial/paid, rejected; withdrawal pending, processing, completed, rejected.
4. Tambahkan pagination/infinite scroll dan filter yang konsisten untuk daftar besar. API BountyController sudah memakai pagination 10 item; pertahankan kontrak pagination untuk layar campaign.
5. Pastikan seluruh form memberi validasi dekat dengan field, ringkasan biaya/fee sebelum konfirmasi, progress upload, retry yang aman, dan pencegahan double-tap/double-submit.

### P1 — Konsistensi API
- `GET /api/campaigns` terlihat dideklarasikan lebih dari sekali (closure dan controller). Pastikan route aktif yang dimaksud, hapus definisi duplikat, dan lindungi kontrak API dengan tests.
- Dokumentasikan request/response, status code, pagination, error format, dan versi API. Hindari mengubah response Flutter tanpa migrasi client yang terencana.

## 4. Pengalaman ideal per peran

### A. Clipper / Creator — mobile-first
**Alur ideal:** daftar/login → onboarding singkat → lengkapi profil dan akun sosial → temukan campaign → pahami aturan/hadiah → submit → pantau status dan views → terima reward → tarik saldo.

Rekomendasi:
- Home yang langsung menampilkan campaign relevan, saldo/reward, status submission terakhir, dan satu CTA utama.
- Pencarian dengan kategori, platform (TikTok/Instagram/YouTube), jenis reward, deadline, budget tersisa, dan kata kunci.
- Campaign card memperlihatkan brand, platform, estimasi reward/range, deadline, sisa budget, dan syarat penting tanpa membuka detail.
- Detail campaign memiliki ringkasan “cara ikut”, aturan, contoh konten, target views/tier, estimasi reward, deadline, dan peringatan kelayakan.
- Checklist sebelum submit: akun sosial yang tepat, tautan valid, video milik sendiri, bukti analytics, dan campaign masih aktif.
- Upload dengan progress, retry, dan draft sementara jika jaringan putus.
- Timeline submission: terkirim → sedang ditinjau → disetujui/ditolak → pembayaran/akumulasi reward. Sertakan alasan penolakan yang spesifik dan jalur banding jika kebijakan produk mengizinkan.
- Wallet memisahkan saldo tersedia, saldo tertahan, pendapatan, biaya, withdrawal, dan status pencairan.
- Tampilkan fee, nominal bersih diterima, rekening tujuan, dan estimasi waktu sebelum PIN/konfirmasi akhir.
- Riwayat transaksi dapat difilter dan setiap item punya detail/reference yang bisa disalin.
- Notifikasi yang dapat diatur per kategori; deep link langsung ke campaign/submission/transaksi terkait.
- Pusat bantuan dengan FAQ, panduan bukti analytics, dan tiket bantuan.

### B. Publisher / Brand — web-first, mobile-friendly
**Alur ideal:** onboarding brand → isi profil → buat campaign → lihat simulasi biaya/budget → deposit/hold dana → moderasi campaign → terima submission → approve/reject dengan alasan → pantau performa → campaign selesai dan laporan.

Rekomendasi:
- Campaign creation wizard bertahap: tujuan, platform, brief, syarat, budget, strategi reward, deadline, jumlah pemenang.
- Preview campaign seperti yang akan dilihat Clipper sebelum publish.
- Kalkulator budget: estimasi jumlah creator/hasil, tier payout, biaya platform, saldo yang akan di-hold, dan sisa dana.
- Template campaign agar Publisher tidak mengisi brief dari nol setiap kali.
- Dashboard campaign: views, submission, approval rate, spend, cost per result, remaining budget, deadline, dan performa per platform.
- Submission inbox dengan filter status, platform, views, waktu, dan risiko; bulk approve hanya untuk kasus yang aman dan harus memiliki preview serta konfirmasi.
- Alasan reject terstruktur agar Clipper mendapat feedback yang jelas.
- Notifikasi ketika campaign disetujui, submission masuk, budget hampir habis, atau campaign selesai.
- Export laporan CSV/PDF untuk rekonsiliasi internal.
- Riwayat deposit, invoice, dan penggunaan budget per campaign.

### C. Administrator / Operations — efisiensi dan audit
Modul yang terlihat saat ini sudah mencakup dashboard, user management, campaign moderation, monitoring submission, takeover, dan operasi deposit/withdrawal. Pengembangan berikutnya harus membuat semuanya menjadi satu alur kerja yang dapat diprioritaskan.

Rekomendasi:
- **Unified Operations Inbox:** satu antrean untuk campaign review, submission dispute/flag, deposit, withdrawal, laporan user, dan tiket bantuan.
- Filter cepat: status, umur antrean, nominal, campaign, platform, user, risiko, dan penanggung jawab.
- SLA/aging indicator: item yang paling lama menunggu dan yang mendekati batas layanan.
- Detail case dengan semua bukti, riwayat perubahan, percakapan, saldo/ledger terkait, dan keputusan sebelumnya dalam satu layar.
- Bulk actions untuk operasi non-finansial yang risikonya rendah; tampilkan preview, jumlah item, alasan, dan hasil per item. Untuk transfer/keputusan finansial besar, gunakan maker-checker (pembuat dan penyetuju berbeda).
- Role-based access control: Super Admin, Finance, Moderator, Support, Analyst. Batasi data dan aksi sesuai kebutuhan kerja.
- Audit log yang append-only: siapa melakukan apa, kapan, dari mana, alasan, nilai sebelum/sesudah, dan correlation/reference ID.
- User 360: profil, status verifikasi, campaign, submission, transaksi, laporan, riwayat tindakan, catatan support, dan status banding.
- Alat suspend/unsuspend dengan alasan, durasi, dan notifikasi. Hindari hard delete untuk data yang berkaitan dengan keuangan/audit.
- Rekonsiliasi harian: saldo wallet, dana hold campaign, deposit gateway, withdrawal gateway, fee, refund, dan selisih.
- Export terbatas berdasarkan role, dengan masking data rekening dan jejak unduhan.
- Dashboard kesehatan sistem: error API, antrean gagal, webhook gagal, upload gagal, dan pembayaran yang macet.

## 5. Prinsip UI/UX dan design system

### Prinsip lintas aplikasi
- Satu design system: warna status, tipografi, spacing, icon, button, field, modal, table, dan card konsisten antara halaman terkait.
- Mobile-first untuk Clipper; web dashboard yang responsif untuk Publisher dan Admin.
- Prioritaskan keterbacaan dan hierarki; animasi hanya membantu orientasi, bukan memperlambat pekerjaan.
- Bahasa Indonesia yang sederhana dan konsisten; jelaskan istilah teknis seperti hold, payout, CPM, dan tier dengan tooltip/help.
- Setiap layar harus punya empat keadaan: loading, success/data, empty, dan error/retry. Tambahkan offline/session-expired bila relevan.
- Form panjang dibuat bertahap, menyimpan draft, menunjukkan progress, dan mencegah data hilang.
- Tindakan berisiko (hapus, reject, transfer, refund) harus menampilkan dampak, alasan, dan konfirmasi yang jelas.
- Aksesibilitas: kontras memadai, ukuran teks terbaca, target sentuh memadai, label screen reader, dan tidak mengandalkan warna saja untuk status.
- Konsisten pada format IDR, tanggal, zona waktu, angka views, dan format nama platform.

### Checklist penerimaan UI
- Pengguna baru dapat menyelesaikan tugas utama tanpa bantuan staf.
- Status dan langkah selanjutnya dapat dipahami dalam sekali lihat.
- Tidak ada tombol yang tampak aktif tetapi tidak melakukan aksi.
- Kesalahan memberi solusi yang dapat dilakukan, bukan hanya “terjadi kesalahan”.
- Aksi finansial menampilkan nilai bruto, fee, nilai bersih, dan tujuan.
- Data kosong berbeda jelas dari data yang gagal dimuat.
- Layout diuji pada ukuran layar kecil, tablet, dan desktop yang relevan.

## 6. Arsitektur dan kesiapan skala

**Rekomendasi awal:** pertahankan Laravel sebagai modular monolith + API dan Flutter sebagai client. Jangan buru-buru memecah menjadi microservices sebelum metrik menunjukkan kebutuhan.

Area yang perlu disiapkan:
1. **Domain boundaries:** Auth/Identity, Campaign, Submission/Moderation, Wallet/Ledger, Payments, Notifications, Analytics, Support/Admin.
2. **Financial correctness:** satu service/domain layer untuk mutasi saldo; transaksi database, row lock, idempotency, immutable ledger, reconciliation, dan test concurrency.
3. **API:** versioning, pagination, validation, resource DTO/transformer, consistent error envelope, rate limit, dan dokumentasi OpenAPI.
4. **Database:** index untuk foreign key dan filter yang sering dipakai (status, user_id, campaign_id, created_at, reference); hindari query N+1; gunakan pagination dan query agregasi yang terukur.
5. **Background jobs:** upload processing, notifikasi, email, webhook follow-up, report generation, dan analytics aggregation diproses melalui queue dengan retry/backoff dan dead-letter monitoring.
6. **Media storage:** object storage terkelola, private proof, signed URL, thumbnail/transcoding bila diperlukan, retensi dan penghapusan terjadwal.
7. **Cache:** kategori, campaign discovery, leaderboard, dan statistik yang aman di-cache dengan invalidation yang jelas. Jangan cache saldo final tanpa strategi konsistensi.
8. **Observability:** structured logs, correlation ID, error tracking, latency, queue depth, webhook outcomes, payment reconciliation, crash reporting mobile, dan alert.
9. **Delivery:** CI untuk PHP tests/static analysis dan Flutter analyze/test/build; staging environment; release checklist; backup otomatis; restore drill.
10. **Security/privacy:** least privilege, secrets management, encryption in transit/at rest, retention policy, export/delete account workflow, privacy notice, consent, dan incident response.

## 7. Tahapan roadmap yang disarankan

Urutan ini adalah rekomendasi prioritas, bukan janji jadwal. Pekerjaan aktual ditentukan setelah audit branch terbaru dan keputusan bisnis.

### Tahap 0 — Stabilitas, uang, dan keamanan (P0)
- [ ] Rekonsiliasi transaksi ENUM dengan semua controller, query, Blade, API, dan Flutter.
- [ ] Satukan lifecycle withdrawal web/API dan hilangkan transaksi ganda.
- [ ] Putuskan satu tarif fee resmi dan samakan API, UI, invoice, serta perhitungan backend.
- [ ] Uji deposit/withdrawal/webhook untuk retry, duplicate event, timeout, dan status tidak berurutan.
- [ ] Pastikan mutasi saldo atomic, terkunci, dan idempotent.
- [ ] Audit middleware/Policy seluruh route admin dan ownership seluruh API.
- [ ] Bersihkan route duplikat setelah route:list dan regression test.
- [ ] Audit SSRF pada redirect video link dan keamanan file upload.
- [ ] Buat test keuangan dan otorisasi yang belum ada.
- **Gate:** tidak ada saldo ganda/hilang, payout ganda, akses lintas user, atau endpoint admin yang dapat diakses role biasa dalam test.

### Tahap 1 — Core user journey dan UX launch-ready (P1)
- [ ] Uji onboarding end-to-end untuk Clipper dan Publisher.
- [ ] Sempurnakan campaign discovery, filter, detail, dan aturan reward.
- [ ] Sempurnakan submission upload/progress/retry/status/rejection reason.
- [ ] Sempurnakan campaign creation wizard dan Publisher inbox.
- [ ] Perbaiki notification read/read-all dan deep link.
- [ ] Bedakan error, empty, loading, offline, dan session expired di Flutter.
- [ ] Sempurnakan wallet statement dan preview fee/withdrawal.
- [ ] Pusat bantuan/FAQ dan feedback.
- **Gate:** pengguna baru dari kedua role dapat menyelesaikan alur utama tanpa intervensi admin, dengan semua edge case utama diuji.

### Tahap 2 — Admin operations dan trust (P1/P2)
- [ ] Unified operations inbox dengan filter, SLA, assignment, dan bulk action yang aman.
- [ ] RBAC admin berdasarkan tugas.
- [ ] Audit trail untuk keputusan moderasi dan keuangan.
- [ ] User 360, report/appeal/dispute workflow, dan support tickets.
- [ ] Rekonsiliasi harian dan export laporan.
- [ ] Fraud/risk signals: duplicate video, account ownership, abnormal views, pola submission, dan pola rejection.
- **Gate:** admin dapat memproses volume meningkat tanpa kehilangan jejak keputusan dan tanpa mengorbankan kontrol keuangan.

### Tahap 3 — Scale dan reliability (P2)
- [ ] CI/CD, staging, automated regression, dan release rollback plan.
- [ ] Queue, retry, monitoring, alert, dan dead-letter handling.
- [ ] Database/index/query review dan load test.
- [ ] Backup otomatis dan uji pemulihan.
- [ ] Private media storage, CDN, dan observability.
- [ ] Crash/error analytics untuk app dan web.
- **Gate:** sistem lulus target beban yang disepakati dan tim dapat mendeteksi serta memulihkan insiden.

### Tahap 4 — Growth dan optimasi bisnis (P3)
- [ ] Referral/creator invitation dengan anti-abuse.
- [ ] Campaign templates dan rekomendasi campaign yang relevan.
- [ ] Personalized campaign feed berdasarkan platform/minat/performa.
- [ ] Publisher performance insights dan benchmark.
- [ ] Gamification yang transparan dan tidak mendorong perilaku spam.
- [ ] Program loyalitas/tiers setelah ekonomi reward tervalidasi.
- **Gate:** fitur growth didasarkan pada data aktivasi, retention, repeat campaign, dan unit economics—bukan sekadar menambah menu.

## 8. Metrik produk dan operasi

Mulai ukur baseline sebelum menetapkan target angka.

### Clipper
- Registration → profile completion → linked account → first campaign view → first submission.
- Submission success rate dan waktu dari submit sampai keputusan.
- Persentase submission ditolak dan alasan dominan.
- Waktu reward menjadi saldo tersedia dan waktu withdrawal selesai.
- Retention creator 7/30 hari dan creator aktif bulanan.

### Publisher
- Registration → campaign created → deposit/hold → campaign approved → first submission.
- Waktu campaign review dan waktu submission review.
- Campaign completion rate, repeat campaign rate, spend, cost per result, dan remaining budget.
- Publisher retention dan support contact rate.

### Admin/Finance
- Backlog dan median/P95 waiting time per queue.
- Approval/rejection throughput per admin.
- Webhook success/retry/failure rate.
- Reconciliation difference, duplicate transaction, payout failure, dan refund time.
- Fraud flags, false-positive rate, appeal overturn rate.

### Platform
- API availability, latency P50/P95/P99, error rate.
- Mobile crash-free sessions, upload success, login success.
- Queue age/failures, DB slow queries, storage usage, backup/restore success.

## 9. Keputusan bisnis yang harus ditetapkan sebelum implementasi terkait

Jangan menebak atau mengubah kebijakan ini hanya dari kode:
- Tarif fee deposit/withdrawal/platform dan pihak yang menanggungnya.
- Kapan budget dianggap terkunci, dapat dikembalikan, atau tidak dapat dikembalikan.
- Formula reward CPM/tier, rounding, batas views, dan jadwal pembayaran.
- SLA moderasi campaign dan submission.
- Bukti analytics yang diterima dan kebijakan verifikasi tiap platform.
- Kebijakan fraud, banding, suspend, dan dispute.
- Kebutuhan verifikasi identitas/brand dan persyaratan kepatuhan pembayaran.
- Kebijakan umur pengguna, privasi, retensi bukti, dan penghapusan akun.
- Target pengguna, target transaksi, target beban serentak, dan anggaran infrastruktur.

## 10. Cara AI harus bekerja saat melanjutkan proyek

Saat percakapan baru dimulai, AI harus:

1. Membaca `docs/PROJECT_HANDOFF.md` dan dokumen roadmap ini sebelum memberi saran teknis.
2. Memeriksa branch, commit terbaru, file yang berubah, dan diff yang diberikan pengguna. Jangan menganggap working tree bersih.
3. Membedakan tiga status: **sudah ada di kode**, **sudah diuji**, dan **baru rekomendasi**.
4. Menjelaskan posisi terakhir dan risiko yang masih terbuka.
5. Tetap memberikan rekomendasi strategis: minimal prioritas teknis terdekat dan satu peluang peningkatan UX/operasional yang relevan.
6. Mengurutkan saran berdasarkan dampak pengguna, risiko uang/keamanan, effort, dan dependensi—bukan sekadar menambah fitur.
7. Mengusulkan solusi yang sesuai arsitektur sekarang; hindari rewrite/microservices tanpa alasan terukur.
8. Untuk perubahan, audit file terkait dan dampaknya lintas Flutter, API, web, database, dan admin sebelum mengedit.
9. Sertakan acceptance criteria, test plan, dan risiko migrasi untuk setiap perubahan bermakna.
10. Jangan menjalankan perubahan destruktif, menghapus data, mengubah tarif bisnis, atau merge ke `main` tanpa persetujuan eksplisit.
11. Setelah pekerjaan selesai, perbarui handoff dengan file yang berubah, commit SHA, test yang benar-benar dijalankan, hasilnya, dan next steps.
12. Jika hasil uji lokal belum diberikan, tandai belum diverifikasi; jangan mengklaim berhasil.

### Prompt untuk percakapan baru

> Lanjutkan Clipkeo dari repository https://github.com/yods77/Clipkeo. Baca `docs/PROJECT_HANDOFF.md` dan `docs/PRODUCT_ROADMAP.md` pada branch `fix/security-authentication`. Audit branch dan perubahan lokal terbaru sebelum mengubah kode. Lanjutkan pekerjaan terakhir, tetapi tetap bertindak sebagai partner product engineering: evaluasi UX Clipper, Publisher, Admin, keamanan, keuangan, skalabilitas, dan rekomendasikan prioritas pengembangan berikutnya. Bedakan fitur yang sudah ada, yang sudah diuji, dan yang baru berupa rekomendasi. Jangan merge ke `main`, jangan menghapus perubahan lokal, dan jangan mengubah kebijakan finansial tanpa persetujuan.

---

## 11. Catatan pembaruan

Tambahkan entri terbaru di bawah ini setiap kali roadmap berubah atau keputusan prioritas dibuat.

| Tanggal | Perubahan | Status |
|---|---|---|
| 2026-11-27 | Roadmap awal dibuat dari pemeriksaan struktur Flutter/Laravel, route, controller, model, dan UI Admin pada branch `fix/security-authentication`. | Baseline; perlu validasi dengan working tree lokal dan pengujian end-to-end |
| 2026-11-27 | Financial/security hardening committed: canonical ledger types, atomic campaign refund and web/API withdrawal debits, server-side withdrawal PIN, webhook idempotency, route hardening, and regression tests. | Code committed to `fix/security-authentication`; tests and Flutter analysis not yet executed. Next: Xendit approval idempotency and one withdrawal-ledger lifecycle. |
| 2026-11-27 | Added per-user API withdrawal idempotency, persisted Flutter retry keys, pending-attempt recovery across token renewal/logout, current withdrawal status in result UI, and backend/mobile regression tests. | Committed to `fix/security-authentication`; implementation tests pending local execution. API now requires `Idempotency-Key`, so backend and updated mobile app must be released together. |
| 2026-11-28 | Manual end-to-end Admin Takeover test with a second Clipper account: submission stayed pending for at least 5 minutes, appeared in Admin Review & Takeover, was approved, and balance/reward/ledger were checked for duplicate payout. | All 6 steps reported successful by the user. Exact submission ID and monetary figures were not included; detailed financial reconciliation evidence should be retained for future regression checks. |


## 12. Progress Tracker — 2026-11-27

Checklist ini membedakan pekerjaan yang sudah dibuktikan lewat pengujian lokal, implementasi yang sudah ada tetapi masih menunggu verifikasi, dan pekerjaan yang belum dilakukan.

**Legenda:** [x] selesai dan ada bukti; [~] implementasi/tes sebagian, belum menjadi gate lulus; [ ] belum selesai.

### A. P0 — Financial Integrity & Security

- [x] Perbaikan refund Admin untuk campaign uji ID 1: campaign ditolak, saldo Publisher kembali Rp1.000.000, dan satu transaksi `bounty_refund` tercatat. Jangan ulangi refund pada campaign uji tersebut.
- [~] Normalisasi tipe ledger canonical (`bounty_prize`, `bounty_refund`, dan tipe ENUM lain) serta penyesuaian arah pemasukan/pengeluaran berdasarkan pemilik transaksi sudah ditambahkan ke branch; rekonsiliasi seluruh jalur dan wallet masih perlu diselesaikan.
- [~] Refund campaign menggunakan transaksi database/row lock dan mengosongkan `remaining_budget` pada alur yang sudah diperbaiki; regression test lengkap masih perlu dijalankan setelah sinkronisasi lokal.
- [~] Withdrawal PIN server-side, validasi ownership payout method, atomic debit, dan proteksi webhook idempotency sudah diimplementasikan pada branch; verifikasi menyeluruh pada environment lokal/staging masih diperlukan.
- [~] API withdrawal idempotency per user dan retry recovery di Flutter sudah ditambahkan. Backend dan Flutter harus dirilis berpasangan karena API mewajibkan header `Idempotency-Key`; test pada versi kode terbaru masih harus dijalankan.
- [ ] Idempotency approval transfer Xendit oleh Admin dan satu lifecycle ledger withdrawal yang konsisten untuk Web/API.
- [ ] Rekonsiliasi saldo, ledger, fee, status withdrawal, dan hasil webhook untuk semua skenario sukses/gagal/duplikat.
- [ ] Audit menyeluruh SSRF pada resolver URL video, validasi file upload, webhook secret/signature, dan seluruh route/ownership API.
- [ ] Menetapkan serta menguji satu kebijakan fee yang sama pada API, UI, invoice, dan backend; jangan mengubah tarif bisnis tanpa persetujuan.

### B. P1 — Core User Journey

**Clipper / Android**
- [x] Instalasi aplikasi Android, Google Login, persistensi sesi, dashboard, profile, wallet kosong, dan halaman submissions pernah diuji dan dilaporkan berhasil.
- [x] Submission #1 pada database lokal berstatus `approved`, memiliki 10.000 views, `last_paid_views=10000`, `prize_amount=Rp50.000`, dan `approved_at` terisi.
- [~] Migration `video_id` telah ditambahkan ke branch untuk memperbaiki query submission; eksekusi migration dan pengujian ulang pada database lokal harus dicatat setelah diverifikasi.
- [~] Upload bukti analytics dan tampilan submission pada alur Publisher/Admin telah dilaporkan diuji; retry upload, error state, dan seluruh edge case belum dinyatakan lulus.
- [ ] Rekonsiliasi saldo Clipper dan transaksi reward untuk Submission #1.
- [ ] Uji pencarian/filter/detail campaign dengan data campaign aktif yang tersedia.
- [ ] Uji wallet statement, withdrawal, retry setelah timeout, token renewal, dan status Xendit end-to-end.

**Publisher / Web**
- [x] Publisher berhasil login dan membuat campaign uji; validasi field wajib berjalan.
- [x] Alur review Publisher dan reward Submission #1: status approved, saldo Clipper bertambah Rp50.000, dua transaksi ledger bounty_prize masing-masing Rp50.000 sukses, dan remaining budget Campaign #2 berkurang menjadi Rp950.000.
- [ ] Uji campaign creation, hold budget, approval/rejection, pembatalan/refund, dan laporan performa secara end-to-end dengan data uji baru yang aman.
- [ ] Uji filter/inbox submission, alasan penolakan, dan seluruh state loading/empty/error.

**Admin / Web**
- [x] Admin berhasil login dan membuka halaman Admin.
- [x] Aksi Tolak & Refund pada campaign uji ID 1 pernah berhasil satu kali; jangan diulang.
- [~] Route hardening dan regression tests otorisasi Admin sudah ditambahkan ke branch; audit route dan test versi terbaru masih diperlukan.
- [x] Uji manual end-to-end Admin Takeover dengan akun Clipper kedua: submission pending, Publisher tidak memproses, tunggu minimal 5 menit, Admin Review & Takeover, approve, lalu periksa saldo/reward/ledger untuk mencegah payout ganda (dilaporkan berhasil pengguna pada 2026-11-28).
- [ ] Uji persetujuan deposit/withdrawal, audit trail, dan pembatasan akses role biasa.

### C. Test automation dan kualitas aplikasi

- [x] Baseline Laravel sebelumnya: 64 tests passed dan 249 assertions sebelum implementasi API withdrawal idempotency terbaru.
- [~] Test baru untuk withdrawal approval/webhook, ownership, PIN, normalisasi tipe transaksi, API idempotency, dan schema submission sudah ditambahkan/diubah di branch; jalankan ulang seluruh suite pada HEAD terbaru.
- [ ] Jalankan seluruh `php artisan test`, lint PHP yang berubah, `flutter analyze`, `flutter test`, dan `flutter build apk --debug`; catat output aktual.
- [ ] Uji staging untuk timeout setelah commit, retry dengan idempotency key sama, key/payload berbeda, PIN salah/benar, webhook duplikat, dan callback status tidak berurutan.

### D. P2 — Scale & Reliability

- [ ] CI/CD dan staging environment dengan release/rollback checklist.
- [ ] Load test API dan database, query/index review, serta uji konkurensi mutasi saldo.
- [ ] Backup otomatis dan restore drill.
- [ ] Monitoring API, queue, webhook, upload, dan crash/error Flutter.
- [ ] Private media storage, signed URL, dan kebijakan retensi bukti analytics.

### E. P3 — Growth

- [ ] Referral dan creator invitation dengan anti-abuse.
- [ ] Campaign templates dan rekomendasi campaign untuk Publisher.
- [ ] Personalized campaign feed.
- [ ] Publisher performance insights dan benchmark.
- [ ] Gamification dan loyalty setelah unit economics tervalidasi.

### F. Pengujian aktif berikutnya

1. [x] Rekonsiliasi Submission #1 selesai: Campaign #2 `reward_amount` Rp1.000.000, `remaining_budget` Rp950.000; Clipper ID 1 wallet Rp50.000; Publisher ID 2 wallet Rp0; transaksi bounty_prize ID 4 dan 5 masing-masing Rp50.000 dan sukses.
2. [~] Audit semantik `payout_status`: nilai `partial` pada Submission #1 berasal dari kondisi sisa budget campaign (`remaining_budget > 100`), bukan karena reward Rp50.000 belum dibayarkan. Jangan ubah aturan reward; evaluasi penamaan/status UI secara terpisah.
3. [ ] Pastikan migration penambah `approved_at` ikut tercatat dalam repository dan migration video_id berstatus benar pada database lokal.
4. [x] Admin Takeover selesai diuji manual pada 2026-11-28 dengan akun Clipper kedua; keenam langkah dilaporkan berhasil, termasuk pemeriksaan saldo, reward, dan transaksi agar tidak ada payout ganda. Jangan mengulang approval pada submission yang sudah final.
5. [ ] Lanjutkan pengujian withdrawal Web/API dan simulasi webhook Xendit pada sandbox/mock atau environment test; pastikan jika transfer Xendit gagal, seluruh fee admin 10% dikembalikan sesuai kebijakan Clipkeo. Jangan gunakan transfer uang sungguhan tanpa persetujuan.

**Aturan pencatatan:** status [x] hanya boleh dipakai untuk pekerjaan yang benar-benar telah diuji/dibuktikan. Implementasi yang baru ada di branch tetapi belum dijalankan tetap [~]. Hasil lokal yang belum dilaporkan tidak boleh dianggap lulus.
