<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between mb-3">
    <h4>Rekap Laporan PKL</h4>
    <div class="no-print">
        <a class="btn btn-outline-success" href="<?= site_url('reports/export') ?>">Unduh CSV</a>
        <button class="btn btn-outline-secondary" onclick="window.print()">Cetak</button>
    </div>
</div>
<div class="card"><div class="table-responsive"><table class="table table-bordered mb-0">
    <thead><tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Tempat PKL</th><th>Periode</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Jurnal OK</th><th>Nilai</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= esc($r['name']) ?></td><td><?= esc($r['nis']) ?></td><td><?= esc($r['kelas']) ?> <?= esc($r['jurusan']) ?></td>
            <td><?= esc($r['company_name'] ?? '-') ?></td><td><?= esc($r['start_date']) ?> s/d <?= esc($r['end_date']) ?></td>
            <td><?= $r['hadir'] ?></td><td><?= $r['izin'] ?></td><td><?= $r['sakit'] ?></td><td><?= $r['jurnal_ok'] ?></td>
            <td><?= $r['nilai'] !== null ? esc($r['nilai']) : '-' ?></td></tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="10" class="text-center text-muted">Belum ada data.</td></tr><?php endif ?>
    </tbody></table></div></div>
<?= $this->endSection() ?>
