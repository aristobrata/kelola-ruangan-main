<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Kelola Ruangan LPD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f0f4f0;
        }

        /* Left panel */
        .login-left {
            flex: 1;
            background: linear-gradient(145deg, #6b0f16 0%, #8b1a24 40%, #b3232f 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .brand-logo {
            width: 90px; height: 90px;
            background: #fff;
            border-radius: 24px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            border: 2px solid rgba(255,255,255,.2);
            overflow: hidden;
            padding: 8px;
        }
        .brand-logo i { font-size: 2.8rem; color: #fff; }
        .brand-logo img { width: 100%; height: 100%; object-fit: contain; }
        .brand-name { color: #fff; font-size: 1.5rem; font-weight: 800; text-align: center; line-height: 1.2; }
        .brand-sub  { color: rgba(255,255,255,.7); font-size: .9rem; text-align: center; margin-top: .4rem; }
        .brand-unit {
            margin-top: 1.5rem;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            padding: .75rem 1.25rem;
            text-align: center;
        }
        .brand-unit small { color: rgba(255,255,255,.7); font-size: .75rem; display: block; margin-bottom: .2rem; }
        .brand-unit span  { color: #fff; font-size: .9rem; font-weight: 600; }

        .feature-list { margin-top: 2.5rem; width: 100%; max-width: 300px; }
        .feature-item {
            display: flex; align-items: center; gap: .75rem;
            color: rgba(255,255,255,.85);
            font-size: .85rem;
            padding: .5rem 0;
        }
        .feature-item i { color: #e8b64c; font-size: 1rem; }

        /* Right panel */
        .login-right {
            width: 460px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
        }
        .login-title  { font-size: 1.6rem; font-weight: 800; color: #1a1a2e; }
        .login-subtitle { color: #6b7280; font-size: .9rem; margin-top: .3rem; }

        .form-label { font-weight: 600; font-size: .85rem; color: #374151; margin-bottom: .4rem; }
        .form-control {
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: .7rem 1rem;
            font-size: .9rem;
            transition: all .2s;
        }
        .form-control:focus { border-color: #8b1a24; box-shadow: 0 0 0 3px rgba(139,26,36,.12); }
        .input-group .form-control { border-right: none; }
        .input-group .btn-outline-secondary {
            border: 1.5px solid #e5e7eb;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #9ca3af;
            background: #fff;
        }

        .btn-login {
            background: linear-gradient(135deg, #6b0f16, #b3232f);
            border: none;
            border-radius: 10px;
            padding: .8rem;
            font-weight: 700;
            font-size: .95rem;
            color: #fff;
            width: 100%;
            transition: all .2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(139,26,36,.35); color: #fff; }

        .divider { border-color: #f3f4f6; margin: 1.5rem 0; }
        .login-footer { color: #9ca3af; font-size: .8rem; text-align: center; margin-top: 2rem; }

        @media (max-width: 768px) {
            .login-left { display: none; }
            .login-right { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<!-- Left Panel -->
<div class="login-left">
    <div class="brand-logo"><img src="<?= base_url('assets/img/logo-semen-padang.png') ?>" alt="Logo PT Semen Padang"></div>
    <div class="brand-name">Sistem Kelola Ruangan</div>
    <div class="brand-sub">PT Semen Padang</div>
    <div class="brand-unit">
        <small>Unit</small>
        <span>Learning & People Development</span>
    </div>
    <div class="feature-list">
        <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Manajemen ruangan terpusat</div>
        <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Booking & penjadwalan mudah</div>
        <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Cek ketersediaan real-time</div>
        <div class="feature-item"><i class="bi bi-check-circle-fill"></i> Riwayat penggunaan ruangan</div>
    </div>
</div>

<!-- Right Panel -->
<div class="login-right">
    <div class="mb-4">
        <div class="text-success fw-bold small mb-2">
            <i class="bi bi-shield-check me-1"></i>PORTAL MASUK
        </div>
        <div class="login-title">Selamat Datang</div>
        <div class="login-subtitle">Masuk untuk mengakses sistem kelola ruangan</div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2" style="border-radius:10px; font-size:.875rem">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2" style="border-radius:10px; font-size:.875rem">
        <i class="bi bi-check-circle-fill"></i>
        <?= session()->getFlashdata('success') ?>
    </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text" style="border:1.5px solid #e5e7eb; border-right:none; border-radius:10px 0 0 10px; background:#f9fafb; color:#9ca3af">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" name="username" class="form-control"
                       placeholder="Masukkan username"
                       value="<?= esc(old('username')) ?>" required
                       style="border-left:none; border-radius:0 10px 10px 0">
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text" style="border:1.5px solid #e5e7eb; border-right:none; border-radius:10px 0 0 10px; background:#f9fafb; color:#9ca3af">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" id="passwordInput" class="form-control"
                       placeholder="Masukkan password" required
                       style="border-left:none; border-right:none; border-radius:0">
                <button type="button" class="btn-outline-secondary btn input-group-text"
                        onclick="togglePassword()" style="border-radius:0 10px 10px 0">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn-login btn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Sistem
        </button>
    </form>

    <div class="login-footer">
        <div class="mb-1">
            <i class="bi bi-info-circle me-1"></i>
            Default: <strong>admin / admin123</strong> &nbsp;|&nbsp; <strong>staff / staff123</strong>
        </div>
        <div>&copy; <?= date('Y') ?> PT Semen Padang — Unit Learning & People Development</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>
</body>
</html>
