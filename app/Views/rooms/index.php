+++<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="small text-muted mb-1"><?= count($rooms) ?> ruangan terdaftar</div>
    </div>
    <?php if (session()->get('role') === 'admin'): ?>
    <a href="<?= base_url('rooms/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Tambah Ruangan
    </a>
    <?php endif; ?>
</div>

<div class="row g-3">
    <?php foreach ($rooms as $room): ?>
    <?php
    $bs = $room['booking_status'] ?? $room['status'];
    $statusCfg = [
        'available'   => ['Tersedia',    'available',   'bi-check-circle-fill', '#16a34a'],
        'occupied'    => ['Terpakai',    'occupied',    'bi-people-fill',       '#d97706'],
        'maintenance' => ['Maintenance', 'maintenance', 'bi-tools',             '#dc2626'],
    ];
    [$slabel, $sclass, $sicon, $scolor] = $statusCfg[$bs] ?? [$bs, 'available', 'bi-circle', '#6b7280'];
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="room-card h-100">
            <div class="room-card-bar <?= $sclass ?>"></div>
            <div class="room-card-photo">
                <?php if (!empty($room['foto'])): ?>
                <img src="<?= base_url('uploads/rooms/' . esc($room['foto'])) ?>" alt="Foto <?= esc($room['nama_ruangan']) ?>">
                <?php else: ?>
                <div class="room-card-photo-placeholder">
                    <i class="bi bi-building"></i>
                </div>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge mb-1" style="background:#f3f6f4; color:#6b7280; font-weight:600; font-size:.72rem"><?= esc($room['kode_ruangan']) ?></span>
                        <div class="fw-bold" style="font-size:.95rem; color:#1a2e1e"><?= esc($room['nama_ruangan']) ?></div>
                    </div>
                    <span class="badge rounded-pill px-3 py-2 badge-<?= $sclass ?>">
                        <i class="bi <?= $sicon ?> me-1"></i><?= $slabel ?>
                    </span>
                </div>

                <div class="mb-3" style="font-size:.82rem; color:#6b7280">
                    <div class="mb-1"><i class="bi bi-geo-alt me-1" style="color:#8b1a24"></i><?= esc($room['lokasi']) ?></div>
                    <div class="mb-1"><i class="bi bi-people me-1" style="color:#8b1a24"></i>Kapasitas <?= $room['kapasitas'] ?> orang</div>
                    <?php if ($room['fasilitas']): ?>
                    <div class="mt-2 d-flex flex-wrap gap-1">
                        <?php foreach (array_slice(explode(',', $room['fasilitas']), 0, 3) as $f): ?>
                        <span style="background:#f3f6f4; color:#374151; font-size:.72rem; padding:.2rem .55rem; border-radius:6px; font-weight:500"><?= esc(trim($f)) ?></span>
                        <?php endforeach; ?>
                        <?php if (count(explode(',', $room['fasilitas'])) > 3): ?>
                        <span style="background:#f3f6f4; color:#9ca3af; font-size:.72rem; padding:.2rem .55rem; border-radius:6px">+<?= count(explode(',', $room['fasilitas'])) - 3 ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($room['active_booking']) && $room['active_booking']): ?>
                <div class="rounded-3 p-2 mb-3" style="background:#fffbeb; border:1px solid #fde68a">
                    <small style="color:#d97706; font-weight:600; font-size:.75rem">
                        <i class="bi bi-clock me-1"></i>
                        <?= esc($room['active_booking']['nama_peminjam']) ?> &mdash;
                        <?= substr($room['active_booking']['jam_mulai'],0,5) ?>–<?= substr($room['active_booking']['jam_selesai'],0,5) ?>
                    </small>
                </div>
                <?php endif; ?>

                <div class="d-flex gap-2 mt-auto">
                    <a href="<?= base_url("rooms/{$room['id']}") ?>" class="btn btn-sm btn-outline-primary flex-fill" style="font-size:.8rem">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <a href="<?= base_url("rooms/edit/{$room['id']}") ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url("bookings/create?room_id={$room['id']}") ?>" class="btn btn-sm btn-success" title="Booking" style="font-size:.8rem">
                        <i class="bi bi-calendar-plus"></i>
                    </a>
                    <?php if (session()->get('role') === 'admin'): ?>
                    <form method="post" action="<?= base_url("rooms/delete/{$room['id']}") ?>" class="d-inline"
                          onsubmit="return confirm('Hapus ruangan ini?')">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-danger" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($rooms)): ?>
    <div class="col-12">
        <div class="section-card p-5 text-center text-muted">
            <i class="bi bi-door-closed fs-1 d-block mb-3" style="color:#8b1a24; opacity:.3"></i>
            <h6>Belum ada ruangan terdaftar</h6>
            <p class="small">Tambahkan ruangan untuk mulai mengelola pemesanan.</p>
            <?php if (session()->get('role') === 'admin'): ?>
            <a href="<?= base_url('rooms/create') ?>" class="btn btn-primary">Tambah Ruangan Pertama</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
