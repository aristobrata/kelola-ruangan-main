<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Welcome Banner -->
<div class="mb-4 p-4 rounded-4" style="background:linear-gradient(135deg,#6b0f16,#b3232f); color:#fff; position:relative; overflow:hidden">
    <div style="position:absolute;top:-30px;right:-30px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.06)"></div>
    <div style="position:absolute;bottom:-20px;right:60px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.04)"></div>
    <div class="d-flex align-items-center gap-3">
        <div style="width:52px;height:52px;background:#fff;border-radius:14px;display:flex;align-items:center;justify-content:center;border:1.5px solid rgba(255,255,255,.25);overflow:hidden;padding:5px">
            <img src="<?= base_url('assets/img/logo-semen-padang.png') ?>" alt="Logo PT Semen Padang" style="width:100%;height:100%;object-fit:contain">
        </div>
        <div>
            <div style="font-size:1.1rem;font-weight:800">Selamat Datang, <?= esc(session()->get('nama') ?? 'Pengguna') ?>!</div>
            <div style="font-size:.82rem;opacity:.8"><i class="bi bi-geo-alt me-1"></i>Unit Learning & People Development — PT Semen Padang</div>
        </div>
        <div class="ms-auto text-end d-none d-md-block">
            <div style="font-size:.75rem;opacity:.7">Hari ini</div>
            <div style="font-size:.95rem;font-weight:700"><?= date('l, d F Y') ?></div>
        </div>
    </div>
</div>

<!-- Stats Row 1 -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat-card green">
            <div class="stat-icon green"><i class="bi bi-door-open-fill"></i></div>
            <div class="stat-value"><?= $totalRooms ?></div>
            <div class="stat-label">Total Ruangan</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card green">
            <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-value"><?= $availableRooms ?></div>
            <div class="stat-label">Ruangan Tersedia</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card orange">
            <div class="stat-icon orange"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value"><?= $occupiedRooms ?></div>
            <div class="stat-label">Sedang Terpakai</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card red">
            <div class="stat-icon red"><i class="bi bi-tools"></i></div>
            <div class="stat-value"><?= $maintenanceRooms ?></div>
            <div class="stat-label">Maintenance</div>
        </div>
    </div>
</div>

<!-- Stats Row 2 -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card blue">
            <div class="stat-icon pink"><i class="bi bi-calendar-day-fill"></i></div>
            <div class="stat-value"><?= $bookingStats['today'] ?></div>
            <div class="stat-label"><?= $isAdmin ? 'Booking Hari Ini' : 'Booking Saya Hari Ini' ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card orange">
            <div class="stat-icon orange"><i class="bi bi-clock-fill"></i></div>
            <div class="stat-value"><?= $bookingStats['pending'] ?></div>
            <div class="stat-label"><?= $isAdmin ? 'Menunggu Konfirmasi' : 'Booking Saya Menunggu' ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card green">
            <div class="stat-icon green"><i class="bi bi-check2-all"></i></div>
            <div class="stat-value"><?= $bookingStats['approved'] ?></div>
            <div class="stat-label"><?= $isAdmin ? 'Booking Disetujui' : 'Booking Saya Disetujui' ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class="bi bi-calendar3"></i></div>
            <div class="stat-value"><?= $bookingStats['total'] ?></div>
            <div class="stat-label"><?= $isAdmin ? 'Total Semua Booking' : 'Total Booking Saya' ?></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Status Ruangan -->
    <div class="col-lg-7">
        <div class="section-card">
            <div class="section-card-header">
                <h6><i class="bi bi-door-open me-2 text-success"></i>Status Ruangan Saat Ini</h6>
                <a href="<?= base_url('rooms') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-right me-1"></i>Kelola
                </a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Ruangan</th>
                            <th>Lokasi</th>
                            <th class="text-center">Kapasitas</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roomsWithStatus as $room): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold" style="font-size:.875rem"><?= esc($room['nama_ruangan']) ?></div>
                                <small class="text-muted"><?= esc($room['kode_ruangan']) ?></small>
                            </td>
                            <td><small class="text-muted"><?= esc($room['lokasi']) ?></small></td>
                            <td class="text-center"><small><?= $room['kapasitas'] ?> org</small></td>
                            <td class="text-center">
                                <?php
                                $bs = $room['booking_status'] ?? $room['status'];
                                $statusCfg = [
                                    'available'   => ['Tersedia',   'badge-available'],
                                    'occupied'    => ['Terpakai',   'badge-occupied'],
                                    'maintenance' => ['Maintenance','badge-maintenance'],
                                ];
                                [$slabel, $scls] = $statusCfg[$bs] ?? [$bs, 'bg-secondary text-white'];
                                ?>
                                <span class="badge rounded-pill px-3 <?= $scls ?>"><?= $slabel ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($roomsWithStatus)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data ruangan</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Jadwal Hari Ini -->
    <div class="col-lg-5">
        <div class="section-card" style="height:100%">
            <div class="section-card-header">
                <h6><i class="bi bi-calendar-event me-2 text-success"></i><?= $isAdmin ? 'Jadwal Hari Ini' : 'Jadwal Booking Saya Hari Ini' ?></h6>
                <a href="<?= base_url('bookings') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-right me-1"></i>Semua
                </a>
            </div>
            <div class="p-3">
                <?php if (empty($todayBookings)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2 text-success opacity-50"></i>
                    <div class="small">Tidak ada jadwal hari ini</div>
                </div>
                <?php else: ?>
                <div style="max-height:280px; overflow-y:auto">
                    <?php foreach ($todayBookings as $b): ?>
                    <a href="<?= base_url("bookings/{$b['id']}") ?>" class="text-decoration-none">
                        <div class="border rounded-3 p-3 mb-2" style="border-color:#e8ede9 !important; transition:.15s" onmouseover="this.style.background='#f3f6f4'" onmouseout="this.style.background='#fff'">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="fw-semibold" style="font-size:.825rem; color:#1a2e1e"><?= esc($b['nama_ruangan']) ?></div>
                                <?php
                                $sm = ['pending'=>['Pending','badge-pending'],'approved'=>['Disetujui','badge-approved']];
                                [$bl,$bc] = $sm[$b['status']] ?? [$b['status'],'badge-cancelled'];
                                ?>
                                <span class="badge px-2 <?= $bc ?>" style="font-size:.7rem"><?= $bl ?></span>
                            </div>
                            <div style="font-size:.78rem; color:#6b7280"><?= esc($b['nama_peminjam']) ?></div>
                            <div style="font-size:.78rem; color:#6b7280" class="mt-1">
                                <i class="bi bi-clock me-1"></i>
                                <?= substr($b['jam_mulai'],0,5) ?> – <?= substr($b['jam_selesai'],0,5) ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <a href="<?= base_url('bookings/create') ?>" class="btn btn-primary btn-sm w-100 mt-2">
                    <i class="bi bi-plus-circle me-1"></i>Buat Booking Baru
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
