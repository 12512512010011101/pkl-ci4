<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between mb-3"><h4>Data Siswa</h4><a class="btn btn-primary" href="<?= site_url('students/new') ?>">+ Tambah siswa</a></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Tempat PKL</th><th>Periode</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= esc($r['name']) ?><br><small class="text-muted"><?= esc($r['email']) ?></small></td>
            <td><?= esc($r['nis']) ?></td><td><?= esc($r['kelas']) ?> <?= esc($r['jurusan']) ?></td>
            <td><?= esc($r['company_name'] ?? '-') ?></td><td><?= esc($r['start_date']) ?> s/d <?= esc($r['end_date']) ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('students/edit/' . $r['id']) ?>">Edit</a>
                <form class="d-inline" method="post" action="<?= site_url('students/delete/' . $r['id']) ?>" onsubmit="return confirm('Hapus siswa beserta seluruh datanya?')">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </td></tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="6" class="text-center text-muted">Belum ada siswa.</td></tr><?php endif ?>
    </tbody></table></div></div>
<?= $this->endSection() ?>
