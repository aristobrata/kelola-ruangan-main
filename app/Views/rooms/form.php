<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
<div class="col-lg-8">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= base_url('rooms') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="fw-bold" style="font-size:1rem"><?= esc($title) ?></div>
            <div class="text-muted small">Isi data ruangan dengan lengkap dan benar</div>
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

    <?php if (isset($error)): ?>
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
        <div><?= esc($error) ?></div>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= esc($action) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-section mb-3">
            <div class="form-section-title"><i class="bi bi-door-open-fill"></i>Identitas Ruangan</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Kode Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="kode_ruangan" class="form-control"
                           value="<?= esc(old('kode_ruangan', $room['kode_ruangan'] ?? '')) ?>"
                           placeholder="Cth: R-001" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_ruangan" class="form-control"
                           value="<?= esc(old('nama_ruangan', $room['nama_ruangan'] ?? '')) ?>"
                           placeholder="Cth: Ruang Rapat Utama" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kapasitas (orang) <span class="text-danger">*</span></label>
                    <input type="number" name="kapasitas" class="form-control" min="1"
                           value="<?= esc(old('kapasitas', $room['kapasitas'] ?? '')) ?>"
                           placeholder="Cth: 30" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <?php $sel = old('status', $room['status'] ?? 'available'); ?>
                        <option value="available"   <?= $sel === 'available'   ? 'selected' : '' ?>>Tersedia</option>
                        <option value="maintenance" <?= $sel === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                    <input type="text" name="lokasi" class="form-control"
                           value="<?= esc(old('lokasi', $room['lokasi'] ?? '')) ?>"
                           placeholder="Cth: Gedung A, Lantai 2" required>
                </div>
            </div>
        </div>

        <div class="form-section mb-4">
            <div class="form-section-title"><i class="bi bi-gear-fill"></i>Fasilitas & Deskripsi</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Fasilitas</label>
                    <input type="text" name="fasilitas" class="form-control"
                           value="<?= esc(old('fasilitas', $room['fasilitas'] ?? '')) ?>"
                           placeholder="Cth: Proyektor, AC, Whiteboard, WiFi">
                    <div class="form-text">Pisahkan tiap fasilitas dengan koma ( , )</div>
                </div>
                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"
                              placeholder="Deskripsi singkat tentang ruangan..."><?= esc(old('deskripsi', $room['deskripsi'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-section mb-4">
            <div class="form-section-title"><i class="bi bi-image-fill"></i>Foto Ruangan</div>
            <div class="row g-3 align-items-start">
                <div class="col-md-4">
                    <div class="photo-preview-box" id="photoPreviewBox">
                        <?php if (!empty($room['foto'])): ?>
                        <img src="<?= base_url('uploads/rooms/' . esc($room['foto'])) ?>" alt="Foto ruangan" id="photoPreviewImg">
                        <?php else: ?>
                        <img src="" alt="Foto ruangan" id="photoPreviewImg" style="display:none">
                        <div class="photo-preview-placeholder" id="photoPreviewPlaceholder">
                            <i class="bi bi-image"></i>
                            <span>Belum ada foto</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Unggah Foto <?= $room ? '(opsional, ganti jika perlu)' : '(opsional)' ?></label>
                    <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/png, image/jpeg, image/webp"
                           onchange="previewFoto(event)">
                    <div class="form-text">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</div>

                    <?php if (!empty($room['foto'])): ?>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" value="1" name="hapus_foto" id="hapusFotoCheck">
                        <label class="form-check-label small text-danger" for="hapusFotoCheck">
                            Hapus foto ruangan ini
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i><?= $room ? 'Perbarui' : 'Simpan Ruangan' ?>
            </button>
            <a href="<?= base_url('rooms') ?>" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>

</div>
</div>

<style>
    .photo-preview-box {
        width: 100%;
        aspect-ratio: 4/3;
        border-radius: 14px;
        overflow: hidden;
        background: #f3f6f4;
        border: 1.5px dashed #d7ddd9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .photo-preview-box img { width: 100%; height: 100%; object-fit: cover; }
    .photo-preview-placeholder { text-align: center; color: #9aa39c; }
    .photo-preview-placeholder i { font-size: 1.8rem; display: block; margin-bottom: .25rem; }
    .photo-preview-placeholder span { font-size: .75rem; }
</style>
<script>
    function previewFoto(event) {
        const file = event.target.files[0];
        const img = document.getElementById('photoPreviewImg');
        const placeholder = document.getElementById('photoPreviewPlaceholder');
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            img.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);

        const hapusCheck = document.getElementById('hapusFotoCheck');
        if (hapusCheck) hapusCheck.checked = false;
    }
</script>
<?= $this->endSection() ?>
