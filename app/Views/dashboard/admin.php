<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
/**
 * Dashboard admin (versi polos, siap dirapikan tampilannya).
 * Variabel: $stat (array angka ringkasan), $mentors (daftar pembimbing + jumlah siswa), $unassigned (siswa belum lengkap).
 */
?>
<h4 class="mb-3">Dashboard Admin</h4>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Siswa</div><div class="fs-3"><a href="<?= site_url('admin/students') ?>"><?= (int) $stat['siswa'] ?></a></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Pembimbing</div><div class="fs-3"><a href="<?= site_url('admin/users?role=pembimbing') ?>"><?= (int) $stat['pembimbing'] ?></a></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Tempat PKL</div><div class="fs-3"><a href="<?= site_url('companies') ?>"><?= (int) $stat['perusahaan'] ?></a></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Hadir hari ini</div><div class="fs-3"><?= (int) $stat['hadir_hari_ini'] ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Jurnal menunggu</div><div class="fs-3"><?= (int) $stat['jurnal_pending'] ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Izin menunggu</div><div class="fs-3"><?= (int) $stat['izin_pending'] ?></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Tanpa pembimbing</div><div class="fs-3"><a href="<?= site_url('admin/students?belum=1') ?>"><?= (int) $stat['tanpa_pembimbing'] ?></a></div></div></div></div>
    <div class="col-6 col-md-3"><div class="card"><div class="card-body"><div class="text-muted">Tanpa tempat PKL</div><div class="fs-3"><a href="<?= site_url('admin/students?belum=1') ?>"><?= (int) $stat['tanpa_tempat'] ?></a></div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card"><div class="card-header">Pembimbing dan jumlah siswa</div>
            <div class="table-responsive"><table class="table mb-0 align-middle">
                <thead><tr><th>Nama</th><th>Email</th><th>Siswa</th></tr></thead>
                <tbody>
                <?php foreach ($mentors as $m): ?>
                    <tr><td><?= esc($m['name']) ?></td><td><?= esc($m['email']) ?></td><td><?= (int) $m['jumlah_siswa'] ?></td></tr>
                <?php endforeach ?>
                <?php if (! $mentors): ?><tr><td colspan="3" class="text-center text-muted">Belum ada pembimbing.</td></tr><?php endif ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card"><div class="card-header">Siswa yang belum lengkap (maks. 10)</div>
            <div class="table-responsive"><table class="table mb-0 align-middle">
                <thead><tr><th>Nama</th><th>Belum ada</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($unassigned as $r): ?>
                    <tr>
                        <td><?= esc($r['name']) ?></td>
                        <td>
                            <?php if (empty($r['mentor_id'])): ?><span class="badge text-bg-warning">Pembimbing</span><?php endif ?>
                            <?php if (empty($r['company_id'])): ?><span class="badge text-bg-warning">Tempat PKL</span><?php endif ?>
                        </td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/students/edit/' . $r['id']) ?>">Atur</a></td>
                    </tr>
                <?php endforeach ?>
                <?php if (! $unassigned): ?><tr><td colspan="3" class="text-center text-muted">Semua siswa sudah lengkap.</td></tr><?php endif ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
