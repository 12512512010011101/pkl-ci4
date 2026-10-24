# TUGAS TAMPILAN: Sistem Manajemen PKL

> **Untuk Claude / teman yang membaca file ini:** ini daftar tugas tampilan yang harus dikerjakan, beserta batasannya. Balas dalam **Bahasa Indonesia**, santai tapi jelas. **Baca dulu `PROJECT_CONTEXT.md`** (terutama bagian 0 *Aturan kerja* dan bagian 6 *Data yang dikirim ke View*), lalu `FITUR_LOKASI.md`, baru kerjakan file ini. Kalau ada yang tidak jelas, tanya pemilik proyek, jangan menebak.

Pembagian tugas: pemilik proyek (Firm) mengurus **backend** (Controllers, Models, Database, Routes). Pemakai file ini mengurus **tampilan** (`app/Views/` dan `public/`).

---

## 1. Latar belakang singkat

Fitur baru sudah dibuat dan dites di komputer pemilik proyek: **absensi berbasis lokasi**.

- Pembimbing **mengunci lokasi** tempat PKL (DU/DI) di peta, dengan radius standar 100 m.
- Siswa wajib menyalakan lokasi saat absen. Kalau lokasi mati, muncul pemberitahuan agar menyalakannya.
- **Tahap sekarang: kunci dulu, pembatasan belakangan.** Jarak siswa ke lokasi PKL dicatat, tapi absen tidak ditolak karena jarak. Pembatasan bisa diaktifkan nanti oleh pemilik proyek lewat satu konstanta.
- Navigasi burger sudah dikerjakan sebelumnya. Sekarang giliran memperbarui tampilan halaman-halaman lain.

---

## 2. Daftar tugas

### Tugas 1: Dashboard
File: `app/Views/dashboard/index.php`

- Buat dashboard lebih enak dilihat dan informatif.
- Dashboard punya tiga tampilan menurut role: **siswa** dan **pembimbing** (`dashboard/index.php`), serta **admin** (`dashboard/admin.php`, baru dan masih polos). Ketiganya perlu dirapikan dan dibuat seragam.
- Data yang tersedia (jangan ubah namanya): `$s` (data siswa, hanya role siswa, bisa null), `$students` (array siswa, role pembimbing), `$stat['siswa','jurnal','izin']`. Progres 0-100: `\App\Models\StudentModel::progress($row)`.
- **Admin:** backend admin sudah dibuat (lihat `FITUR_ADMIN.md`). `dashboard/admin.php` menerima `$stat` (angka ringkasan: `siswa`, `pembimbing`, `perusahaan`, `hadir_hari_ini`, `jurnal_pending`, `izin_pending`, `tanpa_pembimbing`, `tanpa_tempat`), `$mentors` (id, name, email, jumlah_siswa), dan `$unassigned` (siswa belum punya pembimbing atau tempat PKL). Tugasmu merapikan tampilannya, bukan mengubah nama variabelnya. Akun uji: `admin@pkl.test` / `password123`.
- Kalau butuh data tambahan (grafik, ringkasan lain), minta pemilik proyek menambah variabel di Controller.

### Tugas 2: Tampilan login
File: `app/Views/auth/login.php`

- Halaman ini berdiri sendiri, **tidak** memakai `layout.php`. Kalau menambah CSS, pasang link-nya di file ini juga.
- Jangan ubah form login: `method="post"`, `action` ke `login`, nama input (`email`, `password`), dan `<?= csrf_field() ?>`.
- Pesan error login memakai flash `error`. Pastikan tetap tampil.

### Tugas 3: Tampilan fitur kunci lokasi
File: `app/Views/companies/location.php` dan `app/Views/attendance/_geo.php`

Dua halaman ini baru, desainnya masih polos. Boleh ditata ulang sebebasnya, **dengan syarat id dan atribut di bawah tidak berubah**, karena JavaScript bergantung padanya.

**Halaman peta pembimbing (`companies/location.php`, JS: `public/js/company-location.js`)**

