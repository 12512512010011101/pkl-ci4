<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= $s ? 'Edit' : 'Tambah' ?> Siswa</h4>
<form method="post" action="<?= site_url($s ? 'students/update/' . $s['id'] : 'students') ?>" class="card card-body" style="max-width:720px">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Nama</label>
            <input name="name" class="form-control" value="<?= esc(old('name', $s['name'] ?? '', false)) ?>" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Email (untuk login)</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email', $s['email'] ?? '', false)) ?>" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Password <?= $s ? '(kosongkan jika tidak diganti)' : '' ?></label>
            <input type="password" name="password" class="form-control" <?= $s ? '' : 'required' ?>></div>
        <div class="col-md-6 mb-3"><label class="form-label">NIS</label>
            <input name="nis" class="form-control" value="<?= esc(old('nis', $s['nis'] ?? '', false)) ?>" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Kelas</label>
            <input name="kelas" class="form-control" value="<?= esc(old('kelas', $s['kelas'] ?? '', false)) ?>" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Jurusan</label>
            <input name="jurusan" class="form-control" value="<?= esc(old('jurusan', $s['jurusan'] ?? '', false)) ?>" required></div>
        <div class="col-12 mb-3"><label class="form-label">Tempat PKL</label>
            <select name="company_id" class="form-select">
                <option value="">-- belum ditentukan --</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (string) old('company_id', $s['company_id'] ?? '', false) === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                <?php endforeach ?>
            </select></div>
        <div class="col-md-6 mb-3"><label class="form-label">Mulai PKL</label>
            <input type="date" name="start_date" class="form-control" value="<?= esc(old('start_date', $s['start_date'] ?? '', false)) ?>" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Selesai PKL</label>
            <input type="date" name="end_date" class="form-control" value="<?= esc(old('end_date', $s['end_date'] ?? '', false)) ?>" required></div>
    </div>
    <div><button class="btn btn-primary">Simpan</button> <a class="btn btn-light" href="<?= site_url('students') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
