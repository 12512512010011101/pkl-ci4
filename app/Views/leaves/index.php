<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $badge = ['pending' => 'secondary', 'approved' => 'success', 'rejected' => 'danger']; $isMentor = session('role') === 'pembimbing'; ?>
<h4 class="mb-3">Pengajuan Izin</h4>
<?php if (! $isMentor): ?>
<form method="post" action="<?= site_url('leaves') ?>" class="card card-body mb-3">
    <?= csrf_field() ?>
    <div class="row g-2">
        <div class="col-md-3"><input type="date" name="date" class="form-control" value="<?= esc(old('date', date('Y-m-d'), false)) ?>" required></div>
        <div class="col-md-2"><select name="type" class="form-select"><option value="izin">Izin</option><option value="sakit">Sakit</option></select></div>
        <div class="col-md-5"><input name="reason" class="form-control" placeholder="Alasan" value="<?= esc(old('reason', null, false)) ?>" required></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Ajukan</button></div>
    </div>
</form>
<?php endif ?>
<div class="card"><div class="table-responsive"><table class="table mb-0 align-middle">
    <thead><tr><th>Tanggal</th><?php if ($isMentor): ?><th>Siswa</th><?php endif ?><th>Jenis</th><th>Alasan</th><th>Status</th><?php if ($isMentor): ?><th></th><?php endif ?></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= esc($r['date']) ?></td><?php if ($isMentor): ?><td><?= esc($r['name']) ?></td><?php endif ?>
            <td><?= esc($r['type']) ?></td><td><?= esc($r['reason']) ?></td>
            <td><span class="badge text-bg-<?= $badge[$r['status']] ?>"><?= esc($r['status']) ?></span></td>
            <?php if ($isMentor): ?><td class="text-end">
                <?php if ($r['status'] === 'pending'): ?>
                <form method="post" action="<?= site_url('leaves/review/' . $r['id']) ?>" class="d-inline"><?= csrf_field() ?>
                    <button name="action" value="approve" class="btn btn-sm btn-success">Setujui</button>
                    <button name="action" value="reject" class="btn btn-sm btn-outline-danger">Tolak</button></form>
                <?php endif ?></td><?php endif ?>
        </tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="6" class="text-center text-muted">Belum ada pengajuan.</td></tr><?php endif ?>
    </tbody></table></div></div>
<?= $this->endSection() ?>
