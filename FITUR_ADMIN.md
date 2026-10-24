# Fitur Admin

Menambah role **admin** di samping `pembimbing` dan `siswa`. Admin mengurus data induk dan memantau seluruh sekolah. Pembimbing tetap menyetujui jurnal dan izin serta menilai, jadi alur lama tidak berubah.

## Yang bisa dilakukan admin

| Menu | Isi |
|---|---|
| **Dashboard** | Ringkasan sekolah: jumlah siswa, pembimbing, tempat PKL, hadir hari ini, jurnal dan izin menunggu, siswa tanpa pembimbing, siswa tanpa tempat PKL. Daftar pembimbing + jumlah siswanya, dan siswa yang belum lengkap (dengan tombol *Atur*). |
| **Rekap** | Tabel semua siswa (tempat PKL, pembimbing, progres, hadir, izin/sakit, jurnal, nilai) + tombol **Unduh CSV**. |
| **Siswa** | Semua siswa. Tambah, ubah, hapus, termasuk memilih **pembimbing** dan **tempat PKL** (admin yang mengatur siapa dibimbing siapa). Filter *Belum lengkap*. |
| **Akun** | Akun pembimbing dan admin: tambah, ubah (nama, email, password, peran), hapus. Akun siswa ditampilkan juga, tapi diubah lewat halaman Siswa. |
| **Tempat PKL** | Halaman yang sama dengan pembimbing, termasuk **kunci lokasi**. |

Aturan keamanan yang sudah ada:
- Semua halaman admin dijaga filter `auth:admin`. Pembimbing dan siswa yang membuka alamatnya dialihkan ke dashboard.
- Admin **tidak bisa menghapus akunnya sendiri** dan **tidak bisa mengubah perannya sendiri** (supaya tidak terkunci keluar).
- Peran yang bisa dibuat lewat halaman Akun hanya `pembimbing` dan `admin`. Siswa dibuat lewat halaman Siswa karena butuh NIS, kelas, dan periode PKL.
- Menghapus pembimbing **tidak** menghapus siswanya. Siswa itu jadi "belum punya pembimbing" dan muncul di daftar *Belum lengkap*.
- Menghapus siswa menghapus juga absensi, jurnal, izin, dan nilainya (CASCADE di database), dan ada konfirmasi di tombolnya.
- CSV dilindungi dari *CSV injection*: sel yang diawali `=`, `+`, `-`, `@` diberi tanda kutip di depannya.
- Semua form memakai `csrf_field()`, password disimpan sebagai hash.

## Isi paket

| File | Status |
|---|---|
| `app/Database/Migrations/2026-10-21-100000_AddAdminRole.php` | baru (menambah nilai `admin` di kolom `users.role`) |
| `app/Database/Seeds/AdminSeeder.php` | baru (akun `admin@pkl.test`) |
| `app/Controllers/Admin.php` | baru |
| `app/Views/dashboard/admin.php` | baru (versi polos) |
| `app/Views/admin/recap.php`, `users.php`, `user_form.php`, `students.php`, `student_form.php` | baru (versi polos) |
| `app/Config/Routes.php` | **ganti** (tambah group `admin`; route `companies/*` kini boleh diakses admin juga) |
| `app/Controllers/Dashboard.php` | **ganti** (tambah cabang admin) |
| `PROJECT_CONTEXT.md`, `TUGAS_TAMPILAN.md` | **ganti** (sudah diperbarui) |

Sebelum menimpa, jalankan `git status` dan pastikan bersih. Setelah menimpa, cek `git diff app/Config/Routes.php app/Controllers/Dashboard.php`: yang berubah harus hanya tambahan admin.

Satu file **tidak** ada di paket karena milik temanmu: `app/Views/layout.php` (lihat langkah 4).

## Cara memasang

**0. Mulai dari `main` yang sudah berisi pekerjaan tampilan temanmu**
```bash
git checkout main
git pull
git checkout -b fitur/admin
```

**1. Ekstrak zip ke root project** (sejajar dengan `composer.json`), pilih Replace.

**2. Jalankan migration:**
```bash
php spark migrate
```
Perintah ini mengubah kolom `role` menjadi `ENUM('siswa','pembimbing','admin')`. Data lama tidak berubah.

**3. Buat akun admin:**
```bash
php spark db:seed AdminSeeder
```
Aman dijalankan berulang (tidak membuat dobel). Login: `admin@pkl.test` / `password123`. **Ganti password ini** lewat menu Akun sebelum dipakai di server sungguhan.

**4. Perbarui menu di `app/Views/layout.php`** (satu perubahan kecil di file temanmu; kabari dia dulu supaya tidak mengedit file ini bersamaan). Tanpa ini admin akan mendapat menu siswa.

