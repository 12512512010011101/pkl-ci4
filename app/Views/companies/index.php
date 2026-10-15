<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between mb-3"><h4>Tempat PKL</h4><a class="btn btn-primary" href="<?= site_url('companies/new') ?>">+ Tambah</a></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Nama</th><th>Alamat</th><th>Telepon</th><th>Pembimbing lapangan</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= esc($r['name']) ?></td><td><?= esc($r['address']) ?></td><td><?= esc($r['phone']) ?></td><td><?= esc($r['pic']) ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('companies/edit/' . $r['id']) ?>">Edit</a>
                   <a class="btn btn-sm btn-outline-success" href="<?= site_url('companies/location/' . $r['id']) ?>"><?= $r['latitude'] !== null ? 'Lokasi (terkunci)' : 'Kunci lokasi' ?></a>
                <form class="d-inline" method="post" action="<?= site_url('companies/delete/' . $r['id']) ?>" onsubmit="return confirm('Hapus perusahaan ini?')">
                    <?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </td></tr>
    <?php endforeach ?>
    <?php if (! $rows): ?><tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr><?php endif ?>
    </tbody></table></div></div>
<?= $this->endSection() ?>
