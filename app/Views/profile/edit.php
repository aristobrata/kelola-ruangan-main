<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
<div class="col-lg-6">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div class="user-avatar" style="width:44px;height:44px;font-size:1.1rem;border-radius:50%;background:#8b1a24;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">
            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
        </div>
        <div>
            <div class="fw-bold" style="font-size:1rem"><?= esc($title) ?></div>
            <div class="text-muted small"><?= esc($user['nama']) ?> · <?= strtoupper(role_label($user['role'])) ?></div>
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
    <?php elseif (isset($error)): ?>
    <div class="alert alert-danger d-flex gap-2 mb-4">
        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
        <div><?= esc($error) ?></div>
    </div>
    <?php endif; ?>

    <div class="form-section mb-3">
        <div class="form-section-title"><i class="bi bi-person-fill"></i>Informasi Akun</div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="text-muted small mb-1">Nama</div>
                <div class="fw-semibold"><?= esc($user['nama']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small mb-1">Username</div>
                <div class="fw-semibold"><?= esc($user['username']) ?></div>
            </div>
        </div>
    </div>

    <form method="post" action="<?= base_url('profile/update-password') ?>">
        <?= csrf_field() ?>
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-key-fill"></i>Ubah Password</div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                    <input type="password" name="password_lama" class="form-control" placeholder="Masukkan password Anda saat ini" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                    <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru" required>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i>Simpan Password Baru
            </button>
        </div>
    </form>

</div>
</div>

<?= $this->endSection() ?>
