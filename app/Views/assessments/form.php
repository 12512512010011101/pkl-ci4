<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<h4 class="mb-1">Penilaian: <?= esc($s['name']) ?></h4>
<p class="text-muted">Nilai 0-100. Nilai akhir = rata-rata keempat aspek.</p>
<form method="post" action="<?= site_url('assessments/' . $s['id']) ?>" class="card card-body" style="max-width:640px">
    <?= csrf_field() ?>
    <div class="row">
        <?php foreach (['disiplin' => 'Disiplin', 'keterampilan' => 'Keterampilan', 'sikap' => 'Sikap kerja', 'laporan' => 'Laporan'] as $f => $label): ?>
            <div class="col-6 mb-3"><label class="form-label"><?= $label ?></label>
                <input type="number" min="0" max="100" name="<?= $f ?>" class="form-control" value="<?= esc(old($f, $a[$f] ?? '', false)) ?>" required></div>
        <?php endforeach ?>
    </div>
    <div class="mb-3"><label class="form-label">Catatan</label>
        <textarea name="note" rows="3" class="form-control"><?= esc(old('note', $a['note'] ?? '', false)) ?></textarea></div>
    <div><button class="btn btn-primary">Simpan</button> <a class="btn btn-light" href="<?= site_url('assessments') ?>">Batal</a></div>
</form>
<?= $this->endSection() ?>
