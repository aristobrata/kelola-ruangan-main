<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Kelola Ruangan') ?> — LPD PT Semen Padang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-dark:  #6b0f16;
            --green-mid:   #8b1a24;
            --green-light: #b3232f;
            --green-pale:  #fbe9ea;
            --gold:        #c9a227;
            --gold-light:  #e8b64c;
            --sidebar-w:   270px;
            --topbar-h:    64px;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f3f6f4;
            color: #1a2e1e;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: linear-gradient(180deg, var(--green-dark) 0%, #33080c 100%);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: transform .28s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
        }

        .sidebar-header {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-logo {
            width: 44px; height: 44px;
            background: #fff;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            border: 1.5px solid rgba(255,255,255,.2);
            overflow: hidden;
            padding: 4px;
        }
        .sidebar-logo i { font-size: 1.4rem; color: #fff; }
        .sidebar-logo img { width: 100%; height: 100%; object-fit: contain; }
        .sidebar-title  { color: #fff; font-size: .95rem; font-weight: 700; line-height: 1.2; }
        .sidebar-subtitle { color: rgba(255,255,255,.55); font-size: .72rem; }
        .sidebar-band { height: 3px; background: linear-gradient(90deg, var(--gold) 0%, var(--gold-light, var(--gold)) 50%, var(--gold) 100%); opacity: .8; }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: .75rem 0; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }

        .nav-section-label {
            padding: .75rem 1.25rem .3rem;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255,255,255,.35);
        }

        .nav-link-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 1.1rem .6rem 1.25rem;
            color: rgba(255,255,255,.72);
            font-size: .845rem;
            font-weight: 500;
            border-radius: 0;
            text-decoration: none;
            transition: all .18s;
            margin: 1px 0;
            border-right: 3px solid transparent;
        }
        .nav-link-item .nav-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem;
            background: rgba(255,255,255,.06);
            flex-shrink: 0;
            transition: background .18s;
        }
        .nav-link-item:hover {
            color: #fff;
            background: rgba(255,255,255,.06);
        }
        .nav-link-item:hover .nav-icon { background: rgba(255,255,255,.12); }
        .nav-link-item.active {
            color: #fff;
            background: rgba(255,255,255,.1);
            border-right-color: #e8b64c;
        }
        .nav-link-item.active .nav-icon {
            background: rgba(93,222,138,.25);
            color: #e8b64c;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .user-card {
            display: flex; align-items: center; gap: .75rem;
        }
        .user-avatar {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: .9rem;
            flex-shrink: 0;
        }
        .user-name   { color: #fff; font-size: .825rem; font-weight: 600; }
        .user-role   { color: rgba(255,255,255,.5); font-size: .72rem; }
        .logout-btn {
            color: rgba(255,255,255,.5);
            font-size: 1rem;
            text-decoration: none;
            transition: color .18s;
            margin-left: auto;
        }
        .logout-btn:hover { color: #ff7f7f; }

        /* ── TOPBAR ── */
        .main-content { margin-left: var(--sidebar-w); }

        .topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e8ede9;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 900;
        }
        .topbar-title { font-size: 1rem; font-weight: 700; color: #1a2e1e; flex: 1; }

        .breadcrumb-topbar {
            display: flex; align-items: center; gap: .4rem;
            font-size: .78rem; color: #6b7280;
        }
        .breadcrumb-topbar a { color: var(--green-mid); text-decoration: none; }
        .breadcrumb-topbar .sep { color: #d1d5db; }

        .topbar-date {
            background: var(--green-pale);
            color: var(--green-mid);
            font-size: .78rem;
            font-weight: 600;
            padding: .35rem .75rem;
            border-radius: 8px;
        }
        .toggle-btn {
            display: none;
            width: 36px; height: 36px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            align-items: center; justify-content: center;
            cursor: pointer; color: #6b7280;
        }

        /* ── PAGE CONTENT ── */
        .page-content { padding: 1.5rem; }

        /* ── ALERTS ── */
        .alert { border-radius: 12px; font-size: .875rem; border: none; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-danger  { background: #fee2e2; color: #991b1b; }

        /* ── STAT CARDS ── */
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.1rem 1.15rem;
            border: 1px solid #e8ede9;
            height: 100%;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            transition: box-shadow .2s, transform .2s;
        }
        .stat-card:hover { box-shadow: 0 8px 30px rgba(107,15,22,.1); transform: translateY(-2px); }
        .stat-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
        }
        .stat-card.green::before  { background: #16a34a; }
        .stat-card.orange::before { background: #d97706; }
        .stat-card.blue::before   { background: #2563eb; }
        .stat-card.red::before    { background: #dc2626; }
        .stat-card.pink::before   { background: #be185d; }

        .stat-body { min-width: 0; }
        .stat-kicker {
            font-size: .66rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: #8b1a24; margin-bottom: .3rem;
        }
        .stat-title { font-size: .82rem; font-weight: 600; color: #4b5563; margin-bottom: .55rem; }
        .stat-value { font-size: 1.85rem; font-weight: 800; color: #1a2e1e; line-height: 1; }
        .stat-label { font-size: .78rem; color: #6b7280; font-weight: 500; margin-top: .3rem; }

        .stat-progress-track {
            height: 5px; border-radius: 3px; background: #f0f0ee;
            margin-top: .65rem; overflow: hidden;
        }
        .stat-progress-fill { height: 100%; border-radius: 3px; }
        .stat-progress-fill.green  { background: #16a34a; }
        .stat-progress-fill.orange { background: #d97706; }
        .stat-progress-note { font-size: .68rem; color: #9ca3af; margin-top: .3rem; text-align: right; }

        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .stat-icon.green  { background: #dcfce7; color: #16a34a; }
        .stat-icon.orange { background: #fef3c7; color: #d97706; }
        .stat-icon.blue   { background: #dbeafe; color: #2563eb; }
        .stat-icon.red    { background: #fee2e2; color: #dc2626; }
        .stat-icon.purple { background: #ede9fe; color: #7c3aed; }
        .stat-icon.pink   { background: #fce7f3; color: #be185d; }

        /* ── CARDS ── */
        .section-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e8ede9;
            overflow: hidden;
        }
        .section-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f3f6f4;
            display: flex; align-items: center; justify-content: space-between;
        }
        .section-card-header h6 { margin: 0; font-weight: 700; color: #1a2e1e; font-size: .9rem; }

        /* ── ROOM CARDS ── */
        .room-card {
            background: #fff;
            border: 1px solid #e8ede9;
            border-radius: 16px;
            overflow: hidden;
            transition: box-shadow .2s, transform .2s;
        }
        .room-card:hover { box-shadow: 0 8px 30px rgba(107,15,22,.1); transform: translateY(-2px); }
        .room-card-bar { height: 5px; }
        .room-card-bar.available   { background: linear-gradient(90deg, #22c55e, #4ade80); }
        .room-card-bar.occupied    { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .room-card-bar.maintenance { background: linear-gradient(90deg, #ef4444, #f87171); }

        /* ── ROOM PHOTOS ── */
        .room-card-photo {
            width: 100%;
            height: 160px;
            overflow: hidden;
            background: var(--green-pale);
        }
        .room-card-photo img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .3s; }
        .room-card:hover .room-card-photo img { transform: scale(1.04); }
        .room-card-photo-placeholder {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            background: var(--green-pale);
            color: var(--green-mid);
            opacity: .5;
            font-size: 2.2rem;
        }
        .room-detail-photo { width: 100%; height: 200px; overflow: hidden; position: relative; }
        .room-detail-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .room-detail-photo .room-card-photo-placeholder { font-size: 2.8rem; }
        .room-photo-count-badge {
            position: absolute; top: 10px; right: 10px; z-index: 3;
            background: rgba(0,0,0,.55); color: #fff; font-size: .7rem; font-weight: 600;
            padding: 3px 9px; border-radius: 20px;
        }
        .room-photo-indicators { margin-bottom: .4rem; }
        .room-photo-indicators [data-bs-target] {
            width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.6);
            border: none; opacity: 1;
        }
        .room-photo-indicators [data-bs-target].active { background: #fff; }

        /* ── BADGES ── */
        .badge-available   { background: #dcfce7; color: #16a34a; font-weight: 600; }
        .badge-occupied    { background: #fef3c7; color: #d97706; font-weight: 600; }
        .badge-maintenance { background: #fee2e2; color: #dc2626; font-weight: 600; }
        .badge-pending     { background: #fef3c7; color: #d97706; font-weight: 600; }
        .badge-approved    { background: #dcfce7; color: #16a34a; font-weight: 600; }
        .badge-rejected    { background: #fee2e2; color: #dc2626; font-weight: 600; }
        .badge-cancelled   { background: #f3f4f6; color: #6b7280; font-weight: 600; }
        .badge-selesai     { background: #dbeafe; color: #2563eb; font-weight: 600; }

        /* ── TABLE ── */
        .table { font-size: .85rem; }
        .table th { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; background: #fafcfa; border-bottom: 1px solid #e8ede9; padding: .75rem 1rem; }
        .table td { padding: .75rem 1rem; vertical-align: middle; border-color: #f3f6f4; }
        .table tbody tr:hover { background: #fafcfa; }

        /* ── FORMS ── */
        .form-section {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e8ede9;
            padding: 1.5rem;
        }
        .form-section-title {
            font-size: .825rem;
            font-weight: 700;
            color: var(--green-mid);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 1rem;
            display: flex; align-items: center; gap: .5rem;
        }
        .form-label { font-weight: 600; font-size: .825rem; color: #374151; margin-bottom: .4rem; }
        .form-control, .form-select {
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: .875rem;
            padding: .6rem .9rem;
            transition: all .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--green-mid);
            box-shadow: 0 0 0 3px rgba(139,26,36,.1);
        }

        /* ── PANTAU JADWAL (SCHEDULE) ── */
        .sched-tabs {
            display: flex; background: #f3f6f4; border-radius: 8px; padding: 3px;
        }
        .sched-tab {
            padding: .4rem .9rem; font-size: .82rem; font-weight: 600; color: #6b7280;
            border-radius: 6px; text-decoration: none;
        }
        .sched-tab.active { background: #fff; color: #8b1a24; box-shadow: 0 1px 3px rgba(0,0,0,.1); }

        .sched-mini-nav {
            width: 26px; height: 26px; border-radius: 6px; display: flex; align-items: center; justify-content: center;
            color: #6b7280; text-decoration: none; background: #f3f6f4; flex-shrink: 0;
        }
        .sched-mini-nav:hover { background: #e8ede9; color: #8b1a24; }

        .sched-mini-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
        .sched-mini-dl { font-size: .68rem; color: #9ca3af; font-weight: 700; padding: 2px 0; }
        .sched-mini-day {
            font-size: .78rem; padding: 5px 0; border-radius: 6px; color: #374151; text-decoration: none;
        }
        .sched-mini-day:hover { background: #f3f6f4; }
        .sched-mini-day.is-today { background: #fef3c7; color: #d97706; font-weight: 700; }
        .sched-mini-day.is-selected { background: #8b1a24; color: #fff; font-weight: 700; }

        .sched-room-list { max-height: 260px; overflow-y: auto; }
        .sched-room-item { display: flex; align-items: center; gap: .4rem; padding: .3rem 0; font-size: .82rem; cursor: pointer; }
        .sched-room-item input { accent-color: #8b1a24; }

        /* Grid waktu (harian/mingguan) */
        .sched-timegrid { display: flex; overflow-x: auto; }
        .sched-axis { position: relative; width: 52px; flex-shrink: 0; }
        .sched-axis-label { position: absolute; font-size: .68rem; color: #9ca3af; white-space: nowrap; }
        .sched-days { display: grid; flex: 1; min-width: 500px; }
        .sched-day-col { border-left: 1px solid #f0ede9; }
        .sched-day-header {
            text-align: center; font-size: .72rem; font-weight: 700; color: #6b7280;
            text-transform: uppercase; padding: .4rem 0; border-bottom: 1px solid #f0ede9;
        }
        .sched-day-header.is-today { color: #8b1a24; }
        .sched-day-num { font-size: .95rem; font-weight: 700; color: #1a2e1e; }
        .sched-day-header.is-today .sched-day-num { color: #8b1a24; }
        .sched-day-body { position: relative; background: #fff; }
        .sched-day-body.is-today { background: #fefaf5; }
        .sched-gridline { position: absolute; left: 0; right: 0; border-top: 1px solid #f3f1ee; }
        .sched-blocks { position: absolute; inset: 0; }
        .sched-block {
            position: absolute; border-radius: 6px; padding: 4px 7px;
            overflow: hidden; text-decoration: none; display: block; box-shadow: 0 1px 3px rgba(0,0,0,.18);
            border: 1px solid rgba(255,255,255,.25);
        }
        .sched-block:hover { filter: brightness(1.08); box-shadow: 0 2px 6px rgba(0,0,0,.25); z-index: 5; }
        .sched-block .sb-title {
            font-size: .72rem; font-weight: 700; color: #fff; line-height: 1.25;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sched-block .sb-room {
            font-size: .66rem; color: rgba(255,255,255,.88); line-height: 1.2; margin-top: 1px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sched-block .sb-time {
            font-size: .64rem; color: rgba(255,255,255,.8); margin-top: 2px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sched-block-ongoing { background: #dc2626; }
        .sched-block-pending { background: #d97706; }
        .sched-block-booked  { background: #16a34a; }

        /* Grid bulanan */
        .sched-month-head {
            display: grid; grid-template-columns: repeat(7, 1fr); text-align: center;
            font-size: .72rem; font-weight: 700; color: #6b7280; text-transform: uppercase; padding-bottom: .5rem;
        }
        .sched-month-row { display: grid; grid-template-columns: repeat(7, 1fr); }
        .sched-month-cell {
            border: 1px solid #f3f1ee; min-height: 78px; padding: .4rem; text-decoration: none;
            display: block; color: inherit;
        }
        .sched-month-cell:hover { background: #fbf8f7; }
        .sched-month-cell.is-muted { color: #c9c2be; background: #fbfaf9; }
        .sched-month-cell.is-today { background: #fef3c7; }
        .sched-month-daynum { font-size: .82rem; font-weight: 700; margin-bottom: .3rem; }
        .sched-month-badge {
            font-size: .65rem; font-weight: 700; color: #fff; border-radius: 4px; padding: 2px 5px; display: inline-block;
        }
        .sched-month-badge.all-confirmed { background: #16a34a; }
        .sched-month-badge.has-pending   { background: #d97706; }

        .sched-legend-item { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: #6b7280; }
        .sched-dot { width: 11px; height: 11px; border-radius: 3px; display: inline-block; }
        .sched-dot-empty { background: #fff; border: 1.5px solid #d9d4d0; }

        /* ── KONSUMSI RADIO OPTIONS ── */
        .konsumsi-options {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
        }
        .konsumsi-option {
            display: flex;
            align-items: center;
            gap: .55rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: .65rem 1rem;
            cursor: pointer;
            font-size: .875rem;
            font-weight: 500;
            color: #374151;
            background: #fff;
            transition: all .18s;
            user-select: none;
        }
        .konsumsi-option:hover { border-color: var(--green-light); }
        .konsumsi-option input[type="checkbox"] {
            accent-color: var(--green-mid);
            width: 1rem;
            height: 1rem;
            margin: 0;
            flex-shrink: 0;
        }
        .konsumsi-option.active {
            border-color: var(--green-mid);
            background: var(--green-pale);
            color: var(--green-dark);
            font-weight: 700;
            box-shadow: 0 0 0 3px rgba(139,26,36,.1);
        }

        /* ── BUTTONS ── */
        .btn-primary {
            background: linear-gradient(135deg, var(--green-dark), var(--green-light));
            border: none;
            font-weight: 600;
            border-radius: 10px;
            transition: all .2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 5px 15px rgba(139,26,36,.3); background: linear-gradient(135deg, var(--green-dark), var(--green-light)); }
        .btn-success { border-radius: 10px; font-weight: 600; }
        .btn-warning { border-radius: 10px; font-weight: 600; }
        .btn-danger  { border-radius: 10px; font-weight: 600; }
        .btn-outline-primary { border-color: var(--green-mid); color: var(--green-mid); border-radius: 10px; font-weight: 600; }
        .btn-outline-primary:hover { background: var(--green-mid); border-color: var(--green-mid); }
        .btn-outline-secondary { border-radius: 10px; font-weight: 500; }

        /* ── OVERLAY ── */
        .sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 1039;
        }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .main-content { margin-left: 0; }
            .toggle-btn { display: flex; }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center gap-3">
            <div class="sidebar-logo"><img src="<?= base_url('assets/img/logo-semen-padang.png') ?>" alt="Logo PT Semen Padang"></div>
            <div>
                <div class="sidebar-title">Kelola Ruangan</div>
                <div class="sidebar-subtitle">PT Semen Padang</div>
            </div>
        </div>
    </div>
    <div class="sidebar-band"></div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>

        <a href="<?= base_url('/') ?>" class="nav-link-item <?= (uri_string() === '' || uri_string() === '/') ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
            Dashboard
        </a>

        <a href="<?= base_url('rooms') ?>" class="nav-link-item <?= str_starts_with(uri_string(), 'rooms') ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-door-open-fill"></i></span>
            Data Ruangan
        </a>

        <a href="<?= base_url('jadwal') ?>" class="nav-link-item <?= str_starts_with(uri_string(), 'jadwal') ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-calendar3-week"></i></span>
            Pantau Jadwal
        </a>

        <div class="nav-section-label">Pemesanan</div>

        <a href="<?= base_url('bookings') ?>" class="nav-link-item <?= (uri_string() === 'bookings' || (str_starts_with(uri_string(), 'bookings') && !str_starts_with(uri_string(), 'bookings/create'))) ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-calendar-check-fill"></i></span>
            Daftar Booking
        </a>

        <a href="<?= base_url('bookings/create') ?>" class="nav-link-item <?= uri_string() === 'bookings/create' ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-plus-circle-fill"></i></span>
            Buat Booking
        </a>

        <div class="nav-section-label">Filter Status</div>

        <a href="<?= base_url('bookings?status=pending') ?>" class="nav-link-item">
            <span class="nav-icon" style="background:rgba(251,191,36,.15)"><i class="bi bi-clock-fill" style="color:#fbbf24"></i></span>
            Menunggu
        </a>
        <a href="<?= base_url('bookings?status=approved') ?>" class="nav-link-item">
            <span class="nav-icon" style="background:rgba(74,222,128,.15)"><i class="bi bi-check-circle-fill" style="color:#4ade80"></i></span>
            Disetujui
        </a>
        <a href="<?= base_url('bookings?status=rejected') ?>" class="nav-link-item">
            <span class="nav-icon" style="background:rgba(248,113,113,.15)"><i class="bi bi-x-circle-fill" style="color:#f87171"></i></span>
            Ditolak
        </a>

        <?php if (is_admin_role()): ?>
        <div class="nav-section-label">Administrasi</div>
        <a href="<?= base_url('reports/bookings') ?>" class="nav-link-item <?= str_starts_with(uri_string(), 'reports') ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-file-earmark-excel-fill"></i></span>
            Laporan Booking
        </a>
        <a href="<?= base_url('users') ?>" class="nav-link-item <?= str_starts_with(uri_string(), 'users') ? 'active' : '' ?>">
            <span class="nav-icon"><i class="bi bi-people-fill"></i></span>
            Manajemen User
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?= strtoupper(substr(session()->get('nama') ?? 'U', 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= esc(session()->get('nama') ?? 'Pengguna') ?></div>
                <div class="user-role"><?= strtoupper(role_label(session()->get('role'))) ?></div>
            </div>
            <a href="<?= base_url('profile') ?>" class="logout-btn" style="margin-left:auto" title="Akun Saya / Ubah Password">
                <i class="bi bi-gear-fill"></i>
            </a>
            <a href="<?= base_url('logout') ?>" class="logout-btn" title="Keluar">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</aside>

<!-- Main Content -->
<div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
        <button class="toggle-btn" id="toggleBtn">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div>
            <div class="topbar-title"><?= esc($title ?? '') ?></div>
            <div class="breadcrumb-topbar">
                <a href="<?= base_url('/') ?>">LPD</a>
                <span class="sep">›</span>
                <span><?= esc($title ?? '') ?></span>
            </div>
        </div>
        <div class="ms-auto">
            <div class="topbar-date">
                <i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">
        <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const toggleBtn= document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    });
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
