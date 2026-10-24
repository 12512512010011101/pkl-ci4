<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="page-head">
    <div>
        <h4 class="mb-0"><?= $s ? 'Edit' : 'Tambah' ?> Siswa</h4>
        <small class="text-muted"><?= $s ? 'Perbarui data siswa PKL' : 'Isi data siswa dan akun loginnya' ?></small>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('students') ?>">&larr; Kembali</a>
</div>

<form method="post" action="<?= site_url($s ? 'students/update/' . $s['id'] : 'students') ?>" class="card form-card border-0 shadow-sm">
    <?= csrf_field() ?>
    <div class="card-body p-3 p-md-4">

        <!-- Akun login -->
        <div class="form-section-title">Akun login</div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input name="name" class="form-control" value="<?= esc(old('name', $s['name'] ?? '', false)) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email (untuk login)</label>
                <input type="email" name="email" class="form-control" value="<?= esc(old('email', $s['email'] ?? '', false)) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password <?= $s ? '(kosongkan jika tidak diganti)' : '' ?></label>
                <input type="password" name="password" class="form-control" autocomplete="new-password" <?= $s ? '' : 'required' ?>>
            </div>
        </div>

        <!-- Data siswa -->
        <div class="form-section-title">Data siswa</div>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">NIS</label>
                <input name="nis" class="form-control" value="<?= esc(old('nis', $s['nis'] ?? '', false)) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <input name="kelas" class="form-control" value="<?= esc(old('kelas', $s['kelas'] ?? '', false)) ?>" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jurusan</label>
                <input name="jurusan" class="form-control" value="<?= esc(old('jurusan', $s['jurusan'] ?? '', false)) ?>" required>
            </div>
        </div>

        <!-- Penempatan PKL -->
        <div class="form-section-title">Penempatan PKL</div>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label">Tempat PKL</label>
                <select name="company_id" class="form-select">
                    <option value="">-- belum ditentukan --</option>
                    <?php foreach ($companies as $c): ?>
                        <option value="<?= esc($c['id']) ?>" <?= (string) old('company_id', $s['company_id'] ?? '', false) === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-6">
                <label class="form-label">Mulai PKL</label>
                <input type="date" name="start_date" class="form-control" value="<?= esc(old('start_date', $s['start_date'] ?? '', false)) ?>" required>
            </div>
            <div class="col-sm-6">
                <label class="form-label">Selesai PKL</label>
                <input type="date" name="end_date" class="form-control" value="<?= esc(old('end_date', $s['end_date'] ?? '', false)) ?>" required>
            </div>
        </div>
    </div>

    <div class="card-footer bg-white border-0 pt-0 pb-3 pb-md-4 px-3 px-md-4">
        <div class="form-actions">
            <button class="btn btn-primary px-4">Simpan</button>
            <a class="btn btn-light border" href="<?= site_url('students') ?>">Batal</a>
        </div>
    </div>
</form>

<?= $this->endSection() ?>