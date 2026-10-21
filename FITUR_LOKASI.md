# Fitur Absensi Berbasis Lokasi

Siswa hanya bisa absen kalau lokasi perangkatnya aktif. Pembimbing mengunci lokasi tempat PKL di peta, dan jarak siswa ke titik itu dicatat setiap absen.

**Tahap saat ini: "kunci dulu, pembatasan belakangan"** (`ENFORCE_RADIUS = false`). Lokasi wajib terbaca, tapi absen **tidak ditolak** karena jarak atau akurasi. Kalau nanti mau membatasi (siswa harus berada di radius lokasi PKL), ubah satu konstanta, lihat bagian *Pengaturan* di bawah.

## Cara kerja singkat

1. **Pembimbing mengunci lokasi** tempat PKL di peta (Tempat PKL > tombol *Kunci lokasi*), dengan radius (standar 100 m).
2. **Siswa membuka halaman Absensi.** JavaScript di browser (Geolocation API, tanpa Node.js) membaca lokasi dan terus memantaunya.
   - Lokasi mati atau izin ditolak: muncul kotak merah berisi cara menyalakannya + tombol *Coba lagi*. Kalau lokasi dinyalakan lagi, status pulih otomatis.
   - Lokasi aktif: kotak menampilkan jarak ke lokasi PKL.
3. **Saat tombol absen ditekan,** browser mengirim `lat`, `lng`, `accuracy` bersama form. **Server yang memutuskan** (rumus Haversine di `App\Libraries\Geo`): absen ditolak kalau lokasi tidak terkirim atau tidak valid. Dengan `ENFORCE_RADIUS = true`, absen juga ditolak bila di luar radius atau akurasi GPS terlalu rendah.
4. Koordinat, jarak, dan akurasi disimpan di tabel `attendances` (masuk dan pulang) sebagai jejak audit.

## Isi paket

| File | Status |
|---|---|
| `app/Database/Migrations/2026-10-14-135500_AddLocationFeature.php` | baru |
| `app/Libraries/Geo.php` | baru |
| `app/Views/attendance/_geo.php` | baru (kotak status lokasi, di-include) |
| `app/Views/companies/location.php` | baru (halaman peta pembimbing) |
| `public/js/geo-attendance.js` | baru |
| `public/js/company-location.js` | baru |
| `app/Config/Routes.php` | **ganti** (tambah 3 route) |
| `app/Controllers/Attendance.php` | **ganti** |
| `app/Controllers/Companies.php` | **ganti** (tambah 3 method) |
| `app/Models/CompanyModel.php`, `AttendanceModel.php` | **ganti** (tambah kolom di `allowedFields`) |
| `PROJECT_CONTEXT.md` | **ganti** (sudah diperbarui) |

Sebelum menimpa file yang diganti, jalankan `git status` dan pastikan belum ada perubahan lain yang belum di-commit.

## Cara memasang

**1. Ekstrak zip ke root project** (sejajar dengan `composer.json`), pilih Replace.

**2. Jalankan migration:**
```bash
php spark migrate
```
Menambah kolom `latitude`, `longitude`, `radius_m` di `companies` dan 8 kolom lokasi di `attendances`. Semua nullable, jadi data lama aman.

**3. Dua edit satu baris di view yang sudah ada** (ini file milik temanmu yang mengerjakan tampilan, jadi hanya ditambah, tidak diganti):

- `app/Views/attendance/index.php`: di dalam bagian khusus siswa (`session('role') === 'siswa'`), **di atas** kartu tombol Absen Masuk/Pulang:
  ```php
  <?= $this->include('attendance/_geo') ?>
  ```
- `app/Views/companies/index.php`: di sel aksi, di samping tombol Edit:
  ```php
  <a class="btn btn-sm btn-outline-success" href="<?= site_url('companies/location/' . $r['id']) ?>"><?= $r['latitude'] !== null ? 'Lokasi (terkunci)' : 'Kunci lokasi' ?></a>
  ```

**4. Commit dan push** di branch sendiri, lalu Pull Request. Teman yang `git pull` wajib menjalankan `php spark migrate`.

## Cara memakai

**Pembimbing:** menu Tempat PKL > *Kunci lokasi* pada perusahaan. Tentukan titik dengan klik peta, geser pin, cari alamat, atau *Pakai lokasi saya* (kalau sedang berada di tempat PKL). Atur radius, lalu *Kunci lokasi ini*. *Buka kunci lokasi* membatalkannya.

**Siswa:** menu Absensi, izinkan lokasi saat browser meminta, lalu tekan Absen Masuk / Pulang.

## Menguji tanpa pergi ke lokasi

