<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$dayNamesID = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'];
$dayShortID = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
$bulanID    = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$today      = date('Y-m-d');

function schedTimeToDecimal(string $t): float
{
    [$h, $m] = explode(':', $t);
    return (int) $h + ((int) $m) / 60;
}

function schedUrl(string $view, string $date, array $roomIds, array $overrides = []): string
{
    $p = array_merge(['view' => $view, 'date' => $date], $overrides);
    $qs = 'view=' . urlencode($p['view']) . '&date=' . urlencode($p['date']);
    foreach ($roomIds as $rid) { $qs .= '&rooms[]=' . (int) $rid; }
    return base_url('jadwal?' . $qs);
}

// Navigasi prev/next & label periode
if ($view === 'harian') {
    $prevDate    = date('Y-m-d', strtotime($date . ' -1 day'));
    $nextDate    = date('Y-m-d', strtotime($date . ' +1 day'));
    $periodLabel = $dayNamesID[(int) date('N', strtotime($date)) - 1]
        . ', ' . (int) date('j', strtotime($date))
        . ' ' . $bulanID[(int) date('n', strtotime($date)) - 1]
        . ' ' . date('Y', strtotime($date));
} elseif ($view === 'mingguan') {
    $prevDate    = date('Y-m-d', strtotime($rangeStart . ' -7 days'));
    $nextDate    = date('Y-m-d', strtotime($rangeStart . ' +7 days'));
    $periodLabel = (int) date('j', strtotime($rangeStart)) . ' ' . $bulanID[(int) date('n', strtotime($rangeStart)) - 1]
        . ' – ' . (int) date('j', strtotime($rangeEnd)) . ' ' . $bulanID[(int) date('n', strtotime($rangeEnd)) - 1]
        . ' ' . date('Y', strtotime($rangeEnd));
} else {
    $prevDate    = date('Y-m-d', strtotime($date . ' -1 month'));
    $nextDate    = date('Y-m-d', strtotime($date . ' +1 month'));
    $periodLabel = $bulanID[(int) date('n', strtotime($date)) - 1] . ' ' . date('Y', strtotime($date));
}

$roomById = [];
foreach ($selectedRooms as $r) { $roomById[$r['id']] = $r; }

$byDay = [];
if ($view !== 'bulanan') {
    foreach ($days as $d) { $byDay[$d] = []; }
    foreach ($bookings as $b) {
        if (!isset($roomById[$b['room_id']])) continue;
        foreach ($days as $d) {
            if ($d >= $b['tanggal_mulai'] && $d <= $b['tanggal_selesai']) {
                $byDay[$d][] = $b;
            }
        }
    }
}

function schedAssignLanes(array $items, string $day, int $gridStartHour, int $gridEndHour): array
{
    foreach ($items as &$it) {
        $s = ($day === $it['tanggal_mulai']) ? schedTimeToDecimal($it['jam_mulai']) : $gridStartHour;
        $e = ($day === $it['tanggal_selesai']) ? schedTimeToDecimal($it['jam_selesai']) : $gridEndHour;
        $it['_start'] = max($s, $gridStartHour);
        $it['_end']   = min($e, $gridEndHour);
    }
    unset($it);
    $items = array_values(array_filter($items, fn ($it) => $it['_end'] > $it['_start']));
    usort($items, fn ($a, $b) => $a['_start'] <=> $b['_start']);

    $laneEnds = [];
    foreach ($items as &$it) {
        $placed = false;
        foreach ($laneEnds as $li => $endT) {
            if ($it['_start'] >= $endT) {
                $it['_lane'] = $li;
                $laneEnds[$li] = $it['_end'];
                $placed = true;
                break;
            }
        }
        if (!$placed) {
            $li = count($laneEnds);
            $it['_lane']  = $li;
            $laneEnds[$li] = $it['_end'];
        }
    }
    unset($it);
    $totalLanes = max(count($laneEnds), 1);
    foreach ($items as &$it) { $it['_lanes'] = $totalLanes; }
    unset($it);
    return $items;
}

