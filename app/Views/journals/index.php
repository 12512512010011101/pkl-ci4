<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $badge = ['pending' => 'secondary', 'approved' => 'success', 'rejected' => 'danger']; $isMentor = session('role') === 'pembimbing'; ?>
<div class="d-flex justify-content-between mb-3">
    <h4>Jurnal Kegiatan</h4>
    <?php if (! $isMentor): ?><a class="btn btn-primary" href="<?= site_url('journals/new') ?>">+ Tambah jurnal</a>
    <?php else: ?>
        <div class="btn-group">
            <a class="btn btn-outline-secondary" href="<?= site_url('journals') ?>">Semua</a>
            <a class="btn btn-outline-secondary" href="<?= site_url('journals?status=pending') ?>">Menunggu</a>
        </div>
    <?php endif ?>
</div>
<?php foreach ($rows as $r): ?>
    <div class="card mb-3"><div class="card-body">
        <div class="d-flex justify-content-between">
            <div><strong><?= esc($r['date']) ?></strong><?php if ($isMentor): ?> &middot; <?= esc($r['name']) ?><?php endif ?></div>
            <span class="badge text-bg-<?= $badge[$r['status']] ?>"><?= esc($r['status']) ?></span>
        </div>
        <p class="mt-2 mb-2"><?= nl2br(esc($r['activity'])) ?></p>
        <?php if ($r['evidence']): ?><a href="<?= base_url('uploads/journals/' . $r['evidence']) ?>" target="_blank">Lihat bukti kegiatan</a><?php endif ?>
        <?php if ($r['mentor_note']): ?><div class="text-muted mt-1">Catatan pembimbing: <?= esc($r['mentor_note']) ?></div><?php endif ?>

        <?php if ($isMentor): ?>
            <form method="post" action="<?= site_url('journals/review/' . $r['id']) ?>" class="d-flex gap-2 mt-2">
                <?= csrf_field() ?>
                <input name="mentor_note" class="form-control" placeholder="Catatan (opsional)" value="<?= esc($r['mentor_note']) ?>">
                <button name="action" value="approve" class="btn btn-success">Setujui</button>
                <button name="action" value="reject" class="btn btn-outline-danger">Tolak</button>
            </form>
        <?php elseif ($r['status'] === 'pending'): ?>
            <form method="post" action="<?= site_url('journals/delete/' . $r['id']) ?>" class="mt-2" onsubmit="return confirm('Hapus jurnal ini?')">
                <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
        <?php endif ?>
    </div></div>
<?php endforeach ?>
<?php if (! $rows): ?><div class="text-center text-muted">Belum ada jurnal.</div><?php endif ?>
<?= $this->endSection() ?>
