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
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body class="bg-light">
<?php
$role = session()->get('role'); // sesuaikan kunci sesinya
$menu = [
    ['dashboard',   'Dashboard',   ['pembimbing', 'siswa']],
    ['students',    'Siswa',       ['pembimbing']],
    ['companies',   'Tempat PKL',  ['pembimbing']],
    ['attendance',  'Absensi',     ['pembimbing', 'siswa']],
    ['journals',    'Jurnal',      ['pembimbing', 'siswa']],
    ['leaves',      'Izin',        ['pembimbing', 'siswa']],
    ['assessments', 'Penilaian',   ['pembimbing']],
    ['reports',     'Laporan',     ['pembimbing']],
];
?>

<!-- Tombol menu untuk HP -->
<nav class="navbar bg-dark d-md-none px-3">
  <span class="navbar-brand text-white mb-0">Sistem PKL</span>
  <button class="btn btn-outline-light btn-sm" data-bs-toggle="offcanvas" data-bs-target="#sidebar">☰ Menu</button>
</nav>

<div class="app-wrap">
  <aside class="offcanvas-md offcanvas-start sidebar" tabindex="-1" id="sidebar">
    <div class="sidebar-brand">
      Sistem PKL
      <button class="btn-close btn-close-white d-md-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
    </div>

    <div class="sidebar-user">
      <div class="fw-semibold"><?= esc(session()->get('name')) ?></div>
      <span class="badge bg-secondary"><?= $role === 'pembimbing' ? 'Pembimbing' : 'Siswa' ?></span>
    </div>

    <ul class="nav flex-column sidebar-menu">
      <?php foreach ($menu as [$path, $label, $roles]): ?>
        <?php if (in_array($role, $roles)): ?>
          <li class="nav-item">
            <a class="nav-link <?= url_is($path . '*') ? 'active' : '' ?>" href="<?= site_url($path) ?>"><?= $label ?></a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>

    <form method="post" action="<?= site_url('logout') ?>" class="sidebar-logout">
      <?= csrf_field() ?>
      <button class="btn btn-outline-light w-100">Keluar</button>
    </form>
  </aside>

  <main class="content">
    <!-- PINDAHKAN flash message lama (success, error, errors) ke sini -->

    <?= $this->renderSection('content') ?>
  </main>
</div>

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
