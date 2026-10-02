<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Tambah Jurnal Harian</h4>
<form method="post" action="<?= site_url('journals') ?>" enctype="multipart/form-data" class="card card-body" style="max-width:640px">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Tanggal</label>
        <input type="date" name="date" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= esc(old('date', date('Y-m-d'), false)) ?>" required></div>
    <div class="mb-3"><label class="form-label">Kegiatan hari ini</label>
        <textarea name="activity" rows="5" class="form-control" required><?= esc(old('activity', null, false)) ?></textarea></div>
    <div class="mb-3"><label class="form-label">Bukti kegiatan (jpg/png/pdf, maks 2 MB)</label>
        <input type="file" name="evidence" class="form-control" accept=".jpg,.jpeg,.png,.pdf"></div>
    <div><button class="btn btn-primary">Simpan</button> <a class="btn btn-light" href="<?= site_url('journals') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
