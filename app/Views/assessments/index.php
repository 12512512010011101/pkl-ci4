<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Penilaian Siswa</h4>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Nama</th><th>Tempat PKL</th><th>Nilai akhir</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= esc($r['name']) ?></td><td><?= esc($r['company_name'] ?? '-') ?></td>
            <td><?= $r['nilai'] !== null ? esc($r['nilai']) : '<span class="text-muted">belum dinilai</span>' ?></td>
            <td class="text-end"><a class="btn btn-sm btn-primary" href="<?= site_url('assessments/' . $r['id']) ?>">Nilai</a></td></tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="4" class="text-center text-muted">Belum ada siswa.</td></tr><?php endif ?>
    </tbody></table></div></div>
<?= $this->endSection() ?>