$hourSpan   = $gridEndHour - $gridStartHour;
$hourHeight = 60; // px per jam — lebih lapang
$gridHeight = $hourSpan * $hourHeight;

// Hitung total booking yang tampil (untuk info header)
$totalVisible = count($bookings);
?>

<style>
/* ─── SCHEDULE PAGE — IMPROVED ─────────────────────────── */

/* Wrapper utama */
.sched-page { display: flex; flex-direction: column; gap: 1rem; }

/* Toolbar atas */
.sched-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
    background: #fff;
    border: 1px solid #e8ede9;
    border-radius: 16px;
    padding: .9rem 1.25rem;
}
.sched-toolbar-left  { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
.sched-toolbar-right { display: flex; align-items: center; gap: .5rem; }

/* View tabs */
.sched-tabs-v2 {
    display: flex;
    background: #f3f4f6;
    border-radius: 10px;
    padding: 3px;
    gap: 2px;
}
.sched-tab-v2 {
    padding: .38rem 1rem;
    font-size: .8rem;
    font-weight: 600;
    color: #6b7280;
    border-radius: 8px;
    text-decoration: none;
    transition: all .18s;
    white-space: nowrap;
}
.sched-tab-v2:hover { color: #374151; background: rgba(255,255,255,.6); }
.sched-tab-v2.active {
    background: #fff;
    color: #8b1a24;
    box-shadow: 0 1px 4px rgba(0,0,0,.12);
}

/* Layout 2 kolom */
.sched-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1rem;
    align-items: start;
}
@media (max-width: 1024px) {
    .sched-layout { grid-template-columns: 1fr; }
}

/* Sidebar kiri */
.sched-sidebar-panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: sticky;
    top: calc(var(--topbar-h) + 1rem);
}

/* Card mini kalender */
.sched-card {
    background: #fff;
    border: 1px solid #e8ede9;
    border-radius: 16px;
    overflow: hidden;
}
.sched-card-head {
    padding: .85rem 1rem .6rem;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
}
.sched-card-title {
    font-size: .78rem;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: .06em;
}
.sched-card-body { padding: .85rem 1rem 1rem; }

