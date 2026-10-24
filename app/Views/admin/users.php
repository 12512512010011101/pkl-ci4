<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
/** Daftar akun. Variabel: $rows (id, name, email, role, created_at, student_id), $role (filter aktif: '', siswa, pembimbing, admin). */
$tabs = ['' => 'Semua', 'pembimbing' => 'Pembimbing', 'siswa' => 'Siswa', 'admin' => 'Admin'];
$badge = ['admin' => 'danger', 'pembimbing' => 'primary', 'siswa' => 'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Akun</h4>
    <a class="btn btn-primary" href="<?= site_url('admin/users/new') ?>">+ Tambah pembimbing / admin</a>
</div>

<ul class="nav nav-pills mb-3">
    <?php foreach ($tabs as $key => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $role === $key ? 'active' : '' ?>"
               href="<?= site_url('admin/users' . ($key !== '' ? '?role=' . $key : '')) ?>"><?= $label ?></a>
        </li>
    <?php endforeach ?>
</ul>

<div class="card"><div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): $self = (int) $r['id'] === (int) session('user_id'); ?>
        <tr>
            <td><?= esc($r['name']) ?><?= $self ? ' <small class="text-muted">(kamu)</small>' : '' ?></td>
            <td><?= esc($r['email']) ?></td>
            <td><span class="badge text-bg-<?= $badge[$r['role']] ?? 'secondary' ?>"><?= esc($r['role']) ?></span></td>
            <td class="text-end text-nowrap">
                <?php if ($r['role'] === 'siswa'): ?>
                    <?php if ($r['student_id']): ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/students/edit/' . $r['student_id']) ?>">Edit di halaman Siswa</a>
                    <?php endif ?>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/users/edit/' . $r['id']) ?>">Edit</a>
                    <?php if (! $self): ?>
                        <form class="d-inline" method="post" action="<?= site_url('admin/users/delete/' . $r['id']) ?>"
                              onsubmit="return confirm('Hapus akun ini? Siswa bimbingannya tidak ikut terhapus, tapi jadi belum punya pembimbing.')">
                            <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    <?php endif ?>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="4" class="text-center text-muted">Belum ada akun.</td></tr><?php endif ?>
    </tbody>
</table></div></div>
<?= $this->endSection() ?>