Id yang wajib tetap ada: `loc-map`, `loc-msg`, `loc-form`, `latitude`, `longitude`, `radius_m`, `loc-search`, `btn-search`, `btn-me`.

- `#loc-map` **wajib punya tinggi** (sekarang `style="height:420px"`). Tanpa tinggi, peta tidak tampil.
- Leaflet dimuat dari CDN. Jangan menghapus link CSS dan script Leaflet.
- Form kunci lokasi (`#loc-form`) mengirim POST ke `companies/location/{id}` dan wajib memuat `csrf_field()`.
- Tombol "Buka kunci lokasi" adalah form POST terpisah ke `companies/location/reset/{id}`, bukan link.

**Kotak status lokasi siswa (`attendance/_geo.php`, JS: `public/js/geo-attendance.js`)**

Id dan atribut yang wajib tetap: `#geo-box` dengan atribut `data-lat`, `data-lng`, `data-radius`, `data-company`, `data-enforce`, `data-max-accuracy`, dan `#geo-status` di dalamnya.

- **Penting:** JavaScript mengganti `className` pada `#geo-status` secara utuh menjadi `alert alert-<jenis> mb-0` (jenis: `secondary`, `info`, `success`, `warning`, `danger`). Jadi **class tambahan yang ditulis langsung di `#geo-status` akan hilang**. Untuk memberi gaya, pakai pembungkus `#geo-box` atau selektor CSS seperti `#geo-box .alert-danger`.
- Isi `#geo-status` diganti lewat `innerHTML` oleh JS. Jangan menaruh konten penting di dalamnya.
- Kotak ini hanya tampil untuk role siswa (sudah dijaga di `_geo.php`).
- Penempatan: di halaman Absensi, di dalam bagian siswa, di atas kartu Absen Masuk/Pulang.

**Form absen di `attendance/index.php`**

- Jangan ubah `action` form: `attendance/checkin` dan `attendance/checkout`. JS mencari form lewat pola itu dan menambahkan `lat`, `lng`, `accuracy` sebagai input tersembunyi secara otomatis. **Jangan menambah input dengan nama itu secara manual.**
- Tombol di dalam form dipakai JS untuk menampilkan "Mengambil lokasi...". Jangan ganti dengan elemen yang bukan `<button>`.

**Ide tampilan opsional (tidak wajib):** di tabel absensi pembimbing, tampilkan jarak ke lokasi PKL. Kolom datanya ada di database, tapi belum dikirim sebagai tampilan. Minta pemilik proyek menyiapkan variabelnya kalau mau dibuat.

### Tugas 4: Perbarui tampilan halaman lain
Hampir semua halaman perlu disegarkan agar seragam dengan navigasi baru. Daftar view dan data yang tersedia ada di `PROJECT_CONTEXT.md` bagian 6. Ringkasnya:

| Folder | Halaman |
|---|---|
| `students/` | `index`, `form` |
| `companies/` | `index`, `form` (halaman `location` ada di Tugas 3) |
| `attendance/` | `index` (kotak lokasi ada di Tugas 3) |
| `journals/` | `index`, `form` |
| `leaves/` | `index` |
| `assessments/` | `index`, `form` |
| `reports/` | `index` |
| `admin/` | `recap`, `users`, `user_form`, `students`, `student_form` (baru, versi polos, khusus admin) |
| `dashboard/` | `index` (siswa dan pembimbing), `admin` (baru, versi polos) |
| `layout.php` | kerangka bersama (menu admin sudah ditambahkan, jangan dihapus saat merombak menu) |

Saran urutan kerja: `layout.php` dan CSS bersama dulu, lalu dashboard dan login, lalu halaman lainnya satu per satu.

---

## 3. Yang tidak boleh diubah (ringkasan)

