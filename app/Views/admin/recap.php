<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
/** Rekap semua siswa. Variabel: $rows (satu baris = satu siswa; ada field mentor_name dan company_name, bisa null). */
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Rekap Semua Siswa</h4>
    <a class="btn btn-outline-success no-print" href="<?= site_url('admin/recap/export') ?>">Unduh CSV</a>
</div>

<div class="card"><div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr>
        <th>Nama</th><th>Kelas</th><th>Tempat PKL</th><th>Pembimbing</th>
        <th style="width:18%">Progres</th><th>Hadir</th><th>Izin/Sakit</th><th>Jurnal</th><th>Nilai</th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $p = \App\Models\StudentModel::progress($r); ?>
        <tr>
            <td><?= esc($r['name']) ?><br><small class="text-muted"><?= esc($r['nis']) ?></small></td>
            <td><?= esc($r['kelas']) ?> <?= esc($r['jurusan']) ?></td>
            <td><?= esc($r['company_name'] ?? '-') ?></td>
            <td><?= esc($r['mentor_name'] ?? '-') ?></td>
            <td><div class="progress"><div class="progress-bar" style="width: <?= $p ?>%"><?= $p ?>%</div></div></td>
            <td><?= (int) $r['hadir'] ?></td>
            <td><?= (int) $r['izin'] ?> / <?= (int) $r['sakit'] ?></td>
            <td><?= (int) $r['jurnal_ok'] ?>/<?= (int) $r['jurnal_all'] ?></td>
            <td><?= $r['nilai'] !== null ? esc($r['nilai']) : '-' ?></td>
        </tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="9" class="text-center text-muted">Belum ada siswa.</td></tr><?php endif ?>
    </tbody>
</table></div></div>
<?= $this->endSection() ?>
