<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= $c ? 'Edit' : 'Tambah' ?> Tempat PKL</h4>
<form method="post" action="<?= site_url($c ? 'companies/update/' . $c['id'] : 'companies') ?>" class="card card-body" style="max-width:640px">
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Nama perusahaan</label>
        <input name="name" class="form-control" value="<?= esc(old('name', $c['name'] ?? '', false)) ?>" required></div>
    <div class="mb-3"><label class="form-label">Alamat</label>
        <textarea name="address" class="form-control" rows="2"><?= esc(old('address', $c['address'] ?? '', false)) ?></textarea></div>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Telepon</label>
            <input name="phone" class="form-control" value="<?= esc(old('phone', $c['phone'] ?? '', false)) ?>"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Pembimbing lapangan</label>
            <input name="pic" class="form-control" value="<?= esc(old('pic', $c['pic'] ?? '', false)) ?>"></div>
    </div>
    <div><button class="btn btn-primary">Simpan</button> <a class="btn btn-light" href="<?= site_url('companies') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
