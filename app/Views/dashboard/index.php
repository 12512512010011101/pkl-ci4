<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php
$jam    = (int) date('G');
$salam  = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
$tone   = static fn ($p) => $p >= 70 ? 'ok' : ($p >= 40 ? 'mid' : 'low');
$isSiswa = session('role') === 'siswa';
?>

<div class="banner mb-4">
    <div>
        <h4><?= $salam ?>, <?= esc(session('name')) ?> 👋</h4>
        <p><?= $isSiswa ? 'Pantau progres PKL kamu hari ini.' : 'Ringkasan kegiatan siswa bimbinganmu.' ?></p>
    </div>
    <span class="chip"><?= $isSiswa ? 'Siswa' : 'Pembimbing' ?></span>
</div>

<?php if ($isSiswa): ?>
    <?php if (! $s): ?>
        <div class="alert alert-warning">Data PKL kamu belum dilengkapi. Hubungi pembimbing.</div>
    <?php else:
        $p    = max(0, min(100, (int) \App\Models\StudentModel::progress($s)));
        $circ = 2 * M_PI * 52;
        $off  = $circ * (1 - $p / 100);
        $sisa = null;
        if (! empty($s['end_date'])) {
            $d    = (new DateTime('today'))->diff(new DateTime($s['end_date']));
            $sisa = $d->invert ? 0 : $d->days;
        }
    ?>

        <!-- Profil + ring progres -->
        <div class="card mb-4"><div class="card-body p-3 p-md-4">
            <div class="profile">
                <div class="ring">
                    <svg width="100%" height="100%" viewBox="0 0 120 120">
                        <circle class="bg" cx="60" cy="60" r="52" fill="none" stroke-width="11"/>
                        <circle class="fg" cx="60" cy="60" r="52" fill="none" stroke-width="11"
                                stroke-dasharray="<?= $circ ?>" stroke-dashoffset="<?= $off ?>"/>
                    </svg>
                    <div class="ring-text"><strong><?= $p ?>%</strong><span>progres</span></div>
                </div>
                <div class="profile-info">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-lg"><?= esc(strtoupper(mb_substr($s['name'], 0, 1))) ?></div>
                        <div class="min-w-0">
                            <h5 class="mb-0 text-truncate"><?= esc($s['name']) ?></h5>
                            <span class="chip chip-brand"><?= esc($s['kelas']) ?> &middot; <?= esc($s['jurusan']) ?></span>
                        </div>
                    </div>
                    <div class="meta-row"><span>📍</span><b><?= esc($s['company_name'] ?? '-') ?></b></div>
                    <div class="meta-row"><span>🗓</span><span><?= esc($s['start_date']) ?> s/d <?= esc($s['end_date']) ?></span></div>
                    <?php if ($sisa !== null): ?>
                        <div class="meta-row"><span>⏳</span><span><?= $sisa > 0 ? 'Sisa <b>' . $sisa . ' hari</b> lagi' : 'Masa PKL telah berakhir' ?></span></div>
                    <?php endif ?>
                </div>
            </div>
        </div></div>

        <!-- Statistik -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3"><div class="stat-card">
                <div class="stat-icon i-ok">✅</div>
                <div><div class="stat-label">Hadir</div><div class="stat-num"><?= esc($s['hadir']) ?></div></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="stat-card">
                <div class="stat-icon i-info">📝</div>
                <div><div class="stat-label">Izin / Sakit</div><div class="stat-num"><?= esc($s['izin']) ?> / <?= esc($s['sakit']) ?></div></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="stat-card">
                <div class="stat-icon i-mid">📒</div>
                <div><div class="stat-label">Jurnal disetujui</div><div class="stat-num"><?= esc($s['jurnal_ok']) ?>/<?= esc($s['jurnal_all']) ?></div></div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="stat-card">
                <div class="stat-icon i-brand">🏆</div>
                <div><div class="stat-label">Nilai akhir</div><div class="stat-num"><?= $s['nilai'] !== null ? esc($s['nilai']) : '-' ?></div></div>
            </div></div>
        </div>

        <!-- Menu cepat -->
        <h6 class="mb-3">Menu cepat</h6>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <a class="tile" href="<?= site_url('attendance') ?>">
                    <div class="stat-icon i-ok">📍</div><div><b>Absensi</b><small>Absen masuk &amp; pulang</small></div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a class="tile" href="<?= site_url('journals/new') ?>">
                    <div class="stat-icon i-mid">✍️</div><div><b>Tulis jurnal</b><small>Catat kegiatan hari ini</small></div>
                </a>
            </div>
            <div class="col-12 col-md-4">
                <a class="tile" href="<?= site_url('leaves') ?>">
                    <div class="stat-icon i-info">📨</div><div><b>Izin</b><small>Ajukan izin / sakit</small></div>
                </a>
            </div>
        </div>
    <?php endif ?>

