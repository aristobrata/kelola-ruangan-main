<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="fw-bold" style="font-size:1rem"><?= esc($title) ?></div>
            <div class="text-muted small">Kelola akun admin & user (pembooking)</div>
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

    <form method="post" action="<?= esc($action) ?>">
        <?= csrf_field() ?>
        <div class="form-section">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control"
                           value="<?= esc(old('nama', $user['nama'] ?? '')) ?>"
                           placeholder="Nama lengkap" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control"
                           value="<?= esc(old('username', $user['username'] ?? '')) ?>"
                           placeholder="Username untuk login" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <?= $user ? '' : '<span class="text-danger">*</span>' ?></label>
                    <input type="password" name="password" class="form-control"
                           placeholder="<?= $user ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>"
                           <?= $user ? '' : 'required' ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <?php $roleVal = old('role', $user['role'] ?? 'user'); ?>
                    <?php if (is_super_admin()): ?>
                    <select name="role" class="form-select" required>
                        <option value="super_admin" <?= $roleVal === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                        <option value="admin" <?= $roleVal === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="user" <?= $roleVal === 'user' ? 'selected' : '' ?>>User (Pembooking)</option>
                    </select>
                    <div class="form-text">Super Admin &amp; Admin mengelola ruangan &amp; menyetujui booking. Hanya Super Admin yang bisa membuat/mengubah akun menjadi Admin. User membuat booking untuk dirinya sendiri.</div>
                    <?php else: ?>
                    <input type="hidden" name="role" value="user">
                    <input type="text" class="form-control" value="User (Pembooking)" disabled>
                    <div class="form-text">Admin hanya dapat membuat/mengelola akun dengan role User. Hubungi Super Admin untuk membuat akun Admin.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-1"></i><?= $user ? 'Perbarui User' : 'Simpan User' ?>
            </button>
            <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>

</div>
</div>

<?= $this->endSection() ?>
