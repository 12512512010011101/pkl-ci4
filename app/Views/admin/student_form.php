<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
/**
 * Form tambah/ubah siswa oleh admin.
 * Variabel: $s (null saat tambah; array siswa saat ubah), $mentors (users role pembimbing), $companies.
 */
$edit    = $s !== null;
$mentorV = (string) old('mentor_id', $s['mentor_id'] ?? '', false);
$compV   = (string) old('company_id', $s['company_id'] ?? '', false);
?>
<h4 class="mb-3"><?= $edit ? 'Ubah Siswa' : 'Tambah Siswa' ?></h4>

<form method="post" class="card card-body" style="max-width:640px"
      action="<?= $edit ? site_url('admin/students/update/' . $s['id']) : site_url('admin/students') ?>">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="<?= esc(old('name', $s['name'] ?? '', false)) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email', $s['email'] ?? '', false)) ?>" required></div>
        <div class="col-12"><label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="6" autocomplete="new-password" <?= $edit ? '' : 'required' ?>>
            <?php if ($edit): ?><div class="form-text">Kosongkan jika tidak ingin mengganti password.</div><?php endif ?></div>

        <div class="col-md-4"><label class="form-label">NIS</label>
            <input type="text" name="nis" class="form-control" value="<?= esc(old('nis', $s['nis'] ?? '', false)) ?>" required></div>
        <div class="col-md-4"><label class="form-label">Kelas</label>
            <input type="text" name="kelas" class="form-control" value="<?= esc(old('kelas', $s['kelas'] ?? '', false)) ?>" required></div>
        <div class="col-md-4"><label class="form-label">Jurusan</label>
            <input type="text" name="jurusan" class="form-control" value="<?= esc(old('jurusan', $s['jurusan'] ?? '', false)) ?>" required></div>

        <div class="col-md-6"><label class="form-label">Pembimbing</label>
            <select name="mentor_id" class="form-select">
                <option value="">- belum ada -</option>
                <?php foreach ($mentors as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= $mentorV === (string) $m['id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                <?php endforeach ?>
            </select></div>
        <div class="col-md-6"><label class="form-label">Tempat PKL</label>
            <select name="company_id" class="form-select">
                <option value="">- belum ada -</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $compV === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                <?php endforeach ?>
            </select></div>

        <div class="col-md-6"><label class="form-label">Tanggal mulai</label>
            <input type="date" name="start_date" class="form-control" value="<?= esc(old('start_date', $s['start_date'] ?? '', false)) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Tanggal selesai</label>
            <input type="date" name="end_date" class="form-control" value="<?= esc(old('end_date', $s['end_date'] ?? '', false)) ?>" required></div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-light" href="<?= site_url('admin/students') ?>">Batal</a>
    </div>
</form>
<?= $this->endSection() ?>