/* Mini kalender */
.mini-cal-nav {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #6b7280;
    text-decoration: none;
    background: #f3f4f6;
    transition: all .15s;
    flex-shrink: 0;
    font-size: .85rem;
}
.mini-cal-nav:hover { background: #e5e7eb; color: #8b1a24; }

.mini-cal-month {
    font-size: .85rem;
    font-weight: 700;
    color: #1f2937;
}

.mini-cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
    text-align: center;
    margin-top: .5rem;
}
.mini-cal-dow {
    font-size: .65rem;
    color: #9ca3af;
    font-weight: 700;
    padding: 3px 0 4px;
    text-transform: uppercase;
}
.mini-cal-day {
    font-size: .76rem;
    padding: 5px 2px;
    border-radius: 7px;
    color: #374151;
    text-decoration: none;
    transition: all .13s;
    line-height: 1;
}
.mini-cal-day:hover { background: #f3f4f6; color: #1f2937; }
.mini-cal-day.is-today {
    background: #fef3c7;
    color: #b45309;
    font-weight: 700;
}
.mini-cal-day.is-selected {
    background: #8b1a24;
    color: #fff;
    font-weight: 700;
}
.mini-cal-day.is-today.is-selected {
    background: #8b1a24;
    color: #fff;
}

/* Filter ruangan */
.room-filter-list {
    max-height: 280px;
    overflow-y: auto;
    margin-bottom: .75rem;
    scrollbar-width: thin;
    scrollbar-color: #e5e7eb transparent;
}
.room-filter-list::-webkit-scrollbar { width: 4px; }
.room-filter-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

.room-filter-item {
    display: flex;
    align-items: center;
    gap: .55rem;
    padding: .45rem .5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background .13s;
    font-size: .82rem;
    color: #374151;
    font-weight: 500;
    user-select: none;
}
.room-filter-item:hover { background: #f9fafb; }
.room-filter-item input[type="checkbox"] {
    accent-color: #8b1a24;
    width: 15px; height: 15px;
    margin: 0; flex-shrink: 0;
}
.room-filter-code {
    font-size: .68rem;
    color: #9ca3af;
    background: #f3f4f6;
    border-radius: 4px;
    padding: 1px 5px;
    margin-left: auto;
    flex-shrink: 0;
}
.room-filter-quickbtns {
    display: flex; gap: .4rem; margin-bottom: .5rem;
}
.room-filter-quickbtn {
    font-size: .72rem;
    color: #6b7280;
    background: #f3f4f6;
    border: none;
    border-radius: 6px;
    padding: .25rem .6rem;
    cursor: pointer;
    transition: all .13s;
    font-weight: 500;
}
.room-filter-quickbtn:hover { background: #e5e7eb; color: #374151; }

/* ─── PANEL UTAMA ─── */
.sched-main-panel { min-width: 0; }

.sched-period-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .5rem;
    padding: .9rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
}
.sched-period-nav { display: flex; align-items: center; gap: .6rem; }
.sched-period-btn {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #6b7280;
    text-decoration: none;
    background: #f3f4f6;
    transition: all .15s;
    font-size: .85rem;
}
.sched-period-btn:hover { background: #e5e7eb; color: #8b1a24; }
.sched-period-label {
    font-size: .95rem;
    font-weight: 700;
    color: #1f2937;
    min-width: 0;
}
.sched-today-btn {
    font-size: .78rem;
    font-weight: 600;
    color: #6b7280;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    padding: .35rem .85rem;
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}
.sched-today-btn:hover { background: #e5e7eb; color: #8b1a24; }

.sched-info-bar {
    padding: .5rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: .75rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.sched-info-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 2px;
    margin-right: 4px;
}

/* ─── EMPTY STATE ─── */
.sched-empty {
    padding: 3.5rem 1.5rem;
    text-align: center;
    color: #9ca3af;
}
.sched-empty-icon {
    font-size: 2.8rem;
    opacity: .25;
    display: block;
    margin-bottom: .75rem;
}
.sched-empty-text { font-size: .9rem; margin-bottom: .25rem; }
.sched-empty-sub  { font-size: .78rem; }

/* ─── TAMPILAN BULANAN ─── */
.sched-month-wrapper { padding: 1rem 1.25rem; }

.month-head {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    text-align: center;
    margin-bottom: 4px;
}
.month-head-cell {
    font-size: .72rem;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    padding: .3rem 0;
    letter-spacing: .04em;
}
.month-head-cell.is-weekend { color: #d1d5db; }

.month-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    border: 1px solid #f0f0ee;
    border-radius: 12px;
    overflow: hidden;
}
.month-cell {
    border-right: 1px solid #f0f0ee;
    border-bottom: 1px solid #f0f0ee;
    min-height: 90px;
    padding: .5rem .55rem;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    gap: .3rem;
    color: inherit;
    background: #fff;
    transition: background .13s;
    position: relative;
}
.month-cell:nth-child(7n) { border-right: none; }
.month-cell:hover { background: #fafafa; z-index: 1; }
.month-cell.is-muted { background: #fafafa; }
.month-cell.is-muted .month-daynum { color: #d1d5db; }
.month-cell.is-today { background: #fffbeb; }
.month-cell.is-today .month-daynum {
    background: #8b1a24;
    color: #fff;
    border-radius: 6px;
    padding: 2px 6px;
    display: inline-block;
}
.month-cell.is-weekend { background: #fafaf9; }

.month-daynum {
    font-size: .82rem;
    font-weight: 700;
    color: #374151;
    line-height: 1.2;
}

.month-badge {
    font-size: .65rem;
    font-weight: 700;
    color: #fff;
    border-radius: 5px;
    padding: 2px 6px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    width: fit-content;
}
.month-badge.all-confirmed { background: #16a34a; }
.month-badge.has-pending   { background: #d97706; }
.month-badge i { font-size: .6rem; }

/* ─── TIME GRID (Harian/Mingguan) ─── */
.sched-timegrid-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    padding: 0 1.25rem 1.25rem;
    -webkit-overflow-scrolling: touch;
}

.sched-timegrid-v2 {
    display: flex;
    min-width: 500px;
    position: relative;
}

/* Sumbu jam */
.sched-axis-v2 {
    width: 56px;
    flex-shrink: 0;
    position: relative;
    padding-top: 42px; /* tinggi header hari */
}
.sched-axis-hour {
    position: absolute;
    left: 0;
    right: 0;
    text-align: right;
    padding-right: 10px;
    font-size: .65rem;
    color: #b0b8c4;
    font-weight: 600;
    line-height: 1;
    transform: translateY(-50%);
}

/* Kolom hari */
.sched-days-v2 {
    display: grid;
    flex: 1;
}

.sched-day-col-v2 {
    border-left: 1px solid #f0ede9;
    display: flex;
    flex-direction: column;
}
.sched-day-col-v2:first-child { border-left: none; }

.sched-day-header-v2 {
    padding: .55rem .4rem;
    text-align: center;
    border-bottom: 2px solid #f0ede9;
    flex-shrink: 0;
    background: #fafafa;
}
.sched-day-hdr-name {
    font-size: .7rem;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .05em;
    line-height: 1;
}
.sched-day-hdr-num {
    font-size: 1.1rem;
    font-weight: 800;
    color: #1f2937;
    line-height: 1.2;
    margin-top: 2px;
}
.sched-day-col-v2.is-today .sched-day-header-v2 {
    background: #fff7f7;
    border-bottom-color: #8b1a24;
}
.sched-day-col-v2.is-today .sched-day-hdr-name { color: #8b1a24; }
.sched-day-col-v2.is-today .sched-day-hdr-num  {
    color: #fff;
    background: #8b1a24;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 4px auto 0;
    font-size: 1rem;
}

.sched-day-body-v2 {
    position: relative;
    flex: 1;
    background: #fff;
}
.sched-day-col-v2.is-today .sched-day-body-v2 { background: #fffaf9; }

/* Garis jam di dalam body */
.sched-hour-lines {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.sched-hour-line {
    position: absolute;
    left: 0; right: 0;
    border-top: 1px dashed #f0ede9;
}
.sched-hour-line.is-full { border-top-style: solid; border-top-color: #e8e4e0; }
.sched-hour-line.is-half { border-top-color: #f5f2ef; }

/* Blok booking */
.sched-blocks-v2 { position: absolute; inset: 0; }

.sched-block-v2 {
    position: absolute;
    border-radius: 7px;
    padding: 5px 8px;
    overflow: hidden;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    box-shadow: 0 1px 4px rgba(0,0,0,.15), inset 0 1px 0 rgba(255,255,255,.2);
    border-left: 3px solid rgba(255,255,255,.4);
    transition: filter .15s, box-shadow .15s, z-index 0s;
}
.sched-block-v2:hover {
    filter: brightness(1.06);
    box-shadow: 0 4px 12px rgba(0,0,0,.25);
    z-index: 10;
}

.sched-block-v2.st-ongoing { background: linear-gradient(135deg, #dc2626, #ef4444); }
.sched-block-v2.st-pending { background: linear-gradient(135deg, #d97706, #f59e0b); }
.sched-block-v2.st-booked  { background: linear-gradient(135deg, #16a34a, #22c55e); }

.sb2-title {
    font-size: .72rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sb2-room {
    font-size: .64rem;
    color: rgba(255,255,255,.85);
    line-height: 1.2;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.sb2-time {
    font-size: .6rem;
    color: rgba(255,255,255,.75);
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 2px;
    white-space: nowrap;
}
.sb2-time i { font-size: .58rem; }

/* ─── LEGENDA ─── */
.sched-legend {
    display: flex;
    flex-wrap: wrap;
    gap: .6rem 1.25rem;
    padding: .75rem 1.25rem 1rem;
    border-top: 1px solid #f3f4f6;
}
.sched-legend-item {
    display: flex;
    align-items: center;
    gap: .45rem;
    font-size: .75rem;
    color: #6b7280;
    font-weight: 500;
}
.legend-dot {
    width: 12px; height: 12px;
    border-radius: 3px;
    flex-shrink: 0;
}
.legend-dot.ongoing { background: linear-gradient(135deg, #dc2626, #ef4444); }
.legend-dot.pending { background: linear-gradient(135deg, #d97706, #f59e0b); }
.legend-dot.booked  { background: linear-gradient(135deg, #16a34a, #22c55e); }
.legend-dot.empty   { background: #fff; border: 1.5px solid #d1d5db; }

/* ─── RESPONSIVE ─── */
@media (max-width: 768px) {
    .sched-toolbar { padding: .7rem 1rem; }
    .sched-month-wrapper { padding: .75rem; }
    .sched-timegrid-wrapper { padding: 0 .75rem .75rem; }
    .month-cell { min-height: 64px; padding: .35rem .4rem; }
    .sched-period-label { font-size: .85rem; }
}
</style>

<div class="sched-page">

    <!-- ── TOOLBAR ── -->
    <div class="sched-toolbar">
        <div class="sched-toolbar-left">
            <div class="sched-tabs-v2">
                <a href="<?= schedUrl('harian',  $date, $selectedRoomIds) ?>"
                   class="sched-tab-v2 <?= $view === 'harian'  ? 'active' : '' ?>">
                    <i class="bi bi-calendar-day me-1"></i>Harian
                </a>
                <a href="<?= schedUrl('mingguan', $date, $selectedRoomIds) ?>"
                   class="sched-tab-v2 <?= $view === 'mingguan' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-week me-1"></i>Mingguan
                </a>
                <a href="<?= schedUrl('bulanan',  $date, $selectedRoomIds) ?>"
                   class="sched-tab-v2 <?= $view === 'bulanan'  ? 'active' : '' ?>">
                    <i class="bi bi-calendar-month me-1"></i>Bulanan
                </a>
            </div>
            <span class="text-muted small d-none d-sm-inline">
                <i class="bi bi-door-open me-1"></i><?= count($allRooms) ?> ruangan terdaftar
            </span>
        </div>
        <div class="sched-toolbar-right">
            <a href="<?= base_url('bookings/create') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Buat Booking
            </a>
        </div>
    </div>

    <!-- ── LAYOUT 2 KOLOM ── -->
    <div class="sched-layout">

        <!-- SIDEBAR KIRI -->
        <div class="sched-sidebar-panel">

            <!-- Mini Kalender -->
            <div class="sched-card">
                <div class="sched-card-head">
                    <span class="sched-card-title"><i class="bi bi-calendar3 me-1"></i>Kalender</span>
                </div>
                <div class="sched-card-body">
                    <?php
                    $calMonthTs = strtotime(date('Y-m-01', strtotime($date)));
                    $calDow     = (int) date('N', $calMonthTs);
                    $calDays    = (int) date('t', $calMonthTs);
                    $calPrev    = date('Y-m-d', strtotime('-1 month', $calMonthTs));
                    $calNext    = date('Y-m-d', strtotime('+1 month', $calMonthTs));
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <a href="<?= schedUrl($view, $calPrev, $selectedRoomIds) ?>" class="mini-cal-nav">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <div class="mini-cal-month">
                            <?= $bulanID[(int) date('n', $calMonthTs) - 1] ?> <?= date('Y', $calMonthTs) ?>
                        </div>
                        <a href="<?= schedUrl($view, $calNext, $selectedRoomIds) ?>" class="mini-cal-nav">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>

                    <div class="mini-cal-grid">
                        <?php foreach (['S','S','R','K','J','S','M'] as $dl): ?>
                        <div class="mini-cal-dow"><?= $dl ?></div>
                        <?php endforeach; ?>

                        <?php for ($i = 1; $i < $calDow; $i++): ?>
                        <div></div>
                        <?php endfor; ?>

                        <?php for ($d = 1; $d <= $calDays; $d++):
                            $cellDate = date('Y-m-d', mktime(0, 0, 0, (int) date('n', $calMonthTs), $d, (int) date('Y', $calMonthTs)));
                            $isToday  = $cellDate === $today;
                            $isSel    = $cellDate === $date;
                        ?>
                        <a href="<?= schedUrl($view, $cellDate, $selectedRoomIds) ?>"
                           class="mini-cal-day <?= $isToday ? 'is-today' : '' ?> <?= $isSel ? 'is-selected' : '' ?>"><?= $d ?></a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Filter Ruangan -->
            <div class="sched-card">
                <div class="sched-card-head">
                    <span class="sched-card-title"><i class="bi bi-funnel me-1"></i>Filter Ruangan</span>
                    <span class="badge" style="background:#f3f4f6;color:#6b7280;font-size:.68rem;font-weight:600">
                        <?= count($selectedRoomIds) ?>/<?= count($allRooms) ?>
                    </span>
                </div>
                <div class="sched-card-body">
                    <form method="get" action="<?= base_url('jadwal') ?>">
                        <input type="hidden" name="view"             value="<?= esc($view) ?>">
                        <input type="hidden" name="date"             value="<?= esc($date) ?>">
                        <input type="hidden" name="filter_submitted" value="1">

                        <div class="room-filter-quickbtns mb-1">
                            <button type="button" class="room-filter-quickbtn"
                                    onclick="document.querySelectorAll('.rf-cb').forEach(cb=>cb.checked=true)">
                                Pilih Semua
                            </button>
                            <button type="button" class="room-filter-quickbtn"
                                    onclick="document.querySelectorAll('.rf-cb').forEach(cb=>cb.checked=false)">
                                Hapus Semua
                            </button>
                        </div>

                        <div class="room-filter-list">
                            <?php foreach ($allRooms as $r): ?>
                            <label class="room-filter-item">
                                <input class="rf-cb" type="checkbox" name="rooms[]"
                                       value="<?= $r['id'] ?>"
                                       <?= in_array((int) $r['id'], $selectedRoomIds, true) ? 'checked' : '' ?>>
                                <span class="flex-grow-1 text-truncate"><?= esc($r['nama_ruangan']) ?></span>
                                <span class="room-filter-code"><?= esc($r['kode_ruangan']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-1">
                            <i class="bi bi-check2 me-1"></i>Terapkan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Legenda (di sidebar, bukan bawah grid) -->
            <div class="sched-card">
                <div class="sched-card-head">
                    <span class="sched-card-title"><i class="bi bi-info-circle me-1"></i>Keterangan</span>
                </div>
                <div class="sched-card-body" style="padding-top:.6rem">
                    <div class="d-flex flex-column gap-2">
                        <div class="sched-legend-item">
                            <span class="legend-dot ongoing"></span>Sedang Berlangsung
                        </div>
                        <div class="sched-legend-item">
                            <span class="legend-dot pending"></span>Menunggu Konfirmasi
                        </div>
                        <div class="sched-legend-item">
                            <span class="legend-dot booked"></span>Sudah Dikonfirmasi
                        </div>
                        <div class="sched-legend-item">
                            <span class="legend-dot empty"></span>Tidak Ada Booking
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /sidebar -->

        <!-- PANEL UTAMA -->
        <div class="sched-main-panel">
            <div class="sched-card">

                <!-- Header periode navigasi -->
                <div class="sched-period-bar">
                    <div class="sched-period-nav">
                        <a href="<?= schedUrl($view, $prevDate, $selectedRoomIds) ?>" class="sched-period-btn">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <div class="sched-period-label"><?= $periodLabel ?></div>
                        <a href="<?= schedUrl($view, $nextDate, $selectedRoomIds) ?>" class="sched-period-btn">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                    <a href="<?= schedUrl($view, $today, $selectedRoomIds) ?>" class="sched-today-btn">
                        <i class="bi bi-calendar-check me-1"></i>Hari Ini
                    </a>
                </div>

                <!-- Info bar -->
                <?php if (!empty($selectedRooms)): ?>
                <div class="sched-info-bar">
                    <span>
                        <i class="bi bi-door-open me-1"></i>
                        <strong><?= count($selectedRooms) ?></strong> ruangan dipilih
                    </span>
                    <?php if ($view !== 'bulanan'): ?>
                    <span>
                        <i class="bi bi-calendar-event me-1"></i>
                        <strong><?= $totalVisible ?></strong> booking ditampilkan
                    </span>
                    <?php endif; ?>
                    <span class="ms-auto text-muted">
                        Grid: <?= sprintf('%02d:00', $gridStartHour) ?> – <?= sprintf('%02d:00', $gridEndHour) ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- ── KONTEN ── -->

                <?php if (empty($selectedRooms)): ?>
                <!-- Empty: belum pilih ruangan -->
                <div class="sched-empty">
                    <i class="bi bi-calendar-x sched-empty-icon"></i>
                    <div class="sched-empty-text fw-semibold">Belum ada ruangan dipilih</div>
                    <div class="sched-empty-sub">Pilih minimal satu ruangan di panel kiri untuk menampilkan jadwal.</div>
                </div>

                <?php elseif ($view === 'bulanan'): ?>
                <!-- ════════════ TAMPILAN BULANAN ════════════ -->
                <div class="sched-month-wrapper">
                    <div class="month-head">
                        <?php foreach ($dayNamesID as $idx => $dn): ?>
                        <div class="month-head-cell <?= $idx >= 5 ? 'is-weekend' : '' ?>"><?= $dn ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="month-grid">
                        <?php foreach ($days as $d):
                            $inMonth   = date('Y-m', strtotime($d)) === date('Y-m', strtotime($date));
                            $isToday   = $d === $today;
                            $dow       = (int) date('N', strtotime($d)); // 1=Sen..7=Min
                            $isWeekend = $dow >= 6;
                            $c         = $monthCounts[$d] ?? ['total' => 0, 'pending' => 0];
                        ?>
                        <a href="<?= schedUrl('harian', $d, $selectedRoomIds) ?>"
                           class="month-cell
                               <?= !$inMonth  ? 'is-muted'   : '' ?>
                               <?= $isToday   ? 'is-today'   : '' ?>
                               <?= $isWeekend && $inMonth ? 'is-weekend' : '' ?>">
                            <div class="month-daynum"><?= (int) date('j', strtotime($d)) ?></div>
                            <?php if ($c['total'] > 0): ?>
                            <div class="month-badge <?= $c['pending'] > 0 ? 'has-pending' : 'all-confirmed' ?>">
                                <i class="bi bi-calendar-check"></i>
                                <?= $c['total'] ?>
                            </div>
                            <?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php else: ?>
                <!-- ════════════ TAMPILAN HARIAN / MINGGUAN ════════════ -->
                <div class="sched-timegrid-wrapper">
                    <div class="sched-timegrid-v2">

                        <!-- Sumbu jam -->
                        <div class="sched-axis-v2" style="height:<?= $gridHeight + 42 ?>px">
                            <?php for ($h = $gridStartHour; $h <= $gridEndHour; $h++): ?>
                            <div class="sched-axis-hour"
                                 style="top:<?= 42 + ($h - $gridStartHour) * $hourHeight ?>px">
                                <?= sprintf('%02d:00', $h) ?>
                            </div>
                            <?php endfor; ?>
                        </div>

                        <!-- Kolom hari -->
                        <div class="sched-days-v2"
                             style="grid-template-columns:repeat(<?= count($days) ?>,1fr)">
                            <?php foreach ($days as $d):
                                $isToday = $d === $today;
                                $dow     = (int) date('N', strtotime($d));
                            ?>
                            <div class="sched-day-col-v2 <?= $isToday ? 'is-today' : '' ?>">

                                <!-- Header hari -->
                                <div class="sched-day-header-v2">
                                    <div class="sched-day-hdr-name"><?= $dayShortID[$dow - 1] ?></div>
                                    <div class="sched-day-hdr-num"><?= (int) date('j', strtotime($d)) ?></div>
                                </div>

                                <!-- Body timeline -->
                                <div class="sched-day-body-v2" style="height:<?= $gridHeight ?>px">

                                    <!-- Garis jam -->
                                    <div class="sched-hour-lines">
                                        <?php for ($h = $gridStartHour; $h <= $gridEndHour; $h++):
                                            $top = ($h - $gridStartHour) * $hourHeight;
                                        ?>
                                        <div class="sched-hour-line is-full"
                                             style="top:<?= $top ?>px"></div>
                                        <?php if ($h < $gridEndHour): ?>
                                        <div class="sched-hour-line is-half"
                                             style="top:<?= $top + $hourHeight / 2 ?>px"></div>
                                        <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>

                                    <!-- Blok booking -->
                                    <div class="sched-blocks-v2">
                                        <?php foreach (schedAssignLanes($byDay[$d] ?? [], $d, $gridStartHour, $gridEndHour) as $b):
                                            $top      = ($b['_start'] - $gridStartHour) * $hourHeight;
                                            $height   = max(($b['_end'] - $b['_start']) * $hourHeight, 28);
                                            $laneW    = 100 / $b['_lanes'];
                                            $left     = $b['_lane'] * $laneW;
                                            $room     = $roomById[$b['room_id']] ?? null;
                                            $roomName = $room ? $room['nama_ruangan'] : '';
                                            $canOpen  = is_admin_role() || (int) ($b['user_id'] ?? 0) === (int) session()->get('user_id');
                                            $tag      = $canOpen ? 'a' : 'div';
                                            $href     = $canOpen ? 'href="' . base_url("bookings/{$b['id']}") . '"' : '';
                                            $status   = $b['sched_status'];
                                        ?>
                                        <<?= $tag ?> <?= $href ?>
                                            class="sched-block-v2 st-<?= $status ?>"
                                            style="top:<?= $top ?>px;height:<?= $height ?>px;left:calc(<?= $left ?>% + 2px);width:calc(<?= $laneW ?>% - 4px)"
                                            title="<?= esc($b['nama_peminjam']) ?> — <?= esc($roomName) ?> (<?= substr($b['jam_mulai'],0,5) ?>–<?= substr($b['jam_selesai'],0,5) ?>)">
                                            <div class="sb2-title"><?= esc($b['nama_peminjam']) ?></div>
                                            <?php if ($height >= 40): ?>
                                            <div class="sb2-room"><?= esc($roomName) ?></div>
                                            <?php endif; ?>
                                            <?php if ($height >= 52): ?>
                                            <div class="sb2-time">
                                                <i class="bi bi-clock"></i>
                                                <?= substr($b['jam_mulai'],0,5) ?>–<?= substr($b['jam_selesai'],0,5) ?>
                                            </div>
                                            <?php endif; ?>
                                        </<?= $tag ?>>
                                        <?php endforeach; ?>
                                    </div>

                                </div><!-- /body -->
                            </div><!-- /day-col -->
                            <?php endforeach; ?>
                        </div><!-- /days -->

                    </div><!-- /timegrid -->
                </div><!-- /wrapper -->

                <?php endif; ?>

            </div><!-- /sched-card -->
        </div><!-- /main-panel -->

    </div><!-- /layout -->
</div><!-- /sched-page -->

<?= $this->endSection() ?>
