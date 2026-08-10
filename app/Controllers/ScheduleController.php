<?php

namespace App\Controllers;

use App\Models\RoomModel;
use CodeIgniter\Database\BaseConnection;

class ScheduleController extends BaseController
{
    protected RoomModel      $roomModel;
    protected BaseConnection $db;

    protected int $gridStartHour = 7;  // jam mulai grid (07:00)
    protected int $gridEndHour   = 22; // jam akhir grid (22:00)

    public function __construct()
    {
        $this->roomModel = new RoomModel();
        $this->db        = \Config\Database::connect();
    }

    public function index()
    {
        $view = $this->request->getGet('view') ?? 'mingguan';
        if (!in_array($view, ['harian', 'mingguan', 'bulanan'], true)) {
            $view = 'mingguan';
        }

        $dateParam = $this->request->getGet('date');
        $date = ($dateParam && strtotime($dateParam)) ? $dateParam : date('Y-m-d');

        $allRooms   = $this->roomModel->orderBy('nama_ruangan', 'ASC')->findAll();
        $roomIdsGet = $this->request->getGet('rooms');
        $filterSubmitted = $this->request->getGet('filter_submitted');
        if (is_array($roomIdsGet) && count($roomIdsGet) > 0) {
            $selectedRoomIds = array_map('intval', $roomIdsGet);
        } elseif ($filterSubmitted) {
            // Form filter disubmit tapi tidak ada ruangan dicentang — hormati pilihan kosong tsb.
            $selectedRoomIds = [];
        } else {
            // Kunjungan pertama tanpa query string — tampilkan semua ruangan secara default.
            $selectedRoomIds = array_map(fn ($r) => (int) $r['id'], $allRooms);
        }
        $selectedRooms = array_values(array_filter($allRooms, fn ($r) => in_array((int) $r['id'], $selectedRoomIds, true)));

        // Tentukan rentang tanggal yang ditampilkan sesuai jenis tampilan
        [$rangeStart, $rangeEnd, $days] = $this->computeRange($view, $date);

        $bookings = [];
        if (!empty($selectedRoomIds)) {
            $bookings = $this->db->table('bookings b')
                ->select('b.*, r.nama_ruangan, r.kode_ruangan')
                ->join('rooms r', 'r.id = b.room_id')
                ->whereIn('b.room_id', $selectedRoomIds)
                ->whereIn('b.status', ['pending', 'approved', 'selesai'])
                ->where('b.tanggal_mulai <=', $rangeEnd)
                ->where('b.tanggal_selesai >=', $rangeStart)
                ->orderBy('b.jam_mulai', 'ASC')
                ->get()
                ->getResultArray();

            $today = date('Y-m-d');
            $now   = date('H:i:s');
            foreach ($bookings as &$b) {
                if ($b['status'] === 'pending') {
                    $b['sched_status'] = 'pending';
                } elseif (
                    $b['tanggal_mulai'] <= $today && $b['tanggal_selesai'] >= $today
                    && $b['jam_mulai'] <= $now && $b['jam_selesai'] > $now
                ) {
                    $b['sched_status'] = 'ongoing';
                } else {
                    $b['sched_status'] = 'booked';
                }
            }
            unset($b);
        }

        // Untuk tampilan bulanan: hitung ringkasan jumlah booking per tanggal
        $monthCounts = [];
        if ($view === 'bulanan') {
            foreach ($bookings as $b) {
                $cursor = max($b['tanggal_mulai'], $rangeStart);
                $stop   = min($b['tanggal_selesai'], $rangeEnd);
                while ($cursor <= $stop) {
                    if (!isset($monthCounts[$cursor])) {
                        $monthCounts[$cursor] = ['total' => 0, 'pending' => 0];
                    }
                    $monthCounts[$cursor]['total']++;
                    if ($b['sched_status'] === 'pending') {
                        $monthCounts[$cursor]['pending']++;
                    }
                    $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
                }
            }
        }

        return view('schedule/index', [
            'title'            => 'Pantau Jadwal Ruangan',
            'view'             => $view,
            'date'             => $date,
            'rangeStart'       => $rangeStart,
            'rangeEnd'         => $rangeEnd,
            'days'             => $days,
            'allRooms'         => $allRooms,
            'selectedRoomIds'  => $selectedRoomIds,
            'selectedRooms'    => $selectedRooms,
            'bookings'         => $bookings,
            'monthCounts'      => $monthCounts,
            'gridStartHour'    => $this->gridStartHour,
            'gridEndHour'      => $this->gridEndHour,
        ]);
    }

    /**
     * Menghitung tanggal awal, tanggal akhir, dan daftar tanggal yang ditampilkan
     * sesuai jenis tampilan (harian/mingguan/bulanan).
     */
    protected function computeRange(string $view, string $date): array
    {
        $ts = strtotime($date);

        if ($view === 'harian') {
            $start = $end = date('Y-m-d', $ts);
            $days  = [$start];
            return [$start, $end, $days];
        }

        if ($view === 'mingguan') {
            $dow    = (int) date('N', $ts); // 1 (Senin) - 7 (Minggu)
            $start  = date('Y-m-d', strtotime("-" . ($dow - 1) . " days", $ts));
            $end    = date('Y-m-d', strtotime("+6 days", strtotime($start)));
            $days   = [];
            for ($i = 0; $i < 7; $i++) {
                $days[] = date('Y-m-d', strtotime("+{$i} days", strtotime($start)));
            }
            return [$start, $end, $days];
        }

        // bulanan
        $start = date('Y-m-01', $ts);
        $end   = date('Y-m-t', $ts);
        // Lengkapi ke grid 7 kolom (mulai Senin) supaya kalender rapi
        $gridStart = date('Y-m-d', strtotime('-' . ((int) date('N', strtotime($start)) - 1) . ' days', strtotime($start)));
        $gridEndTs = strtotime($end);
        $gridEnd   = date('Y-m-d', strtotime('+' . (7 - (int) date('N', $gridEndTs)) . ' days', $gridEndTs));
        $days = [];
        $cursor = $gridStart;
        while ($cursor <= $gridEnd) {
            $days[] = $cursor;
            $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
        }
        return [$start, $end, $days];
    }
}
