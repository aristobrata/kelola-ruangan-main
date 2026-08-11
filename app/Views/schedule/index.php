<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$dayNamesID = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'];
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

// Navigasi prev/next & label periode sesuai jenis tampilan
if ($view === 'harian') {
    $prevDate = date('Y-m-d', strtotime($date . ' -1 day'));
    $nextDate = date('Y-m-d', strtotime($date . ' +1 day'));
    $periodLabel = $dayNamesID[(int) date('N', strtotime($date)) - 1] . ', ' . (int) date('j', strtotime($date)) . ' ' . $bulanID[(int) date('n', strtotime($date)) - 1] . ' ' . date('Y', strtotime($date));
} elseif ($view === 'mingguan') {
    $prevDate = date('Y-m-d', strtotime($rangeStart . ' -7 days'));
    $nextDate = date('Y-m-d', strtotime($rangeStart . ' +7 days'));
    $periodLabel = (int) date('j', strtotime($rangeStart)) . ' ' . $bulanID[(int) date('n', strtotime($rangeStart)) - 1]
        . ' – ' . (int) date('j', strtotime($rangeEnd)) . ' ' . $bulanID[(int) date('n', strtotime($rangeEnd)) - 1] . ' ' . date('Y', strtotime($rangeEnd));
} else {
    $prevDate = date('Y-m-d', strtotime($date . ' -1 month'));
    $nextDate = date('Y-m-d', strtotime($date . ' +1 month'));
    $periodLabel = $bulanID[(int) date('n', strtotime($date)) - 1] . ' ' . date('Y', strtotime($date));
}

// Kelompokkan booking per tanggal (gabungan semua ruangan terpilih) untuk tampilan harian/mingguan
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

