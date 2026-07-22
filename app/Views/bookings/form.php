<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
<div class="col-lg-9">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= base_url('bookings') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="fw-bold" style="font-size:1rem"><?= esc($title) ?></div>
            <div class="text-muted small">Isi formulir pemesanan ruangan</div>
        </div>
    </div>

    <?php if (isset($validation)): ?>
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
        <div>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-1 ps-3">
                <?php foreach ($validation->getErrors() as $error): ?>
                <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($conflict_error)): ?>
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-calendar-x-fill mt-1 flex-shrink-0"></i>
        <div><?= esc($conflict_error) ?></div>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= esc($action) ?>">
        <?= csrf_field() ?>
        <div class="row g-3">

            <!-- Peminjam -->
            <div class="col-12">
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-person-fill"></i>Informasi Peminjam</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Peminjam <span class="text-danger">*</span></label>
                            <input type="text" name="nama_peminjam" class="form-control"
                                   value="<?= esc(old('nama_peminjam', $booking['nama_peminjam'] ?? session()->get('nama'))) ?>"
                                   placeholder="Nama lengkap peminjam" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Instansi / Unit Kerja</label>
                            <input type="text" name="instansi" class="form-control"
                                   value="<?= esc(old('instansi', $booking['instansi'] ?? '')) ?>"
                                   placeholder="Cth: Unit LPD, Divisi SDM">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keperluan / Tujuan <span class="text-danger">*</span></label>
                            <textarea name="keperluan" class="form-control" rows="2" required
                                      placeholder="Jelaskan keperluan penggunaan ruangan..."><?= esc(old('keperluan', $booking['keperluan'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pilih Ruangan -->
            <div class="col-12">
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-door-open-fill"></i>Pilih Ruangan</div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                            <select name="room_id" id="roomSelect" class="form-select" required>
                                <option value="">-- Pilih Ruangan --</option>
                                <?php foreach ($rooms as $r): ?>
                                <option value="<?= $r['id'] ?>"
                                        data-kapasitas="<?= $r['kapasitas'] ?>"
                                        data-lokasi="<?= esc($r['lokasi']) ?>"
                                        data-fasilitas="<?= esc($r['fasilitas'] ?? '-') ?>"
                                        <?= (old('room_id', $selectedRoom ?? '') == $r['id']) ? 'selected' : '' ?>>
                                    <?= esc($r['kode_ruangan']) ?> — <?= esc($r['nama_ruangan']) ?> (<?= $r['kapasitas'] ?> org)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jumlah Peserta <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_peserta" class="form-control" min="1"
                                   value="<?= esc(old('jumlah_peserta', $booking['jumlah_peserta'] ?? 1)) ?>"
                                   required>
                        </div>
                        <div class="col-12" id="roomInfo" style="display:none">
                            <div class="rounded-3 p-3" style="background:#fbe9ea; border:1px solid #bbf7d0">
                                <div class="row g-2" style="font-size:.82rem">
                                    <div class="col-sm-4">
                                        <span class="text-muted">Lokasi:</span>
                                        <strong class="d-block" id="infoLokasi"></strong>
                                    </div>
                                    <div class="col-sm-3">
                                        <span class="text-muted">Kapasitas:</span>
                                        <strong class="d-block" style="color:#8b1a24" id="infoKapasitas"></strong>
                                    </div>
                                    <div class="col-sm-5">
                                        <span class="text-muted">Fasilitas:</span>
                                        <strong class="d-block" id="infoFasilitas"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jadwal -->
            <div class="col-12">
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-calendar3"></i>Jadwal Penggunaan</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control"
                                   value="<?= esc(old('tanggal_mulai', $booking['tanggal_mulai'] ?? '')) ?>"
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control"
                                   value="<?= esc(old('tanggal_selesai', $booking['tanggal_selesai'] ?? '')) ?>"
                                   min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" class="form-control"
                                   value="<?= esc(old('jam_mulai', isset($booking['jam_mulai']) ? substr($booking['jam_mulai'],0,5) : '')) ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" class="form-control"
                                   value="<?= esc(old('jam_selesai', isset($booking['jam_selesai']) ? substr($booking['jam_selesai'],0,5) : '')) ?>"
                                   required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Konsumsi & Catatan -->
            <div class="col-12">
                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-cup-hot-fill"></i>Konsumsi</div>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label d-block mb-2">Kebutuhan Konsumsi</label>
                            <div class="konsumsi-options">
                                <?php
                                    $oldKonsumsi = old('konsumsi');
                                    if ($oldKonsumsi !== null) {
                                        $selectedKonsumsi = is_array($oldKonsumsi) ? $oldKonsumsi : array_filter(array_map('trim', explode(',', $oldKonsumsi)));
                                    } else {
                                        $stored = $booking['konsumsi'] ?? '';
                                        $selectedKonsumsi = $stored ? array_filter(array_map('trim', explode(',', $stored))) : [];
                                    }
                                ?>
                                <?php foreach ($konsumsiOptions as $val => $label): ?>
                                <label class="konsumsi-option <?= in_array($val, $selectedKonsumsi) ? 'active' : '' ?>">
                                    <input type="checkbox" name="konsumsi[]" value="<?= esc($val) ?>"
                                           <?= in_array($val, $selectedKonsumsi) ? 'checked' : '' ?>>
                                    <span><?= esc($label) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">Bisa pilih lebih dari satu. Kosongkan jika tidak membutuhkan konsumsi.</div>
                        </div>
                    </div>
                    <div class="form-section-title"><i class="bi bi-chat-text-fill"></i>Catatan</div>
                    <textarea name="catatan" class="form-control" rows="2"
                              placeholder="Catatan atau permintaan khusus (opsional)..."><?= esc(old('catatan', $booking['catatan'] ?? '')) ?></textarea>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-send me-1"></i><?= $booking ? 'Perbarui Booking' : 'Kirim Booking' ?>
                </button>
                <a href="<?= base_url('bookings') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>

</div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    const roomSelect = document.getElementById('roomSelect');
    const roomInfo   = document.getElementById('roomInfo');
    function updateRoomInfo() {
        const opt = roomSelect.options[roomSelect.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('infoLokasi').textContent    = opt.dataset.lokasi    || '-';
            document.getElementById('infoKapasitas').textContent = opt.dataset.kapasitas + ' orang';
            document.getElementById('infoFasilitas').textContent = opt.dataset.fasilitas || '-';
            roomInfo.style.display = '';
        } else {
            roomInfo.style.display = 'none';
        }
    }
    roomSelect.addEventListener('change', updateRoomInfo);
    updateRoomInfo();

    document.querySelectorAll('.konsumsi-option input[type="checkbox"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            checkbox.closest('.konsumsi-option').classList.toggle('active', checkbox.checked);
        });
    });
</script>
<?= $this->endSection() ?>
