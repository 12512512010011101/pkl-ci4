<?php
/**
 * Kotak status lokasi untuk halaman absensi siswa.
 * Dipasang dengan satu baris di attendance/index.php:
 *     <?= $this->include('attendance/_geo') ?>
 *
 * JavaScript (public/js/geo-attendance.js) mencari form lewat atribut action yang
 * berakhiran "attendance/checkin" dan "attendance/checkout", lalu menambahkan lat/lng
 * secara otomatis. Jangan ubah action form tersebut dan jangan ganti id #geo-box / #geo-status.
 */
$company = $company ?? null;
$locked  = $company && $company['latitude'] !== null && $company['longitude'] !== null;
?>
<?php if (session('role') === 'siswa'): ?>
    <div id="geo-box" class="mb-3"
         data-lat="<?= $locked ? esc($company['latitude']) : '' ?>"
         data-lng="<?= $locked ? esc($company['longitude']) : '' ?>"
         data-radius="<?= $company ? (int) ($company['radius_m'] ?: 100) : 100 ?>"
         data-company="<?= esc($company['name'] ?? '') ?>"
         data-enforce="<?= \App\Controllers\Attendance::ENFORCE_RADIUS ? '1' : '0' ?>"
         data-max-accuracy="<?= (int) \App\Controllers\Attendance::MAX_ACCURACY_M ?>">
        <div id="geo-status" class="alert alert-secondary mb-0" role="alert">Memeriksa lokasi perangkat...</div>
    </div>
    <script src="<?= base_url('js/geo-attendance.js') ?>" defer></script>
<?php endif ?>
