<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('rooms') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="fw-bold" style="font-size:1rem">Detail Ruangan</div>
</div>

<?php
$statusCfg = [
    'available'   => ['Tersedia',    'badge-available',   '#16a34a', '#dcfce7'],
    'maintenance' => ['Maintenance', 'badge-maintenance', '#dc2626', '#fee2e2'],
];
[$slabel, $scls, $scolor, $sbg] = $statusCfg[$room['status']] ?? [$room['status'], '', '#6b7280', '#f3f4f6'];
?>

<div class="row g-3">
    <!-- Info Ruangan -->
    <div class="col-lg-4">
        <div class="section-card mb-3">
            <div class="room-card-bar <?= $room['status'] ?>"></div>
            <?php
                $allPhotos = [];
                if (!empty($room['foto'])) { $allPhotos[] = $room['foto']; }
                foreach ($roomPhotos ?? [] as $p) { $allPhotos[] = $p['filename']; }
            ?>
            <div class="room-detail-photo">
                <?php if (count($allPhotos) === 0): ?>
                <div class="room-card-photo-placeholder">
                    <i class="bi bi-building"></i>
                </div>
                <?php elseif (count($allPhotos) === 1): ?>
                <img src="<?= base_url('uploads/rooms/' . esc($allPhotos[0])) ?>" alt="Foto <?= esc($room['nama_ruangan']) ?>">
                <?php else: ?>
                <div id="roomPhotoCarousel" class="carousel slide h-100" data-bs-ride="false">
                    <div class="carousel-inner h-100">
                        <?php foreach ($allPhotos as $i => $fn): ?>
                        <div class="carousel-item h-100 <?= $i === 0 ? 'active' : '' ?>">
                            <img src="<?= base_url('uploads/rooms/' . esc($fn)) ?>" class="d-block w-100 h-100"
                                 style="object-fit:cover" alt="Foto <?= esc($room['nama_ruangan']) ?> <?= $i + 1 ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#roomPhotoCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#roomPhotoCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                    <div class="carousel-indicators room-photo-indicators">
                        <?php foreach ($allPhotos as $i => $fn): ?>
                        <button type="button" data-bs-target="#roomPhotoCarousel" data-bs-slide-to="<?= $i ?>"
                                class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="room-photo-count-badge"><i class="bi bi-images me-1"></i><?= count($allPhotos) ?> foto</div>
                </div>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <span class="badge" style="background:#f3f6f4; color:#6b7280; font-weight:600; font-size:.72rem"><?= esc($room['kode_ruangan']) ?></span>
                    <span class="badge rounded-pill px-3 py-2 <?= $scls ?>"><?= $slabel ?></span>
                </div>
                <div class="fw-bold mb-1" style="font-size:1.1rem; color:#1a2e1e"><?= esc($room['nama_ruangan']) ?></div>
                <div class="text-muted small mb-4"><i class="bi bi-geo-alt me-1" style="color:#8b1a24"></i><?= esc($room['lokasi']) ?></div>

                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="text-center p-3 rounded-3" style="background:#f3f6f4">
                            <div class="fw-bold fs-4" style="color:#8b1a24"><?= $room['kapasitas'] ?></div>
                            <div class="text-muted" style="font-size:.72rem">Kapasitas (org)</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 rounded-3" style="background:#f3f6f4">
                            <div class="fw-bold fs-4" style="color:#8b1a24"><?= count($bookings) ?></div>
                            <div class="text-muted" style="font-size:.72rem">Total Booking</div>
                        </div>
                    </div>
                </div>

                <?php if ($room['fasilitas']): ?>
                <div class="mb-3">
                    <div class="text-muted small fw-semibold mb-2" style="text-transform:uppercase; letter-spacing:.05em">Fasilitas</div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (explode(',', $room['fasilitas']) as $f): ?>
                        <span style="background:#fbe9ea; color:#8b1a24; font-size:.75rem; padding:.25rem .6rem; border-radius:6px; font-weight:500"><?= esc(trim($f)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($room['deskripsi']): ?>
                <div class="mb-4">
                    <div class="text-muted small fw-semibold mb-1" style="text-transform:uppercase; letter-spacing:.05em">Deskripsi</div>
                    <p class="small text-muted mb-0"><?= nl2br(esc($room['deskripsi'])) ?></p>
                </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <?php if (is_admin_role()): ?>
                    <a href="<?= base_url("rooms/edit/{$room['id']}") ?>" class="btn btn-warning btn-sm flex-fill">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url("bookings/create?room_id={$room['id']}") ?>" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-calendar-plus me-1"></i>Booking
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Booking -->
    <div class="col-lg-8">
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-clock-history me-2 text-success"></i>Riwayat Booking</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Keperluan</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Jam</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td class="fw-medium" style="font-size:.85rem"><?= esc($b['nama_peminjam']) ?></td>
                            <td class="text-muted" style="font-size:.82rem"><?= esc(mb_strimwidth($b['keperluan'], 0, 40, '...')) ?></td>
                            <td style="font-size:.82rem"><?= date('d M Y', strtotime($b['tanggal_mulai'])) ?></td>
                            <td style="font-size:.82rem">
                                <?php if ($b['tanggal_selesai'] !== $b['tanggal_mulai']): ?>
                                    <?= date('d M Y', strtotime($b['tanggal_selesai'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">–</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.82rem"><?= substr($b['jam_mulai'],0,5) ?>–<?= substr($b['jam_selesai'],0,5) ?></td>
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
                        <tr><td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x d-block fs-2 mb-2 opacity-25"></i>
                            Belum ada booking
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
