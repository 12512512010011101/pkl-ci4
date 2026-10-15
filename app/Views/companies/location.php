<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $locked = $c['latitude'] !== null && $c['longitude'] !== null; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Kunci Lokasi PKL</h4>
        <div class="text-muted"><?= esc($c['name']) ?> &middot; <?= esc($c['address'] ?? '-') ?></div>
    </div>
    <span class="badge text-bg-<?= $locked ? 'success' : 'secondary' ?>"><?= $locked ? 'Terkunci' : 'Belum dikunci' ?></span>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="input-group mb-2">
                <input type="text" id="loc-search" class="form-control" placeholder="Cari alamat atau nama tempat"
                       value="<?= esc($c['address'] ?? '') ?>">
                <button type="button" id="btn-search" class="btn btn-outline-primary">Cari</button>
                <button type="button" id="btn-me" class="btn btn-outline-secondary">Pakai lokasi saya</button>
            </div>
            <div id="loc-map" style="height:420px"></div>
            <div id="loc-msg" class="small text-muted mt-2">Klik peta atau geser pin untuk menentukan titik lokasi PKL.</div>
        </div></div>
    </div>

    <div class="col-lg-4">
        <form method="post" id="loc-form" action="<?= site_url('companies/location/' . $c['id']) ?>" class="card card-body mb-3">
            <?= csrf_field() ?>
            <div class="mb-3"><label class="form-label">Latitude</label>
                <input type="text" id="latitude" name="latitude" class="form-control"
                       value="<?= esc(old('latitude', $c['latitude'] ?? '', false)) ?>" required></div>
            <div class="mb-3"><label class="form-label">Longitude</label>
                <input type="text" id="longitude" name="longitude" class="form-control"
                       value="<?= esc(old('longitude', $c['longitude'] ?? '', false)) ?>" required></div>
            <div class="mb-3"><label class="form-label">Radius absen (meter)</label>
                <input type="number" id="radius_m" name="radius_m" min="20" max="5000" class="form-control"
                       value="<?= esc(old('radius_m', $c['radius_m'] ?? 100, false)) ?>" required>
                <div class="form-text">Dipakai untuk menilai jarak absen siswa (standar 100 m). Absen ditolak karena jarak hanya jika pembatasan diaktifkan (ENFORCE_RADIUS di Attendance.php).</div></div>
            <div>
                <button class="btn btn-primary">Kunci lokasi ini</button>
                <a class="btn btn-light" href="<?= site_url('companies') ?>">Kembali</a>
            </div>
        </form>

        <?php if ($locked): ?>
            <form method="post" action="<?= site_url('companies/location/reset/' . $c['id']) ?>"
                  onsubmit="return confirm('Buka kunci lokasi? Siswa tidak akan divalidasi jaraknya sampai lokasi dikunci lagi.')">
                <?= csrf_field() ?>
                <button class="btn btn-outline-danger btn-sm">Buka kunci lokasi</button>
            </form>
        <?php endif ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
<script src="<?= base_url('js/company-location.js') ?>" defer></script>
<?= $this->endSection() ?>