<?php else: ?>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4"><div class="stat-card">
            <div class="stat-icon i-brand">👥</div>
            <div><div class="stat-label">Siswa bimbingan</div><div class="stat-num"><?= esc($stat['siswa']) ?></div></div>
        </div></div>
        <div class="col-6 col-sm-4">
            <a class="stat-card" href="<?= site_url('journals?status=pending') ?>">
                <div class="stat-icon i-mid">📒</div>
                <div><div class="stat-label">Jurnal menunggu</div><div class="stat-num"><?= esc($stat['jurnal']) ?></div></div>
            </a>
        </div>
        <div class="col-6 col-sm-4">
            <a class="stat-card" href="<?= site_url('leaves') ?>">
                <div class="stat-icon i-info">📝</div>
                <div><div class="stat-label">Izin menunggu</div><div class="stat-num"><?= esc($stat['izin']) ?></div></div>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Monitoring progres PKL</span>
            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('students') ?>">Lihat semua</a>
        </div>

        <!-- Tabel (PC) -->
        <div class="table-responsive d-none d-md-block">
            <table class="table align-middle mb-0">
                <thead><tr><th>Siswa</th><th>Tempat PKL</th><th style="width:26%">Progres</th><th>Hadir</th><th>Jurnal</th><th>Nilai</th></tr></thead>
                <tbody>
                <?php foreach ($students as $r): $p = max(0, min(100, (int) \App\Models\StudentModel::progress($r))); ?>
                    <tr>
                        <td>
                            <div class="row-user">
                                <div class="avatar"><?= esc(strtoupper(mb_substr($r['name'], 0, 1))) ?></div>
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate"><?= esc($r['name']) ?></div>
                                    <small class="text-muted"><?= esc($r['kelas']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= esc($r['company_name'] ?? '-') ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="pbar flex-fill"><span class="<?= $tone($p) ?>" style="width: <?= $p ?>%"></span></div>
                                <small class="fw-semibold" style="width:2.6rem"><?= $p ?>%</small>
                            </div>
                        </td>
                        <td><span class="chip chip-ok"><?= esc($r['hadir']) ?> hari</span></td>
                        <td><?= esc($r['jurnal_ok']) ?>/<?= esc($r['jurnal_all']) ?></td>
                        <td>
                            <?php if ($r['nilai'] !== null): ?>
                                <span class="chip chip-brand"><?= esc($r['nilai']) ?></span>
                            <?php else: ?>
                                <span class="chip chip-muted">Belum</span>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if (! $students): ?>
                    <tr><td colspan="6"><div class="empty"><div class="big">🎓</div>Belum ada siswa.</div></td></tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>

        <!-- Kartu (HP) -->
        <div class="d-md-none">
            <?php foreach ($students as $r): $p = max(0, min(100, (int) \App\Models\StudentModel::progress($r))); ?>
                <div class="student-item">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="row-user">
                            <div class="avatar"><?= esc(strtoupper(mb_substr($r['name'], 0, 1))) ?></div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate"><?= esc($r['name']) ?></div>
                                <div class="small text-muted text-truncate"><?= esc($r['company_name'] ?? '-') ?></div>
                            </div>
                        </div>
                        <?php if ($r['nilai'] !== null): ?>
                            <span class="chip chip-brand flex-shrink-0">Nilai <?= esc($r['nilai']) ?></span>
                        <?php else: ?>
                            <span class="chip chip-muted flex-shrink-0">Belum dinilai</span>
                        <?php endif ?>
                    </div>
                    <div class="d-flex align-items-center gap-2 my-2">
                        <div class="pbar flex-fill"><span class="<?= $tone($p) ?>" style="width: <?= $p ?>%"></span></div>
                        <small class="fw-semibold"><?= $p ?>%</small>
                    </div>
                    <div class="d-flex gap-3 small text-muted">
                        <span>Hadir: <b class="text-body"><?= esc($r['hadir']) ?></b></span>
                        <span>Jurnal: <b class="text-body"><?= esc($r['jurnal_ok']) ?>/<?= esc($r['jurnal_all']) ?></b></span>
                    </div>
                </div>
            <?php endforeach ?>
            <?php if (! $students): ?>
                <div class="empty"><div class="big">🎓</div>Belum ada siswa.</div>
            <?php endif ?>
        </div>
    </div>

<?php endif ?>
<?= $this->endSection() ?>