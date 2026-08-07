<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('bookings') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="fw-bold" style="font-size:1rem">Detail Booking</div>
</div>

<?php
$statusCfg = [
    'pending'   => ['Menunggu Konfirmasi', 'warning',   'bi-clock-history',     '#d97706', '#fffbeb'],
    'approved'  => ['Disetujui',           'success',   'bi-check-circle-fill', '#16a34a', '#f0fdf4'],
    'rejected'  => ['Ditolak',             'danger',    'bi-x-circle-fill',     '#dc2626', '#fef2f2'],
    'cancelled' => ['Dibatalkan',          'secondary', 'bi-dash-circle',       '#6b7280', '#f9fafb'],
    'selesai'   => ['Selesai',             'primary',   'bi-flag-fill',         '#2563eb', '#eff6ff'],
];
[$slabel, $stype, $sicon, $scolor, $sbg] = $statusCfg[$booking['status']] ?? [$booking['status'], 'secondary', 'bi-circle', '#6b7280', '#f9fafb'];
?>

<div class="row g-3">
    <div class="col-lg-8">

        <!-- Status Banner -->
        <div class="rounded-4 p-3 mb-3 d-flex align-items-center gap-3" style="background:<?= $sbg ?>; border:1.5px solid <?= $scolor ?>33">
            <div style="width:44px;height:44px;border-radius:12px;background:<?= $scolor ?>22;display:flex;align-items:center;justify-content:center;color:<?= $scolor ?>;font-size:1.3rem;flex-shrink:0">
                <i class="bi <?= $sicon ?>"></i>
            </div>
            <div>
                <div class="fw-bold" style="color:<?= $scolor ?>">Status: <?= $slabel ?></div>
                <div style="font-size:.78rem; color:#6b7280">Dibuat <?= date('d M Y, H:i', strtotime($booking['created_at'])) ?></div>
            </div>
        </div>

        <!-- Peminjam -->
        <div class="form-section mb-3">
            <div class="form-section-title"><i class="bi bi-person-fill"></i>Informasi Peminjam</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Nama Peminjam</div>
                    <div class="fw-semibold"><?= esc($booking['nama_peminjam']) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Kontak</div>
                    <div class="fw-semibold"><?= esc($booking['kontak'] ?: '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Instansi / Unit Kerja</div>
                    <div class="fw-semibold"><?= esc($booking['instansi'] ?: '-') ?></div>
                </div>
                <div class="col-md-8">
                    <div class="text-muted small mb-1">Keperluan</div>
                    <div class="fw-semibold"><?= nl2br(esc($booking['keperluan'])) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Jumlah Peserta</div>
                    <div class="fw-semibold"><?= $booking['jumlah_peserta'] ?> orang</div>
                </div>
            </div>
        </div>

        <!-- Jadwal -->
        <div class="form-section mb-3">
            <div class="form-section-title"><i class="bi bi-calendar3"></i>Jadwal Penggunaan</div>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small mb-1">Tanggal Mulai</div>
                    <div class="fw-semibold"><?= date('d M Y', strtotime($booking['tanggal_mulai'])) ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small mb-1">Tanggal Selesai</div>
                    <div class="fw-semibold"><?= date('d M Y', strtotime($booking['tanggal_selesai'])) ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small mb-1">Jam Mulai</div>
                    <div class="fw-semibold" style="font-size:1.1rem; color:#8b1a24"><?= substr($booking['jam_mulai'],0,5) ?></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small mb-1">Jam Selesai</div>
                    <div class="fw-semibold" style="font-size:1.1rem; color:#8b1a24"><?= substr($booking['jam_selesai'],0,5) ?></div>
                </div>
            </div>
        </div>

        <?php if (!empty($booking['konsumsi'])): ?>
        <div class="form-section mb-3">
            <div class="form-section-title"><i class="bi bi-cup-hot-fill"></i>Konsumsi</div>
            <p class="mb-3 text-muted"><?= esc($booking['konsumsi']) ?></p>

            <?php if (is_admin_role() && $booking['status'] === 'pending'): ?>
            <hr class="my-3">
            <div class="fw-semibold mb-2" style="font-size:.85rem">
                <i class="bi bi-cash-coin me-1" style="color:#8b1a24"></i>Konfirmasi Penanggung Biaya
            </div>
            <form method="post" action="<?= base_url("bookings/confirm-konsumsi/{$booking['id']}") ?>">
                <?= csrf_field() ?>
                <div class="row g-3 align-items-end">
                    <div class="col-sm-8">
                        <label class="form-label small mb-1 d-block">Penanggung Biaya</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="penanggung_biaya" id="pusdiklat" value="pusdiklat"
                                       <?= ($booking['penanggung_biaya'] ?? '') === 'pusdiklat' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="pusdiklat" style="font-size:.85rem">Pusdiklat</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="penanggung_biaya" id="unit_peminjam" value="unit_peminjam"
                                       <?= ($booking['penanggung_biaya'] ?? '') === 'unit_peminjam' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="unit_peminjam" style="font-size:.85rem">Unit Peminjam</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                    </div>
                </div>
            </form>
            <?php elseif ($booking['penanggung_biaya']): ?>
            <hr class="my-3">
            <div class="text-muted small mb-1">Penanggung Biaya</div>
            <div class="fw-semibold">
                <?= $booking['penanggung_biaya'] === 'pusdiklat' ? 'Pusdiklat' : 'Unit Peminjam' ?>
                <?php if ($booking['penanggung_biaya'] === 'unit_peminjam' && $booking['instansi']): ?>
                <span class="text-muted fw-normal">(<?= esc($booking['instansi']) ?>)</span>
                <?php endif; ?>
            </div>
            <?php elseif ($booking['status'] === 'pending'): ?>
            <hr class="my-3">
            <div class="text-muted" style="font-size:.8rem"><i class="bi bi-hourglass-split me-1"></i>Penanggung biaya belum dikonfirmasi admin.</div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($booking['catatan']): ?>
        <div class="form-section mb-3">
            <div class="form-section-title"><i class="bi bi-chat-text-fill"></i>Catatan</div>
            <p class="mb-0 text-muted"><?= nl2br(esc($booking['catatan'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <?php $needsPenanggung = $booking['status'] === 'pending' && !empty($booking['konsumsi']) && empty($booking['penanggung_biaya']); ?>
        <?php if ($needsPenanggung && is_admin_role()): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="font-size:.85rem">
            <i class="bi bi-exclamation-triangle-fill"></i>
            Booking ini memilih konsumsi. Konfirmasi &amp; simpan <strong class="mx-1">Penanggung Biaya</strong> di atas terlebih dahulu sebelum bisa menyetujui.
        </div>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($booking['status'] === 'pending'): ?>
            <?php if (is_admin_role()): ?>
            <form method="post" action="<?= base_url("bookings/approve/{$booking['id']}") ?>">
                <?= csrf_field() ?>
                <button class="btn btn-success" <?= $needsPenanggung ? 'disabled title="Konfirmasi Penanggung Biaya terlebih dahulu"' : '' ?>>
                    <i class="bi bi-check-circle me-1"></i>Setujui
                </button>
            </form>
            <form method="post" action="<?= base_url("bookings/reject/{$booking['id']}") ?>">
                <?= csrf_field() ?>
                <button class="btn btn-danger" onclick="return confirm('Tolak booking ini?')">
                    <i class="bi bi-x-circle me-1"></i>Tolak
                </button>
            </form>
            <?php endif; ?>
            <a href="<?= base_url("bookings/edit/{$booking['id']}") ?>" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <?php endif; ?>

            <?php if ($booking['status'] === 'approved' && is_admin_role()): ?>
            <form method="post" action="<?= base_url("bookings/selesai/{$booking['id']}") ?>">
                <?= csrf_field() ?>
                <button class="btn btn-primary"><i class="bi bi-flag me-1"></i>Tandai Selesai</button>
            </form>
            <?php endif; ?>

            <?php if (in_array($booking['status'], ['pending','approved'])): ?>
            <form method="post" action="<?= base_url("bookings/cancel/{$booking['id']}") ?>">
                <?= csrf_field() ?>
                <button class="btn btn-outline-secondary" onclick="return confirm('Batalkan booking ini?')">
                    <i class="bi bi-dash-circle me-1"></i>Batalkan
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Ruangan -->
    <div class="col-lg-4">
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-door-open me-2 text-success"></i>Informasi Ruangan</h6>
            </div>
            <div class="p-4">
                <span class="badge mb-2" style="background:#fbe9ea; color:#8b1a24; font-weight:600"><?= esc($booking['kode_ruangan']) ?></span>
                <div class="fw-bold mb-1" style="font-size:1rem"><?= esc($booking['nama_ruangan']) ?></div>
                <div class="text-muted small mb-3"><i class="bi bi-geo-alt me-1"></i><?= esc($booking['lokasi']) ?></div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="text-center p-3 rounded-3" style="background:#f3f6f4">
                            <div class="fw-bold fs-5" style="color:#8b1a24"><?= $booking['kapasitas'] ?></div>
                            <div class="text-muted" style="font-size:.72rem">Kapasitas</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-3 rounded-3" style="background:#f3f6f4">
                            <div class="fw-bold fs-5" style="color:#8b1a24"><?= $booking['jumlah_peserta'] ?></div>
                            <div class="text-muted" style="font-size:.72rem">Peserta</div>
                        </div>
                    </div>
                </div>

                <?php if ($booking['fasilitas']): ?>
                <div class="mb-3">
                    <div class="text-muted small mb-2">Fasilitas</div>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach (explode(',', $booking['fasilitas']) as $f): ?>
                        <span style="background:#fbe9ea;color:#8b1a24;font-size:.72rem;padding:.2rem .55rem;border-radius:6px;font-weight:500"><?= esc(trim($f)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <a href="<?= base_url("rooms/{$booking['room_id']}") ?>" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-door-open me-1"></i>Lihat Detail Ruangan
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
