<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="small text-muted"><?= count($bookings) ?> booking ditemukan</div>
    <a href="<?= base_url('reports/bookings/export?' . http_build_query($filters)) ?>" class="btn btn-success">
        <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
    </a>
</div>

<!-- Filter -->
<div class="form-section mb-4">
    <form method="get" action="<?= base_url('reports/bookings') ?>" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="tanggal_dari" class="form-control" value="<?= esc($filters['tanggal_dari']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="tanggal_sampai" class="form-control" value="<?= esc($filters['tanggal_sampai']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Ruangan</label>
            <select name="room_id" class="form-select">
                <option value="">Semua Ruangan</option>
                <?php foreach ($rooms as $r): ?>
                <option value="<?= $r['id'] ?>" <?= (string) $filters['room_id'] === (string) $r['id'] ? 'selected' : '' ?>>
                    <?= esc($r['nama_ruangan']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <?php foreach ($statusLabels as $v => $l): ?>
                <option value="<?= $v ?>" <?= $filters['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search"></i></button>
            <?php if ($filters['tanggal_dari'] || $filters['tanggal_sampai'] || $filters['room_id'] || $filters['status']): ?>
            <a href="<?= base_url('reports/bookings') ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
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
                    <th>Ruangan</th>
                    <th>Peminjam</th>
                    <th>Keperluan</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th>Jam</th>
                    <th class="text-center">Peserta</th>
                    <th>Konsumsi</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $i => $b): ?>
                <tr>
                    <td class="text-muted" style="font-size:.8rem"><?= $i + 1 ?></td>
                    <td style="font-size:.85rem">
                        <div class="fw-semibold"><?= esc($b['nama_ruangan']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><?= esc($b['kode_ruangan']) ?></div>
                    </td>
                    <td class="fw-medium" style="font-size:.85rem"><?= esc($b['nama_peminjam']) ?></td>
                    <td class="text-muted" style="font-size:.82rem"><?= esc(mb_strimwidth($b['keperluan'], 0, 35, '...')) ?></td>
                    <td style="font-size:.82rem"><?= date('d M Y', strtotime($b['tanggal_mulai'])) ?></td>
                    <td style="font-size:.82rem"><?= date('d M Y', strtotime($b['tanggal_selesai'])) ?></td>
                    <td style="font-size:.82rem"><?= substr($b['jam_mulai'], 0, 5) ?>–<?= substr($b['jam_selesai'], 0, 5) ?></td>
                    <td class="text-center" style="font-size:.82rem"><?= $b['jumlah_peserta'] ?></td>
                    <td class="text-muted" style="font-size:.8rem"><?= esc($b['konsumsi'] ?: '-') ?></td>
                    <td class="text-center">
                        <?php
                        $sm = ['pending'=>['Pending','badge-pending'],'approved'=>['Disetujui','badge-approved'],'rejected'=>['Ditolak','badge-rejected'],'cancelled'=>['Batal','badge-cancelled'],'selesai'=>['Selesai','badge-selesai']];
                        [$bl,$bc] = $sm[$b['status']] ?? [$b['status'],'badge-cancelled'];
                        ?>
                        <span class="badge px-2 <?= $bc ?>"><?= $bl ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                <tr><td colspan="10" class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x d-block fs-2 mb-2 opacity-25"></i>
                    Tidak ada data booking untuk filter ini
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