/**
 * Hitung posisi waktu (jam desimal, jam clamp ke rentang grid) tiap booking pada
 * hari tertentu, lalu bagi ke "lane" berdampingan hanya untuk booking yang jamnya
 * benar-benar tumpang tindih — supaya lebar kolom tidak dipecah rata berdasarkan
 * jumlah ruangan yang dicentang, tapi mengikuti tumpang tindih jadwal yang nyata.
 */
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
            $it['_lane'] = $li;
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
$hourHeight = 52; // px per jam
$gridHeight = $hourSpan * $hourHeight;
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="text-muted small"><i class="bi bi-door-open me-1"></i>Total <?= count($allRooms) ?> Ruangan Terdaftar</div>
    <div class="d-flex align-items-center gap-2">
        <div class="sched-tabs">
            <a href="<?= schedUrl('harian', $date, $selectedRoomIds) ?>" class="sched-tab <?= $view === 'harian' ? 'active' : '' ?>">Harian</a>
            <a href="<?= schedUrl('mingguan', $date, $selectedRoomIds) ?>" class="sched-tab <?= $view === 'mingguan' ? 'active' : '' ?>">Mingguan</a>
            <a href="<?= schedUrl('bulanan', $date, $selectedRoomIds) ?>" class="sched-tab <?= $view === 'bulanan' ? 'active' : '' ?>">Bulanan</a>
        </div>
        <a href="<?= base_url('bookings/create') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Buat Booking
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- SIDEBAR -->
    <div class="col-lg-3">
        <!-- Mini kalender -->
        <div class="form-section mb-3">
            <?php
            $calMonthTs = strtotime(date('Y-m-01', strtotime($date)));
            $calDow     = (int) date('N', $calMonthTs);
            $calDays    = (int) date('t', $calMonthTs);
            $calPrev    = date('Y-m-d', strtotime('-1 month', $calMonthTs));
            $calNext    = date('Y-m-d', strtotime('+1 month', $calMonthTs));
            ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <a href="<?= schedUrl($view, $calPrev, $selectedRoomIds) ?>" class="sched-mini-nav"><i class="bi bi-chevron-left"></i></a>
                <div class="fw-semibold" style="font-size:.85rem"><?= $bulanID[(int) date('n', $calMonthTs) - 1] ?> <?= date('Y', $calMonthTs) ?></div>
                <a href="<?= schedUrl($view, $calNext, $selectedRoomIds) ?>" class="sched-mini-nav"><i class="bi bi-chevron-right"></i></a>
            </div>
            <div class="sched-mini-grid">
                <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $dl): ?>
                <div class="sched-mini-dl"><?= $dl ?></div>
                <?php endforeach; ?>
                <?php for ($i = 1; $i < $calDow; $i++): ?>
                <div></div>
                <?php endfor; ?>
                <?php for ($d = 1; $d <= $calDays; $d++):
                    $cellDate = date('Y-m-d', mktime(0, 0, 0, (int) date('n', $calMonthTs), $d, (int) date('Y', $calMonthTs)));
                    $isToday  = $cellDate === $today;
                    $isSel    = $cellDate === $date;
                ?>
                <a href="<?= schedUrl($view, $cellDate, $selectedRoomIds) ?>" class="sched-mini-day <?= $isToday ? 'is-today' : '' ?> <?= $isSel ? 'is-selected' : '' ?>"><?= $d ?></a>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Filter Ruangan -->
        <div class="form-section">
            <div class="fw-semibold mb-2" style="font-size:.85rem">Pilih Ruangan</div>
            <form method="get" action="<?= base_url('jadwal') ?>">
                <input type="hidden" name="view" value="<?= esc($view) ?>">
                <input type="hidden" name="date" value="<?= esc($date) ?>">
                <input type="hidden" name="filter_submitted" value="1">
                <div class="sched-room-list mb-2">
                    <?php foreach ($allRooms as $r): ?>
                    <label class="sched-room-item">
                        <input type="checkbox" name="rooms[]" value="<?= $r['id'] ?>" <?= in_array((int) $r['id'], $selectedRoomIds, true) ? 'checked' : '' ?>>
                        <span><?= esc($r['nama_ruangan']) ?></span>
                        <span class="text-muted" style="font-size:.72rem">(<?= esc($r['kode_ruangan']) ?>)</span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Terapkan Filter</button>
            </form>
        </div>
    </div>

    <!-- MAIN -->
    <div class="col-lg-9">
        <div class="form-section mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= schedUrl($view, $prevDate, $selectedRoomIds) ?>" class="sched-mini-nav"><i class="bi bi-chevron-left"></i></a>
                    <div class="fw-bold"><?= $periodLabel ?></div>
                    <a href="<?= schedUrl($view, $nextDate, $selectedRoomIds) ?>" class="sched-mini-nav"><i class="bi bi-chevron-right"></i></a>
                </div>
                <a href="<?= schedUrl($view, $today, $selectedRoomIds) ?>" class="btn btn-outline-secondary btn-sm">Hari Ini</a>
            </div>

            <?php if (empty($selectedRooms)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-calendar-x d-block fs-2 mb-2 opacity-25"></i>
                Pilih minimal satu ruangan untuk menampilkan jadwal.
            </div>

            <?php elseif ($view === 'bulanan'): ?>
            <!-- ==================== TAMPILAN BULANAN ==================== -->
            <div class="sched-month-head">
                <?php foreach ($dayNamesID as $dn): ?>
                <div><?= $dn ?></div>
                <?php endforeach; ?>
            </div>
            <?php foreach (array_chunk($days, 7) as $week): ?>
            <div class="sched-month-row">
                <?php foreach ($week as $d):
                    $inMonth = date('Y-m', strtotime($d)) === date('Y-m', strtotime($date));
                    $isToday = $d === $today;
                    $c = $monthCounts[$d] ?? ['total' => 0, 'pending' => 0];
                ?>
                <a href="<?= schedUrl('harian', $d, $selectedRoomIds) ?>" class="sched-month-cell <?= !$inMonth ? 'is-muted' : '' ?> <?= $isToday ? 'is-today' : '' ?>">
                    <div class="sched-month-daynum"><?= (int) date('j', strtotime($d)) ?></div>
                    <?php if ($c['total'] > 0): ?>
                    <div class="sched-month-badge <?= $c['pending'] > 0 ? 'has-pending' : 'all-confirmed' ?>"><?= $c['total'] ?> booking</div>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>

            <?php else: ?>
            <!-- ==================== TAMPILAN HARIAN / MINGGUAN ==================== -->
            <div class="sched-timegrid">
                <div class="sched-axis" style="height:<?= $gridHeight ?>px">
                    <?php for ($h = $gridStartHour; $h <= $gridEndHour; $h++): ?>
                    <div class="sched-axis-label" style="top:<?= ($h - $gridStartHour) * $hourHeight - 7 ?>px"><?= sprintf('%02d:00', $h) ?></div>
                    <?php endfor; ?>
                </div>
                <div class="sched-days" style="grid-template-columns:repeat(<?= count($days) ?>,1fr)">
                    <?php foreach ($days as $d):
                        $isToday = $d === $today;
                    ?>
                    <div class="sched-day-col">
                        <div class="sched-day-header <?= $isToday ? 'is-today' : '' ?>">
                            <?= $dayNamesID[(int) date('N', strtotime($d)) - 1] ?>
                            <div class="sched-day-num"><?= (int) date('j', strtotime($d)) ?></div>
                        </div>
                        <div class="sched-day-body <?= $isToday ? 'is-today' : '' ?>" style="height:<?= $gridHeight ?>px">
                            <?php for ($h = $gridStartHour; $h <= $gridEndHour; $h++): ?>
                            <div class="sched-gridline" style="top:<?= ($h - $gridStartHour) * $hourHeight ?>px"></div>
                            <?php endfor; ?>
                            <div class="sched-blocks">
                                <?php foreach (schedAssignLanes($byDay[$d] ?? [], $d, $gridStartHour, $gridEndHour) as $b):
                                    $top       = ($b['_start'] - $gridStartHour) * $hourHeight;
                                    $height    = max(($b['_end'] - $b['_start']) * $hourHeight, 24);
                                    $laneW     = 100 / $b['_lanes'];
                                    $left      = $b['_lane'] * $laneW;
                                    $room      = $roomById[$b['room_id']] ?? null;
                                    $roomName  = $room ? $room['nama_ruangan'] : '';
                                    $canOpen   = is_admin_role() || (int) ($b['user_id'] ?? 0) === (int) session()->get('user_id');
                                    $tag       = $canOpen ? 'a' : 'div';
                                    $hrefAttr  = $canOpen ? 'href="' . base_url("bookings/{$b['id']}") . '"' : '';
                                ?>
                                <<?= $tag ?> <?= $hrefAttr ?> class="sched-block sched-block-<?= $b['sched_status'] ?>"
                                   style="top:<?= $top ?>px;height:<?= $height ?>px;left:calc(<?= $left ?>% + 2px);width:calc(<?= $laneW ?>% - 4px)"
                                   title="<?= esc($b['nama_peminjam']) ?> — <?= esc($roomName) ?> (<?= substr($b['jam_mulai'],0,5) ?>–<?= substr($b['jam_selesai'],0,5) ?>)">
                                    <div class="sb-title"><?= esc($b['nama_peminjam']) ?></div>
                                    <div class="sb-room"><?= esc($roomName) ?></div>
                                    <?php if ($height >= 46): ?>
                                    <div class="sb-time"><?= substr($b['jam_mulai'],0,5) ?>–<?= substr($b['jam_selesai'],0,5) ?></div>
                                    <?php endif; ?>
                                </<?= $tag ?>>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Legenda -->
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <div class="sched-legend-item"><span class="sched-dot sched-block-ongoing"></span>Sedang Digunakan</div>
            <div class="sched-legend-item"><span class="sched-dot sched-block-pending"></span>Menunggu Konfirmasi</div>
            <div class="sched-legend-item"><span class="sched-dot sched-block-booked"></span>Sudah Dikonfirmasi</div>
            <div class="sched-legend-item"><span class="sched-dot sched-dot-empty"></span>Tersedia</div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
