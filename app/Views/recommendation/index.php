<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="form-section mb-4">
    <div class="form-section-title"><i class="bi bi-stars"></i>Cari Ruangan yang Paling Sesuai</div>
    <p class="text-muted mb-3" style="font-size:.85rem">
        Sistem akan merekomendasikan &amp; merangking ruangan menggunakan metode
        <strong>SAW (Simple Additive Weighting)</strong> berdasarkan kesesuaian kapasitas,
        kelengkapan fasilitas, dan tingkat keterpakaian ruangan.
    </p>
    <form method="get" action="<?= base_url('rekomendasi') ?>">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Jumlah Peserta <span class="text-danger">*</span></label>
                <input type="number" name="jumlah_peserta" class="form-control" min="1"
                       value="<?= esc($jumlahPeserta) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                <input type="date" name="tanggal" class="form-control" min="<?= date('Y-m-d') ?>"
                       value="<?= esc($tanggal) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                <input type="time" name="jam_mulai" class="form-control" value="<?= esc($jamMulai) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                <input type="time" name="jam_selesai" class="form-control" value="<?= esc($jamSelesai) ?>" required>
            </div>

            <?php if (!empty($availableFacilities)): ?>
            <div class="col-12">
                <label class="form-label d-block mb-2">Fasilitas yang Dibutuhkan <span class="text-muted fw-normal">(opsional)</span></label>
                <div class="konsumsi-options">
                    <?php foreach ($availableFacilities as $f): ?>
                    <label class="konsumsi-option <?= in_array($f, $fasilitasDiminta, true) ? 'active' : '' ?>">
                        <input type="checkbox" name="fasilitas[]" value="<?= esc($f) ?>"
                               <?= in_array($f, $fasilitasDiminta, true) ? 'checked' : '' ?>>
                        <span><?= esc($f) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i>Cari &amp; Rangking Ruangan
                </button>
            </div>
        </div>
    </form>
</div>