Ganti bagian `$menu = ...` (baris yang diawali `$menu = $role === 'pembimbing'` sampai titik koma di akhir) dengan:
```php
$menus = [
    'pembimbing' => ['dashboard' => 'Dashboard', 'students' => 'Siswa', 'companies' => 'Tempat PKL', 'attendance' => 'Absensi', 'journals' => 'Jurnal', 'leaves' => 'Izin', 'assessments' => 'Penilaian', 'reports' => 'Laporan'],
    'admin'      => ['dashboard' => 'Dashboard', 'admin/recap' => 'Rekap', 'admin/students' => 'Siswa', 'admin/users' => 'Akun', 'companies' => 'Tempat PKL'],
    'siswa'      => ['dashboard' => 'Dashboard', 'attendance' => 'Absensi', 'journals' => 'Jurnal', 'leaves' => 'Izin'],
];
$menu      = $menus[$role] ?? $menus['siswa'];
$roleLabel = ['pembimbing' => 'Pembimbing', 'admin' => 'Admin', 'siswa' => 'Siswa'][$role] ?? 'Siswa';
```
Lalu ganti teks lencana peran `<?= $role === 'pembimbing' ? 'Pembimbing' : 'Siswa' ?>` dengan:
```php
<?= esc($roleLabel) ?>
```
Kalau layout temanmu sudah berubah dan bentuknya berbeda, intinya sama: menu dipilih menurut `session('role')`, dan label peran memuat "Admin".

**5. Tes:** `php spark serve`, buka `http://localhost:8080`, login `admin@pkl.test`.

**6. Commit dan Pull Request:**
```bash
git add app public PROJECT_CONTEXT.md FITUR_ADMIN.md TUGAS_TAMPILAN.md
git commit -m "Tambah role admin: dashboard, rekap, kelola akun dan siswa"
git push -u origin fitur/admin
```
Jangan memakai `git add .`. Setelah merge, temanmu `git pull` lalu `php spark migrate` (dan boleh `php spark db:seed AdminSeeder` di database lokalnya).

## Alamat halaman admin

| URL | Fungsi |
|---|---|
| `/dashboard` | dashboard admin (otomatis untuk role admin) |
| `/admin/recap`, `/admin/recap/export` | rekap, unduh CSV |
| `/admin/students` (`?belum=1`), `/admin/students/new`, `/admin/students/edit/{id}` | data siswa |
| `/admin/users` (`?role=pembimbing|siswa|admin`), `/admin/users/new`, `/admin/users/edit/{id}` | akun |
| `/companies`, `/companies/location/{id}` | tempat PKL (juga untuk pembimbing) |

Simpan dan hapus memakai POST (`.../update/{id}`, `.../delete/{id}`), bukan link.

## Yang sudah diuji, dan yang belum

Diuji dengan CodeIgniter 4.7.4 asli, memakai file project milikmu (filter login, Auth, models, layout, seeder) dan database SQLite sebagai pengganti MySQL: **68 pemeriksaan lulus** lewat request HTTP sungguhan, mencakup login admin, akses tiap role (pembimbing dan siswa ditolak dari halaman admin, admin ditolak dari halaman pembimbing), tambah, ubah, dan hapus akun maupun siswa, validasi (email dobel, password pendek, role palsu, pembimbing palsu, tanggal terbalik), perlindungan akun sendiri, rekap dan CSV (termasuk anti rumus), foreign key (hapus pembimbing: siswa tetap ada, hapus siswa: data ikut terhapus), kunci lokasi oleh admin, CSRF, dan logout.

**Belum diuji:** migration `AddAdminRole` di MySQL/MariaDB sungguhan (isinya satu perintah `ALTER TABLE ... MODIFY` standar), tampilan di browser (view masih polos), dan view `companies/index`, `students/index` milik temanmu setelah menu admin dipasang. Kalau `php spark migrate` atau halaman manapun error, kirim pesannya ke aku.

## Batasan dan ide lanjutan

- Admin melihat **angka rekap** per siswa, belum melihat isi absensi, jurnal, dan izin satu per satu. Itu butuh penyesuaian view pembimbing, jadi dipisah untuk tahap berikutnya.
- Pembimbing masih bisa menambah siswa sendiri lewat halaman Siswa miliknya. Kalau nanti hanya admin yang boleh, cukup memindahkan route itu.
- Belum ada fitur "lupa password"; reset dilakukan admin lewat menu Akun atau Siswa.
- Pengaturan sistem (misalnya aktifkan pembatasan radius lokasi) masih lewat konstanta di `Attendance.php`.
