# PROJECT_CONTEXT: Sistem Manajemen PKL (CodeIgniter 4)

> **Untuk Claude yang membaca file ini:** ini adalah ringkasan konteks proyek dari percakapan sebelumnya antara pemilik proyek dan Claude lain. Anggap isinya sebagai kebenaran proyek saat ini. Balas dalam **Bahasa Indonesia**, santai tapi jelas. Kalau ada hal yang tidak tertulis di sini, tanyakan dulu, jangan menebak. Baca bagian **0. Aturan kerja** sebelum mengubah apa pun.

---

## 0. Aturan kerja (penting)

1. Pembagian tugas: pemilik proyek mengerjakan **Controllers, Models, Database (migration/seeder), Routes**. Teman (pemakai file ini) mengerjakan **tampilan**: `app/Views/` dan `public/` (CSS, gambar, JS).
2. **Jangan mengubah nama variabel** yang dikirim Controller ke View (daftarnya ada di bagian 6). Kalau tampilan butuh data baru, minta pemilik proyek menambahkannya di Controller.
3. **Jangan menghapus kode PHP di view** (`<?= ... ?>`, `<?php foreach ... ?>`). Bagian itu menampilkan data. Boleh dipindah posisinya, jangan dibuang.
4. Setiap `<form method="post">` **wajib** memuat `<?= csrf_field() ?>` (CSRF aktif).
5. Teks yang berasal dari database/input **wajib** dibungkus `esc(...)`.
6. **Jangan mengubah migration yang sudah di-merge.** Perubahan struktur database = buat migration baru.
7. Kerja di branch sendiri (mis. `fitur/tampilan`), gabungkan lewat Pull Request. Jangan push langsung ke `main`.
8. `layout.php` adalah file bersama: jangan diedit bersamaan dengan orang lain.
9. Teks antarmuka memakai Bahasa Indonesia.
10. **Jangan mengubah** `action` form absen (`attendance/checkin`, `attendance/checkout`) dan jangan mengganti id `#geo-box` / `#geo-status`: JavaScript lokasi (`public/js/geo-attendance.js`) mencarinya lewat pola itu dan menambahkan `lat`, `lng`, `accuracy` ke form secara otomatis. Jangan menambah input bernama itu secara manual.

---

## 1. Ringkasan proyek

Aplikasi web untuk mengelola kegiatan siswa selama PKL (Praktik Kerja Lapangan).

**Peran (role):**
- `pembimbing`: mengelola siswa bimbingannya, tempat PKL, menyetujui jurnal dan izin, menilai, melihat laporan.
- `siswa`: absen masuk/pulang, menulis jurnal + upload bukti, mengajukan izin, melihat progres dan nilai.

**Fitur:** login, data siswa, data tempat PKL (lokasi dikunci pembimbing di peta), absensi (wajib lokasi GPS; jarak ke lokasi PKL dicatat, pembatasan radius bisa diaktifkan lewat konstanta), jurnal harian + upload bukti, pengajuan izin (kalau disetujui otomatis tercatat di absensi), monitoring progres (berdasar tanggal mulai-selesai), penilaian, dashboard pembimbing, rekap laporan (cetak + unduh CSV).

**Catatan asal-usul:** spesifikasi awal memakai Laravel, lalu diganti ke CodeIgniter 4. Kode inti ditulis oleh Claude dan sudah dicoba pemilik proyek: login, dashboard, dan import database berjalan.

---

## 2. Tech stack

| Bagian | Versi / keterangan |
|---|---|
| Framework | CodeIgniter 4.7.4 |
| PHP | 8.2.12 (XAMPP, Windows) |
| Database | MySQL/MariaDB (XAMPP), nama database `db_pkl` |
| UI | Bootstrap 5.3.3 lewat CDN (di `layout.php`) |
| Editor | VS Code, terminal PowerShell |
| Server dev | `php spark serve` di http://localhost:8080 |

---

## 3. Setup di komputer baru (setelah `git clone`)

```bash
composer install            # wajib: folder vendor/ tidak ada di GitHub
copy .env.example .env      # kalau .env belum ada
```

