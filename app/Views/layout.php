<?php
$role = session('role');
$menu = $role === 'pembimbing'
    ? ['dashboard' => 'Dashboard', 'students' => 'Siswa', 'companies' => 'Tempat PKL', 'attendance' => 'Absensi', 'journals' => 'Jurnal', 'leaves' => 'Izin', 'assessments' => 'Penilaian', 'reports' => 'Laporan']
    : ['dashboard' => 'Dashboard', 'attendance' => 'Absensi', 'journals' => 'Jurnal', 'leaves' => 'Izin'];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Manajemen PKL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('dashboard') ?>">PKL Manager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($menu as $path => $label): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url($path) ?>"><?= $label ?></a></li>
                <?php endforeach ?>
            </ul>
            <span class="navbar-text me-3"><?= esc(session('name')) ?> (<?= esc($role) ?>)</span>
            <a class="btn btn-sm btn-light" href="<?= site_url('logout') ?>">Keluar</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger"><ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach ?>
        </ul></div>
    <?php endif ?>

    <?= $this->renderSection('content') ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