Di Chrome (PC): `F12` > titik tiga > More tools > **Sensors** > Location. Pilih *Other...* lalu isi latitude/longitude, atau pilih *Location unavailable* untuk meniru lokasi mati. Muat ulang halaman setelah mengubahnya.

Skenario yang perlu dites (mode sekarang, `ENFORCE_RADIUS = false`):
1. Lokasi di titik PKL: absen berhasil, pesan menyebut jarak.
2. Lokasi 500 m dari titik: kotak kuning "di luar area PKL", absen **tetap berhasil**, jarak 500 m tercatat.
3. *Location unavailable* atau izin diblokir (ikon gembok > Lokasi > Blokir): kotak merah muncul, absen tidak terkirim. Nyalakan lagi: kotak pulih otomatis.
4. PKL belum dikunci: absen tetap berhasil, jarak kosong.
5. Absen pulang juga mencatat lokasi.

Setelah `ENFORCE_RADIUS = true`, ulangi skenario 2 (sekarang ditolak dengan pesan jarak) dan 5 (pulang di luar radius ditolak).

## Syarat penting: HTTPS

Browser **hanya mengizinkan lokasi di HTTPS atau `http://localhost`**.

- Di PC sendiri dengan `php spark serve` (`http://localhost:8080`): jalan.
- Dibuka dari HP lewat IP (`http://192.168.x.x:8080`): **diblokir browser**, kotak merah "bukan HTTPS" muncul. Untuk mencoba di HP, pakai tunnel HTTPS (mis. Cloudflare Tunnel atau ngrok), atau deploy ke hosting ber-HTTPS.
- Saat dipakai sungguhan, aplikasi harus di-hosting dengan HTTPS.

## Batasan yang perlu diketahui

- **Lokasi bisa dipalsukan.** Aplikasi fake GPS atau DevTools bisa mengirim koordinat palsu, dan server tidak bisa membuktikannya. Fitur ini menyulitkan, bukan menghilangkan kecurangan. Yang sudah ada: koordinat + akurasi tiap absen tersimpan. Pengembangan berikutnya: tampilkan jarak dan titik absen di halaman pembimbing, tandai absen yang mencurigakan (akurasi terlalu sempurna, lompatan lokasi mustahil), atau minta foto selfie saat absen.
- **Akurasi laptop/PC rendah.** Laptop tanpa GPS memakai Wi-Fi/IP dengan akurasi ratusan sampai ribuan meter. Di mode sekarang laptop tetap bisa absen (lokasinya dicatat, tampil peringatan "akurasi rendah"). Begitu `ENFORCE_RADIUS = true`, akurasi di atas `MAX_ACCURACY_M` (300 m) ditolak, jadi absen harus lewat HP.
- Pencarian alamat memakai layanan gratis OpenStreetMap (Nominatim) dan hanya untuk pemakaian ringan.

## Pengaturan (konstanta di atas `app/Controllers/Attendance.php`)

| Konstanta | Standar | Artinya |
|---|---|---|
| `ENFORCE_RADIUS` | `false` | `false` = mode "kunci saja": lokasi wajib terbaca, jarak hanya dicatat. `true` = absen ditolak bila di luar radius atau akurasi terlalu rendah |
| `MAX_ACCURACY_M` | `300` | Akurasi terburuk yang diterima (meter), hanya berlaku saat `ENFORCE_RADIUS = true` |
| `REQUIRE_LOCKED_COMPANY` | `false` | `true` = siswa tidak bisa absen bila pembimbing belum mengunci lokasi PKL-nya |

Radius per perusahaan diatur pembimbing di halaman kunci lokasi (20 sampai 5000 m).

**Mengaktifkan pembatasan nanti:** ubah `public const ENFORCE_RADIUS = false;` menjadi `true` di `app/Controllers/Attendance.php`, simpan, selesai. Pesan di halaman absen siswa ikut berubah otomatis (JavaScript membacanya dari server lewat `_geo.php`), jadi tidak ada file lain yang perlu diubah. Sebaiknya aktifkan setelah semua tempat PKL sudah dikunci dan radiusnya sudah diuji dengan HP.

## Catatan untuk yang mengerjakan tampilan

- Boleh menata ulang `_geo.php` dan `location.php`, tapi jangan mengganti id `geo-box`, `geo-status`, `loc-map`, `loc-msg`, `loc-form`, `latitude`, `longitude`, `radius_m`, `loc-search`, `btn-search`, `btn-me`, dan jangan mengubah `action` form absen. JavaScript bergantung pada nama-nama itu.
- Kotak status memakai kelas `alert alert-*` dari Bootstrap. Warnanya boleh diubah lewat CSS.
- Variabel baru untuk view: `$company` di `attendance/index` (bisa null), `$c` di `companies/location`.