Isi `.env` (pastikan baris tidak diawali `#`):
```
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
app.appTimezone = 'Asia/Jakarta'

database.default.hostname = localhost
database.default.database = db_pkl
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Lalu: nyalakan MySQL di XAMPP, buat database kosong `db_pkl` di phpMyAdmin.

**Pilih salah satu cara mengisi database:**

- **A. Dari nol:** `php spark migrate` lalu `php spark db:seed InitSeeder`.
- **B. Dari dump `.sql` milik pemilik proyek:** import `db_pkl.sql` ke `db_pkl` (phpMyAdmin → Import). **Jangan** jalankan `migrate` dan `db:seed` setelah import, karena dump sudah berisi semuanya.

Terakhir:
```bash
php spark serve
```

**Akun contoh (dari seeder):**
- Pembimbing: `pembimbing@pkl.test` / `password123`
- Siswa: `siswa@pkl.test` / `password123`

---

## 4. Struktur file penting

```
app/
  Config/
    Routes.php            # semua URL aplikasi
    Filters.php           # alias 'auth' + CSRF global (sudah diatur)
  Controllers/
    PklController.php     # base: helper url+form, myStudent(), myStudentIds()
    Auth.php  Dashboard.php  Students.php  Companies.php
    Attendance.php  Journals.php  Leaves.php  Assessments.php  Reports.php
  Filters/AuthFilter.php  # cek login + role ('auth' / 'auth:siswa' / 'auth:pembimbing')
  Models/                 # UserModel, StudentModel (detail(), progress()), CompanyModel,
                          # AttendanceModel, JournalModel, LeaveModel, AssessmentModel
  Libraries/Geo.php       # hitung jarak dua koordinat (Haversine)
  Database/
    Migrations/2026-10-02-100000_CreatePklTables.php
    Migrations/2026-10-14-135500_AddLocationFeature.php   # kolom lokasi (companies, attendances)
    Seeds/InitSeeder.php
  Views/
    layout.php            # kerangka halaman (navbar, flash message)
    auth/login.php        # berdiri sendiri, tidak pakai layout
    dashboard/ students/ companies/ attendance/ journals/ leaves/ assessments/ reports/
public/
  uploads/journals/       # bukti kegiatan (tidak di-commit)
  js/geo-attendance.js    # absen: deteksi GPS, jarak, kirim lat/lng (siswa)
  js/company-location.js  # peta kunci lokasi PKL (pembimbing)
