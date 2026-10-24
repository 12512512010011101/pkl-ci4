<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$today   = date('Y-m-d');
$status  = static fn ($r) => $today < $r['start_date']
    ? ['Belum mulai', 'chip-info']
    : ($today > $r['end_date'] ? ['Selesai', 'chip-muted'] : ['Berjalan', 'chip-ok']);
$berjalan = 0;
foreach ($rows as $x) { if ($today >= $x['start_date'] && $today <= $x['end_date']) $berjalan++; }
?>

<div class="page-head">
    <div>
        <h4 class="mb-0">Data Siswa</h4>
        <small><?= count($rows) ?> siswa &middot; <?= $berjalan ?> sedang PKL</small>
    </div>
    <a class="btn btn-primary" href="<?= site_url('students/new') ?>">+ Tambah siswa</a>
</div>

<div class="card">
    <div class="toolbar">
        <div class="search-box">
            <input type="search" id="cari" class="form-control" placeholder="Cari nama, NIS, atau tempat PKL...">
        </div>
        <span class="chip chip-brand" id="hitung"><?= count($rows) ?> data</span>
    </div>

    <!-- Tabel (PC) -->
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0">
            <thead><tr><th>Siswa</th><th>NIS</th><th>Kelas</th><th>Tempat PKL</th><th>Periode</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): [$stLabel, $stClass] = $status($r); ?>
                <tr class="cari-item" data-cari="<?= esc(mb_strtolower($r['name'] . ' ' . $r['email'] . ' ' . $r['nis'] . ' ' . ($r['company_name'] ?? '')), 'attr') ?>">
                    <td>
                        <div class="row-user">
                            <div class="avatar"><?= esc(strtoupper(mb_substr($r['name'], 0, 1))) ?></div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate"><?= esc($r['name']) ?></div>
                                <small class="text-muted"><?= esc($r['email']) ?></small>
                            </div>
                        </div>
                    </td>
                    <td><?= esc($r['nis']) ?></td>
                    <td><span class="chip chip-brand"><?= esc($r['kelas']) ?> <?= esc($r['jurusan']) ?></span></td>
                    <td><?= esc($r['company_name'] ?? '-') ?></td>
                    <td>
                        <div class="small text-nowrap"><?= esc($r['start_date']) ?> s/d <?= esc($r['end_date']) ?></div>
                        <span class="chip <?= $stClass ?> mt-1"><?= $stLabel ?></span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('students/edit/' . $r['id']) ?>">Edit</a>
                        <form class="d-inline" method="post" action="<?= site_url('students/delete/' . $r['id']) ?>" onsubmit="return confirm('Hapus siswa beserta seluruh datanya?')">
                            <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
            <?php if (! $rows): ?>
                <tr><td colspan="6"><div class="empty"><div class="big">🎓</div>Belum ada siswa.</div></td></tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>

    <!-- Kartu (HP) -->
    <div class="d-md-none">
        <?php foreach ($rows as $r): [$stLabel, $stClass] = $status($r); ?>
            <div class="student-item cari-item" data-cari="<?= esc(mb_strtolower($r['name'] . ' ' . $r['email'] . ' ' . $r['nis'] . ' ' . ($r['company_name'] ?? '')), 'attr') ?>">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="row-user">
                        <div class="avatar"><?= esc(strtoupper(mb_substr($r['name'], 0, 1))) ?></div>
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate"><?= esc($r['name']) ?></div>
                            <div class="small text-muted text-truncate"><?= esc($r['email']) ?></div>
                        </div>
                    </div>
                    <span class="chip <?= $stClass ?> flex-shrink-0"><?= $stLabel ?></span>
                </div>
                <div class="small mt-3">
                    <div><span class="text-muted">NIS:</span> <?= esc($r['nis']) ?></div>
                    <div><span class="text-muted">Kelas:</span> <?= esc($r['kelas']) ?> <?= esc($r['jurusan']) ?></div>
                    <div><span class="text-muted">Tempat PKL:</span> <?= esc($r['company_name'] ?? '-') ?></div>
                    <div><span class="text-muted">Periode:</span> <?= esc($r['start_date']) ?> s/d <?= esc($r['end_date']) ?></div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a class="btn btn-sm btn-outline-primary flex-fill" href="<?= site_url('students/edit/' . $r['id']) ?>">Edit</a>
                    <form class="flex-fill" method="post" action="<?= site_url('students/delete/' . $r['id']) ?>" onsubmit="return confirm('Hapus siswa beserta seluruh datanya?')">
                        <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger w-100">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach ?>
        <?php if (! $rows): ?>
            <div class="empty"><div class="big">🎓</div>Belum ada siswa.</div>
        <?php endif ?>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('cari');
    var hitung = document.getElementById('hitung');
    if (!input) return;
    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        var tampil = 0;
        document.querySelectorAll('.cari-item').forEach(function (el) {
            var cocok = el.getAttribute('data-cari').indexOf(q) !== -1;
            el.style.display = cocok ? '' : 'none';
            // tabel dan kartu sama-sama ada di HTML, hitung yang tabel saja
            if (cocok && el.tagName === 'TR') tampil++;
        });
        if (hitung) hitung.textContent = tampil + ' data';
    });
})();
</script>

<?= $this->endSection() ?>