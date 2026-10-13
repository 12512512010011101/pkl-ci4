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
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <style>@media print { .no-print { display: none !important; } }</style>
</head>
<body class="bg-light">

<nav class="navbar bg-dark navbar-dark topbar no-print">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= site_url('dashboard') ?>">Sistem PKL</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#menuUtama" aria-controls="menuUtama"
            aria-expanded="false" aria-label="Buka menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuUtama">
      <div class="menu-user">
        <span class="fw-semibold text-white"><?= esc(session('name')) ?></span>
        <span class="badge bg-secondary"><?= $role === 'pembimbing' ? 'Pembimbing' : 'Siswa' ?></span>
      </div>

      <ul class="navbar-nav">
        <?php foreach ($menu as $path => $label): ?>
          <li class="nav-item">
            <a class="nav-link <?= url_is($path . '*') ? 'active' : '' ?>"
               href="<?= site_url($path) ?>"><?= $label ?></a>
          </li>
        <?php endforeach ?>
      </ul>

      <form method="post" action="<?= site_url('logout') ?>" class="mt-2 mb-1">
        <?= csrf_field() ?>
        <button class="btn btn-outline-light btn-sm w-100">Keluar</button>
      </form>
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