```

---

## 5. Database

Semua tabel punya `id` (INT unsigned auto increment), `created_at`, `updated_at`.

| Tabel | Kolom utama |
|---|---|
| `users` | name, email (unik), password (hash), role (`siswa`/`pembimbing`) |
| `companies` | name, address, phone, pic (pembimbing lapangan), latitude, longitude (NULL = belum dikunci), radius_m (default 100) |
| `students` | user_id (unik, FK users), company_id (FK, boleh null), mentor_id (FK users, boleh null), nis, kelas, jurusan, start_date, end_date |
| `attendances` | student_id, date, check_in, check_out, status (`hadir`/`izin`/`sakit`), note, serta kolom lokasi `check_in_lat/lng/distance_m/accuracy_m` dan `check_out_...` (NULL untuk izin/sakit). Unik (student_id, date) |
| `journals` | student_id, date, activity, evidence (nama file), status (`pending`/`approved`/`rejected`), mentor_note |
| `leave_requests` | student_id, date, type (`izin`/`sakit`), reason, status (`pending`/`approved`/`rejected`) |
| `assessments` | student_id (unik), disiplin, keterampilan, sikap, laporan (0-100), final_score (rata-rata), note |

---

## 6. Routes dan data yang dikirim ke View

Pola URL: halaman form tambah = `.../new`, edit = `.../edit/{id}`, simpan = POST, hapus = POST `.../delete/{id}`.

| View | URL | Role | Variabel yang tersedia |
|---|---|---|---|
| `auth/login` | `/login` | semua | (flash `error`) |
| `dashboard/index` | `/dashboard` | semua | `$s` (data siswa, hanya role siswa, bisa null), `$students` (array, role pembimbing), `$stat['siswa','jurnal','izin']` |
| `students/index` | `/students` | pembimbing | `$rows` |
| `students/form` | `/students/new`, `/students/edit/{id}` | pembimbing | `$s` (null saat tambah), `$companies` |
| `companies/index` | `/companies` | pembimbing | `$rows` |
| `companies/form` | `/companies/new`, `/companies/edit/{id}` | pembimbing | `$c` (null saat tambah) |
| `companies/location` | `/companies/location/{id}` | pembimbing | `$c` (halaman peta kunci lokasi; reset: POST `/companies/location/reset/{id}`) |
| `attendance/index` | `/attendance` | semua | `$rows`, `$today`, `$s`, `$company` (data tempat PKL siswa, bisa null; rows milik pembimbing punya field `name`) |
| `journals/index` | `/journals` | semua | `$rows` (untuk pembimbing ada field `name`) |
| `journals/form` | `/journals/new` | siswa | (tidak ada) |
| `leaves/index` | `/leaves` | semua | `$rows` (untuk pembimbing ada field `name`) |
| `assessments/index` | `/assessments` | pembimbing | `$rows` |
| `assessments/form` | `/assessments/{studentId}` | pembimbing | `$s`, `$a` (null kalau belum dinilai) |
| `reports/index` | `/reports` | pembimbing | `$rows` (ekspor CSV: `/reports/export`) |

**Isi satu baris siswa** (dari `StudentModel::detail()`): semua kolom `students` + `name`, `email`, `company_name`, `hadir`, `izin`, `sakit`, `jurnal_ok`, `jurnal_all`, `nilai` (null kalau belum dinilai). Progres 0-100: `\App\Models\StudentModel::progress($row)`.

**Flash message** yang ditampilkan `layout.php`: `success`, `error`, dan `errors` (array pesan validasi).

---

## 7. Konvensi kode di View

- Layout: `<?= $this->extend('layout') ?>` lalu `<?= $this->section('content') ?> ... <?= $this->endSection() ?>`.
- URL: `site_url('students')` untuk link/form, `base_url('uploads/journals/' . $nama)` untuk file di `public/`.
- Escape: `esc($nilai)`. Untuk isi form lama: `esc(old('name', $default, false))`.
- Delete/approve/reject memakai **POST** dengan `csrf_field()`, bukan link GET.
- Kelas Bootstrap yang dipakai: card, table, badge, progress, btn, form-control, alert.

---

## 8. Alur kerja Git dan database tim

**Git**
```bash
git checkout main
git pull
git checkout -b fitur/nama-fitur
# ...kerja, lalu...
git add .
git commit -m "feat: deskripsi singkat"
git push -u origin fitur/nama-fitur   # lalu buka Pull Request di GitHub
```
Setelah `git pull`, jalankan `php spark migrate` kalau ada migration baru.

**Database**
- Struktur hanya diubah lewat migration (`php spark make:migration NamaMigration`), bukan lewat phpMyAdmin.
- Data tiap orang di MySQL lokal masing-masing, tidak ikut Git. Untuk menyamakan data, pakai dump:
  - Export (CMD): `cd C:\xampp\mysql\bin` lalu `mysqldump -u root --result-file=C:\Users\%USERNAME%\Desktop\db_pkl.sql db_pkl`
  - Import: phpMyAdmin → pilih `db_pkl` → tab Import. Untuk file besar: `mysql -u root db_pkl < file.sql` di **CMD** (bukan PowerShell).
- Folder `public/uploads/` juga tidak di Git. Kalau mau bukti jurnal terlihat, salin foldernya terpisah.
- Jangan upload dump data asli ke GitHub.

---

## 9. Masalah yang pernah muncul dan solusinya

| Gejala | Penyebab | Solusi |
|---|---|---|
| `"auth" filter must have a matching alias defined` | alias belum didaftarkan | Tambah `'auth' => \App\Filters\AuthFilter::class` di `$aliases` pada `app/Config/Filters.php`, aktifkan `'csrf'` di `$globals['before']` |
| `Failed opening required ...vendor/.../Boot.php` | folder `vendor/` belum ada setelah clone | `composer install` |
| `Module "openssl" is already loaded` | `extension=openssl` dobel di `php.ini` | Hanya peringatan. Beri `;` di salah satu baris di `C:\xampp\php\php.ini` |
| `cd mysqldump` gagal | `mysqldump` itu program, bukan folder | Jalankan langsung: `mysqldump -u root ...` dari `C:\xampp\mysql\bin` |
| `.env` tidak ada / semua baris `#` | file bawaan CI4 bernama `env` dan semua barisnya dikomentari | Rename jadi `.env` atau salin dari `.env.example`, aktifkan baris dengan menghapus `#` |
| Tabel bentrok saat import | database sudah pernah di-`migrate` | Drop database `db_pkl`, buat ulang yang kosong, lalu import |
| Link bukti jurnal rusak | `public/uploads/` tidak ikut | Salin folder `uploads` dari pemilik proyek |

---

## 10. Status dan ide lanjutan

**Sudah jalan:** halaman login, redirect ke dashboard, filter auth, import database ke komputer kedua, server dev.

**Baru ditambahkan (lolos uji sintaks + uji JS di browser tiruan, belum dites di lapangan):** absensi berbasis lokasi GPS dan kunci lokasi PKL oleh pembimbing. Wajib `php spark migrate`. Detail dan cara pasang: `FITUR_LOKASI.md`. Pengaturan ada di konstanta atas `app/Controllers/Attendance.php` (`ENFORCE_RADIUS`, `MAX_ACCURACY_M`, `REQUIRE_LOCKED_COMPANY`). Tahap sekarang: `ENFORCE_RADIUS = false` (lokasi wajib terbaca dan jarak dicatat, absen tidak ditolak karena jarak); ubah ke `true` untuk membatasi absen di radius lokasi PKL.

**Ide pengembangan:** seeder data contoh yang lebih lengkap, grafik progres, ekspor PDF, README proyek sendiri (README di repo masih bawaan CI4), merapikan tampilan (tema Bootstrap, ikon, font, CSS kustom di `public/css/style.css`).

**Menambah CSS sendiri:** buat `public/css/style.css`, lalu tambahkan di `<head>` pada `layout.php` dan `auth/login.php`, setelah link Bootstrap:
```php
<link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
```
