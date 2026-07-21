<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="small text-muted"><?= count($bookings) ?> booking ditemukan</div>
    <a href="<?= base_url('bookings/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Buat Booking
    </a>
</div>

<!-- Filter -->
<div class="form-section mb-4">
    <form method="get" action="<?= base_url('bookings') ?>" class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label">Cari</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Nama peminjam, ruangan, keperluan..."
                   value="<?= esc($search) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <?php foreach (['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak','cancelled'=>'Dibatalkan','selesai'=>'Selesai'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= $status===$v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
            <?php if ($search || $status): ?>
            <a href="<?= base_url('bookings') ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="section-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Peminjam</th>
                    <th>Ruangan</th>
                    <th>Keperluan</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th class="text-center">Peserta</th>
                    <th>Konsumsi</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $i => $b): ?>
                <tr>
                    <td class="text-muted" style="font-size:.8rem"><?= $i+1 ?></td>
                    <td>
                        <div class="fw-semibold" style="font-size:.85rem"><?= esc($b['nama_peminjam']) ?></div>
                        <?php if ($b['instansi']): ?>
                        <div class="text-muted" style="font-size:.75rem"><?= esc($b['instansi']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:.85rem"><?= esc($b['nama_ruangan']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><?= esc($b['kode_ruangan']) ?></div>
                    </td>
                    <td class="text-muted" style="font-size:.82rem"><?= esc(mb_strimwidth($b['keperluan'],0,40,'...')) ?></td>
                    <td style="font-size:.82rem">
                        <?= date('d M Y', strtotime($b['tanggal_mulai'])) ?>
                        <?php if ($b['tanggal_mulai'] !== $b['tanggal_selesai']): ?>
                        <div class="text-muted" style="font-size:.75rem">s/d <?= date('d M Y', strtotime($b['tanggal_selesai'])) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.82rem"><?= substr($b['jam_mulai'],0,5) ?>–<?= substr($b['jam_selesai'],0,5) ?></td>
                    <td class="text-center" style="font-size:.85rem"><?= $b['jumlah_peserta'] ?></td>
                    <td style="font-size:.8rem" class="text-muted"><?= $b['konsumsi'] ? esc($b['konsumsi']) : '-' ?></td>
                    <td class="text-center">
                        <?php
                        $sm = ['pending'=>['Menunggu','badge-pending'],'approved'=>['Disetujui','badge-approved'],'rejected'=>['Ditolak','badge-rejected'],'cancelled'=>['Batal','badge-cancelled'],'selesai'=>['Selesai','badge-selesai']];
                        [$bl,$bc] = $sm[$b['status']] ?? [$b['status'],'badge-cancelled'];
                        ?>
                        <span class="badge px-2 py-1 <?= $bc ?>" style="font-size:.75rem"><?= $bl ?></span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="<?= base_url("bookings/{$b['id']}") ?>"
                               class="btn btn-sm btn-outline-primary" style="padding:.2rem .5rem" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($b['status'] === 'pending' && is_admin_role()): ?>
                            <form method="post" action="<?= base_url("bookings/approve/{$b['id']}") ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-success" style="padding:.2rem .5rem" title="Setujui">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <form method="post" action="<?= base_url("bookings/reject/{$b['id']}") ?>" class="d-inline"
                                  onsubmit="return confirm('Tolak booking ini?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-danger" style="padding:.2rem .5rem" title="Tolak">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x d-block fs-1 mb-2 opacity-25"></i>
                        Tidak ada data booking
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
