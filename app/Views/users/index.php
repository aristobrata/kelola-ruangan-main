<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="small text-muted"><?= count($users) ?> akun terdaftar</div>
    <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus-fill me-1"></i>Tambah User
    </a>
</div>

<div class="section-card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Username</th>
                    <th class="text-center">Role</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td class="text-muted" style="font-size:.8rem"><?= $i + 1 ?></td>
                    <td class="fw-semibold" style="font-size:.85rem"><?= esc($u['nama']) ?></td>
                    <td style="font-size:.85rem"><?= esc($u['username']) ?></td>
                    <td class="text-center">
                        <?php if ($u['role'] === 'admin'): ?>
                        <span class="badge px-2 py-1" style="background:#fbe9ea;color:#8b1a24;font-size:.75rem">Admin</span>
                        <?php else: ?>
                        <span class="badge px-2 py-1" style="background:#f3f6f4;color:#374151;font-size:.75rem">User</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="<?= base_url("users/edit/{$u['id']}") ?>"
                               class="btn btn-sm btn-outline-secondary" style="padding:.2rem .5rem" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ((int)$u['id'] !== (int)session()->get('user_id')): ?>
                            <form method="post" action="<?= base_url("users/delete/{$u['id']}") ?>" class="d-inline"
                                  onsubmit="return confirm('Hapus user ini?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" style="padding:.2rem .5rem" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-people d-block fs-1 mb-2 opacity-25"></i>
                        Belum ada data user
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