<?php if ($searched): ?>

    <?php if ($errorMsg): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i><?= esc($errorMsg) ?>
    </div>

    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="fw-bold">Hasil Rekomendasi — <?= count($results) ?> ruangan memenuhi kriteria</div>
        <div class="text-muted" style="font-size:.78rem">
            Bobot kriteria: Kapasitas <?= $weights['kapasitas'] * 100 ?>% &middot;
            Fasilitas <?= $weights['fasilitas'] * 100 ?>% &middot;
            Keterpakaian <?= $weights['keterpakaian'] * 100 ?>%
        </div>
    </div>

    <?php foreach ($results as $i => $row):
        $room = $row['room'];
        $bookingUrl = base_url('bookings/create?' . http_build_query([
            'room_id'        => $room['id'],
            'jumlah_peserta' => $jumlahPeserta,
            'tanggal'        => $tanggal,
            'jam_mulai'      => $jamMulai,
            'jam_selesai'    => $jamSelesai,
        ]));
    ?>
    <div class="section-card mb-3 <?= $i === 0 ? 'reco-top' : '' ?>">
        <div class="d-flex flex-wrap gap-3 align-items-center p-3">
            <div class="reco-rank <?= $i === 0 ? 'is-top' : '' ?>">#<?= $i + 1 ?></div>

            <div class="flex-grow-1" style="min-width:220px">
                <div class="d-flex align-items-center gap-2">
                    <div class="fw-bold" style="font-size:1rem"><?= esc($room['nama_ruangan']) ?></div>
                    <?php if ($i === 0): ?>
                    <span class="badge px-2" style="background:#fef3c7;color:#d97706;font-size:.68rem">
                        <i class="bi bi-award-fill me-1"></i>Rekomendasi Terbaik
                    </span>
                    <?php endif; ?>
                </div>
                <div class="text-muted" style="font-size:.8rem">
                    <?= esc($room['kode_ruangan']) ?> &middot; <?= esc($room['lokasi']) ?>
                </div>
            </div>

            <div class="reco-metric">
                <div class="reco-metric-label">Kapasitas</div>
                <div class="reco-metric-value"><?= $room['kapasitas'] ?> orang</div>
                <div class="text-muted" style="font-size:.7rem">selisih <?= $row['c1_selisih_kapasitas'] ?></div>
            </div>
            <div class="reco-metric">
                <div class="reco-metric-label">Fasilitas Cocok</div>
                <div class="reco-metric-value">
                    <?= empty($fasilitasDiminta) ? '–' : $row['c2_fasilitas_cocok'] . '/' . count($fasilitasDiminta) ?>
                </div>
            </div>
            <div class="reco-metric">
                <div class="reco-metric-label">Dipakai (30 hari)</div>
                <div class="reco-metric-value"><?= $row['c3_keterpakaian'] ?>x</div>
            </div>
            <div class="reco-metric">
                <div class="reco-metric-label">Skor SAW</div>
                <div class="reco-metric-value reco-score"><?= number_format($row['v'], 3) ?></div>
            </div>

            <a href="<?= $bookingUrl ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-calendar-plus me-1"></i>Booking Ruangan Ini
            </a>
        </div>

        <details class="reco-detail">
            <summary>Lihat rincian perhitungan SAW</summary>
            <div class="table-responsive mt-2">
                <table class="table table-sm mb-0" style="font-size:.78rem">
                    <thead>
                        <tr>
                            <th>Kriteria</th>
                            <th>Nilai Mentah</th>
                            <th>Normalisasi (r)</th>
                            <th>Bobot (w)</th>
                            <th>w × r</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>C1 — Selisih Kapasitas (cost)</td>
                            <td><?= $row['c1_selisih_kapasitas'] ?></td>
                            <td><?= number_format($row['r1'], 3) ?></td>
                            <td><?= $weights['kapasitas'] ?></td>
                            <td><?= number_format($weights['kapasitas'] * $row['r1'], 3) ?></td>
                        </tr>
                        <tr>
                            <td>C2 — Kecocokan Fasilitas (benefit)</td>
                            <td><?= $row['c2_fasilitas_cocok'] ?></td>
                            <td><?= number_format($row['r2'], 3) ?></td>
                            <td><?= $weights['fasilitas'] ?></td>
                            <td><?= number_format($weights['fasilitas'] * $row['r2'], 3) ?></td>
                        </tr>
                        <tr>
                            <td>C3 — Tingkat Keterpakaian (cost)</td>
                            <td><?= $row['c3_keterpakaian'] ?></td>
                            <td><?= number_format($row['r3'], 3) ?></td>
                            <td><?= $weights['keterpakaian'] ?></td>
                            <td><?= number_format($weights['keterpakaian'] * $row['r3'], 3) ?></td>
                        </tr>
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Nilai Preferensi (V)</td>
                            <td><?= number_format($row['v'], 3) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </details>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

<?php endif; ?>

<style>
.reco-rank {
    width: 46px; height: 46px; border-radius: 50%; background: #f3f6f4; color: #6b7280;
    display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .95rem; flex-shrink: 0;
}
.reco-rank.is-top { background: #8b1a24; color: #fff; }
.reco-top { border: 1.5px solid #f0d9ae; }
.reco-metric { min-width: 90px; text-align: center; }
.reco-metric-label { font-size: .68rem; color: #9ca3af; text-transform: uppercase; font-weight: 700; }
.reco-metric-value { font-size: .95rem; font-weight: 700; color: #1a2e1e; }
.reco-metric-value.reco-score { color: #8b1a24; }
.reco-detail { border-top: 1px solid #f0ede9; padding: .6rem 1rem; }
.reco-detail summary { cursor: pointer; font-size: .78rem; color: #6b7280; font-weight: 600; }
.reco-detail summary:hover { color: #8b1a24; }
</style>

<?= $this->endSection() ?>
