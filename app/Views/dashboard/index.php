<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Dashboard</h4>

<?php if (session('role') === 'siswa'): ?>
    <?php if (! $s): ?>
        <div class="alert alert-warning">Data PKL kamu belum dilengkapi. Hubungi pembimbing.</div>
    <?php else: $p = \App\Models\StudentModel::progress($s); ?>
        <div class="card mb-3"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div><strong><?= esc($s['name']) ?></strong> &middot; <?= esc($s['kelas']) ?> <?= esc($s['jurusan']) ?><br>
                    <small class="text-muted">Tempat PKL: <?= esc($s['company_name'] ?? '-') ?> (<?= esc($s['start_date']) ?> s/d <?= esc($s['end_date']) ?>)</small></div>
                <div class="fs-4"><?= $p ?>%</div>
            </div>
            <div class="progress mt-2"><div class="progress-bar" style="width: <?= $p ?>%"></div></div>
        </div></div>
        <div class="row g-3">
            <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Hadir</div><div class="fs-3"><?= $s['hadir'] ?></div></div></div></div>
            <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Izin / Sakit</div><div class="fs-3"><?= $s['izin'] ?> / <?= $s['sakit'] ?></div></div></div></div>
            <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Jurnal disetujui</div><div class="fs-3"><?= $s['jurnal_ok'] ?>/<?= $s['jurnal_all'] ?></div></div></div></div>
            <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Nilai akhir</div><div class="fs-3"><?= $s['nilai'] !== null ? esc($s['nilai']) : '-' ?></div></div></div></div>
        </div>
    <?php endif ?>

<?php else: ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Siswa bimbingan</div><div class="fs-3"><?= $stat['siswa'] ?></div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Jurnal menunggu</div><div class="fs-3"><a href="<?= site_url('journals?status=pending') ?>"><?= $stat['jurnal'] ?></a></div></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><div class="text-muted">Izin menunggu</div><div class="fs-3"><a href="<?= site_url('leaves') ?>"><?= $stat['izin'] ?></a></div></div></div></div>
    </div>
    <div class="card"><div class="card-header">Monitoring progres PKL</div>
        <div class="table-responsive"><table class="table mb-0 align-middle">
            <thead><tr><th>Nama</th><th>Tempat PKL</th><th style="width:25%">Progres</th><th>Hadir</th><th>Jurnal</th><th>Nilai</th></tr></thead>
            <tbody>
            <?php foreach ($students as $r): $p = \App\Models\StudentModel::progress($r); ?>
                <tr>
                    <td><?= esc($r['name']) ?></td><td><?= esc($r['company_name'] ?? '-') ?></td>
                    <td><div class="progress"><div class="progress-bar" style="width: <?= $p ?>%"><?= $p ?>%</div></div></td>
                    <td><?= $r['hadir'] ?></td><td><?= $r['jurnal_ok'] ?>/<?= $r['jurnal_all'] ?></td>
                    <td><?= $r['nilai'] !== null ? esc($r['nilai']) : '-' ?></td>
                </tr>
            <?php endforeach ?>
            <?php if (! $students): ?><tr><td colspan="6" class="text-center text-muted">Belum ada siswa.</td></tr><?php endif ?>
            </tbody>
        </table></div>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
