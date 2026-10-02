<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Absensi</h4>
<?php if (session('role') === 'siswa'): ?>
    <div class="card card-body mb-3">
        <?php if (! $s): ?>
            <span class="text-muted">Data PKL kamu belum dilengkapi pembimbing.</span>
        <?php else: ?>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="me-auto">Hari ini: <strong><?= date('d M Y') ?></strong>
                    <?php if ($today): ?> &middot; masuk <?= esc($today['check_in'] ?? '-') ?>, pulang <?= esc($today['check_out'] ?? '-') ?> (<?= esc($today['status']) ?>)<?php endif ?></div>
                <?php if (! $today): ?>
                    <form method="post" action="<?= site_url('attendance/checkin') ?>"><?= csrf_field() ?><button class="btn btn-success">Absen Masuk</button></form>
                <?php elseif ($today['status'] === 'hadir' && ! $today['check_out']): ?>
                    <form method="post" action="<?= site_url('attendance/checkout') ?>"><?= csrf_field() ?><button class="btn btn-warning">Absen Pulang</button></form>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Tanggal</th><?php if (session('role') === 'pembimbing'): ?><th>Siswa</th><?php endif ?><th>Masuk</th><th>Pulang</th><th>Status</th><th>Catatan</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= esc($r['date']) ?></td>
            <?php if (session('role') === 'pembimbing'): ?><td><?= esc($r['name']) ?></td><?php endif ?>
            <td><?= esc($r['check_in'] ?? '-') ?></td><td><?= esc($r['check_out'] ?? '-') ?></td>
            <td><span class="badge text-bg-<?= $r['status'] === 'hadir' ? 'success' : ($r['status'] === 'izin' ? 'info' : 'warning') ?>"><?= esc($r['status']) ?></span></td>
            <td><?= esc($r['note']) ?></td></tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="6" class="text-center text-muted">Belum ada data absensi.</td></tr><?php endif ?>
    </tbody></table></div></div>
<?= $this->endSection() ?>
