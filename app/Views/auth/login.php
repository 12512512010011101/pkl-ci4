<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistem PKL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width:420px">
    <div class="card shadow-sm mt-5">
        <div class="card-body p-4">
            <h4 class="mb-1">Sistem Manajemen PKL</h4>
            <p class="text-muted">Masuk sebagai siswa atau pembimbing</p>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>
            <form method="post" action="<?= site_url('login') ?>">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= esc(old('email', null, false)) ?>" required autofocus></div>
                <div class="mb-3"><label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required></div>
                <button class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