1. Nama variabel yang dikirim Controller ke View.
2. Kode PHP penampil data (`<?= ... ?>`, `foreach`, `if`). Boleh dipindah posisinya, jangan dihapus.
3. `<?= csrf_field() ?>` di setiap form POST.
4. `esc(...)` untuk teks dari database atau input.
5. `action` form absen dan id `geo-box`, `geo-status`, serta id di halaman peta (daftar di Tugas 3).
6. Tombol hapus, setujui, tolak, dan logout tetap **POST** dengan `csrf_field()`, bukan link GET.
7. File di `app/Controllers`, `app/Models`, `app/Database`, `app/Config/Routes.php`. Kalau perlu perubahan di sana, minta pemilik proyek.
8. `layout.php` jangan diedit bersamaan dengan orang lain.

---

## 4. Cara menambah CSS

Buat `public/css/style.css`, lalu tambahkan di `<head>` pada `layout.php` dan `auth/login.php`, setelah link Bootstrap:

```php
<link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
```

Bootstrap 5.3.3 dimuat lewat CDN di `layout.php`. Kelas yang sudah dipakai: card, table, badge, progress, btn, form-control, alert.

---

## 5. Alur Git

Fitur lokasi masuk ke `main` lewat Pull Request dari pemilik proyek. **Sebelum lanjut mengedit**, ambil `main` terbaru ke branch tampilan:

```bash
git status                       # ada perubahan belum commit? commit dulu
git checkout fitur/tampilan
git fetch origin
git merge origin/main
php spark migrate                # wajib: menambah kolom lokasi di database lokal
```

Kalau muncul `CONFLICT`, jangan panik dan jangan asal pilih. Hubungi pemilik proyek atau tempelkan pesannya ke Claude.

Saat selesai satu bagian:

```bash
git add app/Views public
git commit -m "feat(tampilan): deskripsi singkat"
git push
```

Lalu buka (atau perbarui) Pull Request. Pakai `git add app/Views public`, jangan `git add .`, supaya file seperti `env` tidak ikut terbawa.

---

## 6. Cara menguji tampilan

Jalankan `php spark serve`, buka `http://localhost:8080` (pakai `localhost`, bukan alamat IP, karena fitur lokasi hanya jalan di `localhost` atau HTTPS).

Akun contoh (dari seeder): pembimbing `pembimbing@pkl.test`, siswa `siswa@pkl.test`, password keduanya `password123`.

Periksa untuk **kedua role**:

- [ ] Login tampil rapi dan error login muncul saat salah password.
- [ ] Dashboard pembimbing dan dashboard siswa tampil benar.
- [ ] Menu dan navigasi burger bekerja di layar HP dan PC.
- [ ] Setiap halaman di Tugas 4 terbuka tanpa error, baik untuk siswa maupun pembimbing yang berhak.
- [ ] Pembimbing: Tempat PKL, klik *Kunci lokasi*, peta muncul, bisa klik titik, simpan.
- [ ] Siswa: Absensi, kotak status lokasi muncul, tombol Absen Masuk/Pulang jalan.
- [ ] Tes lokasi mati: Chrome F12, More tools, **Sensors**, Location, pilih *Location unavailable*, muat ulang. Kotak merah harus muncul. Kembalikan lokasi dan klik *Coba lagi*, kotak pulih.
- [ ] Semua form tetap bisa disimpan (CSRF tidak error).
- [ ] Tampilan di lebar layar HP tidak berantakan.

---

## 7. Pertanyaan terbuka untuk pemilik proyek

1. **Role admin:** sudah dibuat (`FITUR_ADMIN.md`). Kalau tampilan admin butuh data tambahan, sebutkan ke pemilik proyek.
2. Data tambahan untuk dashboard (grafik, ringkasan): sebutkan data apa yang dibutuhkan agar disiapkan di Controller.
3. Apakah jarak absen perlu ditampilkan di tabel absensi pembimbing?

---

## 8. Kriteria selesai

- Semua halaman di daftar tugas tampil rapi di PC dan HP.
- Tidak ada id, atribut, atau kode PHP yang masuk daftar larangan yang berubah.
- Fitur lokasi tetap jalan setelah tampilan diubah (cek kotak status lokasi dan peta kunci).
- Perubahan ada di branch sendiri dan masuk lewat Pull Request.
