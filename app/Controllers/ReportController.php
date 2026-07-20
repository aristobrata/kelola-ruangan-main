<?php

namespace App\Controllers;

use App\Libraries\SimpleXlsxWriter;
use App\Models\RoomModel;
use CodeIgniter\Database\BaseConnection;

class ReportController extends BaseController
{
    protected BaseConnection $db;
    protected RoomModel $roomModel;

    protected array $statusLabels = [
        'pending'   => 'Menunggu',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'selesai'   => 'Selesai',
    ];

    public function __construct()
    {
        $this->db        = \Config\Database::connect();
        $this->roomModel = new RoomModel();
    }

    /**
     * Ambil data booking sesuai filter (dipakai bareng oleh halaman & export).
     */
    protected function getFilteredBookings(): array
    {
        $tanggalDari = $this->request->getGet('tanggal_dari') ?? '';
        $tanggalSampai = $this->request->getGet('tanggal_sampai') ?? '';
        $roomId      = $this->request->getGet('room_id') ?? '';
        $status      = $this->request->getGet('status') ?? '';

        $builder = $this->db->table('bookings b')
            ->select('b.*, r.nama_ruangan, r.kode_ruangan, r.lokasi')
            ->join('rooms r', 'r.id = b.room_id')
            ->orderBy('b.tanggal_mulai', 'DESC')
            ->orderBy('b.jam_mulai', 'DESC');

        if ($tanggalDari) {
            $builder->where('b.tanggal_mulai >=', $tanggalDari);
        }
        if ($tanggalSampai) {
            $builder->where('b.tanggal_mulai <=', $tanggalSampai);
        }
        if ($roomId) {
            $builder->where('b.room_id', $roomId);
        }
        if ($status) {
            $builder->where('b.status', $status);
        }

        return $builder->get()->getResultArray();
    }

    protected function getFilters(): array
    {
        return [
            'tanggal_dari'   => $this->request->getGet('tanggal_dari') ?? '',
            'tanggal_sampai' => $this->request->getGet('tanggal_sampai') ?? '',
            'room_id'        => $this->request->getGet('room_id') ?? '',
            'status'         => $this->request->getGet('status') ?? '',
        ];
    }

    /**
     * Halaman laporan: tampilkan filter + pratinjau data + tombol export.
     */
    public function bookings()
    {
        $bookings = $this->getFilteredBookings();
        $filters  = $this->getFilters();
        $rooms    = $this->roomModel->orderBy('nama_ruangan', 'ASC')->findAll();

        return view('reports/bookings', [
            'title'         => 'Laporan Riwayat Booking',
            'bookings'      => $bookings,
            'rooms'         => $rooms,
            'filters'       => $filters,
            'statusLabels'  => $this->statusLabels,
        ]);
    }

    /**
     * Export laporan riwayat booking ke file Excel (.xlsx) sesuai filter yang dipilih.
     */
    public function exportExcel()
    {
        $bookings = $this->getFilteredBookings();

        $headers = [
            'No', 'Kode Ruangan', 'Ruangan', 'Peminjam', 'Instansi', 'Keperluan',
            'Tanggal Mulai', 'Tanggal Selesai', 'Jam Mulai', 'Jam Selesai',
            'Jumlah Peserta', 'Konsumsi', 'Status', 'Catatan', 'Dibuat Pada',
        ];

        $rows = [];
        foreach ($bookings as $i => $b) {
            $rows[] = [
                $i + 1,
                $b['kode_ruangan'],
                $b['nama_ruangan'],
                $b['nama_peminjam'],
                $b['instansi'] ?? '-',
                $b['keperluan'],
                date('d-m-Y', strtotime($b['tanggal_mulai'])),
                date('d-m-Y', strtotime($b['tanggal_selesai'])),
                substr($b['jam_mulai'], 0, 5),
                substr($b['jam_selesai'], 0, 5),
                (int) $b['jumlah_peserta'],
                $b['konsumsi'] ?? '-',
                $this->statusLabels[$b['status']] ?? $b['status'],
                $b['catatan'] ?? '-',
                $b['created_at'] ? date('d-m-Y H:i', strtotime($b['created_at'])) : '-',
            ];
        }

        $columnWidths = [5, 14, 20, 20, 20, 28, 14, 14, 11, 11, 10, 18, 12, 25, 18];

        $filenameParts = ['Laporan-Booking'];
        $filters = $this->getFilters();
        if ($filters['tanggal_dari']) {
            $filenameParts[] = $filters['tanggal_dari'];
        }
        if ($filters['tanggal_sampai']) {
            $filenameParts[] = $filters['tanggal_sampai'];
        }
        $filename = implode('_', $filenameParts);

        $writer = new SimpleXlsxWriter($headers, $rows, 'Laporan Booking', $columnWidths);
        $writer->download($filename);
    }
}
