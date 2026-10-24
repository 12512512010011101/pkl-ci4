<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
/** Daftar semua siswa. Variabel: $rows (ada mentor_name, company_name; bisa null), $belum (true = hanya yang belum lengkap). */
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Siswa</h4>
    <a class="btn btn-primary" href="<?= site_url('admin/students/new') ?>">+ Tambah siswa</a>
</div>

<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link <?= $belum ? '' : 'active' ?>" href="<?= site_url('admin/students') ?>">Semua</a></li>
    <li class="nav-item"><a class="nav-link <?= $belum ? 'active' : '' ?>" href="<?= site_url('admin/students?belum=1') ?>">Belum lengkap</a></li>
</ul>

<div class="card"><div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Nama</th><th>NIS</th><th>Kelas</th><th>Tempat PKL</th><th>Pembimbing</th><th>Periode</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= esc($r['name']) ?><br><small class="text-muted"><?= esc($r['email']) ?></small></td>
            <td><?= esc($r['nis']) ?></td>
            <td><?= esc($r['kelas']) ?> <?= esc($r['jurusan']) ?></td>
            <td><?= $r['company_name'] !== null ? esc($r['company_name']) : '<span class="badge text-bg-warning">Belum ada</span>' ?></td>
            <td><?= $r['mentor_name'] !== null ? esc($r['mentor_name']) : '<span class="badge text-bg-warning">Belum ada</span>' ?></td>
            <td><small><?= esc($r['start_date']) ?> s/d <?= esc($r['end_date']) ?></small></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/students/edit/' . $r['id']) ?>">Edit</a>
                <form class="d-inline" method="post" action="<?= site_url('admin/students/delete/' . $r['id']) ?>"
                      onsubmit="return confirm('Hapus siswa ini beserta absensi, jurnal, izin, dan nilainya?')">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="7" class="text-center text-muted">Tidak ada data.</td></tr><?php endif ?>
    </tbody>
</table></div></div>
<?= $this->endSection() ?>
