<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
/**
 * Form tambah/ubah akun pembimbing atau admin.
 * Variabel: $u (null saat tambah; array users saat ubah).
 */
$edit = $u !== null;
$self = $edit && (int) $u['id'] === (int) session('user_id');
$roleNow = old('role', $u['role'] ?? 'pembimbing', false);
?>
<h4 class="mb-3"><?= $edit ? 'Ubah Akun' : 'Tambah Akun' ?></h4>

<form method="post" class="card card-body" style="max-width:560px"
      action="<?= $edit ? site_url('admin/users/update/' . $u['id']) : site_url('admin/users') ?>">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" value="<?= esc(old('name', $u['name'] ?? '', false)) ?>" required></div>
    <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= esc(old('email', $u['email'] ?? '', false)) ?>" required></div>
    <div class="mb-3"><label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password" <?= $edit ? '' : 'required' ?>>
        <?php if ($edit): ?><div class="form-text">Kosongkan jika tidak ingin mengganti password.</div><?php endif ?></div>
    <div class="mb-3"><label class="form-label">Peran</label>
        <?php if ($self): ?>
            <input type="text" class="form-control" value="<?= esc($u['role']) ?>" disabled>
            <div class="form-text">Peran akunmu sendiri tidak bisa diubah.</div>
        <?php else: ?>
            <select name="role" class="form-select" required>
                <option value="pembimbing" <?= $roleNow === 'pembimbing' ? 'selected' : '' ?>>Pembimbing</option>
                <option value="admin" <?= $roleNow === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        <?php endif ?></div>
    <div>
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-light" href="<?= site_url('admin/users') ?>">Batal</a>
    </div>
</form>
<?= $this->endSection() ?>
