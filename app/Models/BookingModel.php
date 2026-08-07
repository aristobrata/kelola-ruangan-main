<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table      = 'bookings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'room_id', 'user_id', 'nama_peminjam', 'kontak', 'instansi', 'keperluan',
        'tanggal_mulai', 'tanggal_selesai', 'jam_mulai', 'jam_selesai',
        'jumlah_peserta', 'konsumsi', 'biaya_konsumsi', 'penanggung_biaya',
        'status', 'catatan',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'room_id'        => 'required|integer',
        'nama_peminjam'  => 'required|max_length[100]',
        'keperluan'      => 'required',
        'tanggal_mulai'  => 'required|valid_date[Y-m-d]',
        'tanggal_selesai'=> 'required|valid_date[Y-m-d]',
        'jam_mulai'      => 'required',
        'jam_selesai'    => 'required',
        'jumlah_peserta' => 'required|integer|greater_than[0]',
    ];

    protected $validationMessages = [
        'room_id'        => ['required' => 'Ruangan wajib dipilih.'],
        'nama_peminjam'  => ['required' => 'Nama peminjam wajib diisi.'],
        'keperluan'      => ['required' => 'Keperluan wajib diisi.'],
        'tanggal_mulai'  => ['required' => 'Tanggal mulai wajib diisi.'],
        'tanggal_selesai'=> ['required' => 'Tanggal selesai wajib diisi.'],
        'jam_mulai'      => ['required' => 'Jam mulai wajib diisi.'],
        'jam_selesai'    => ['required' => 'Jam selesai wajib diisi.'],
        'jumlah_peserta' => ['required' => 'Jumlah peserta wajib diisi.'],
    ];

    public function getBookingsWithRoom(): array
    {
        return $this->db->table('bookings b')
            ->select('b.*, r.nama_ruangan, r.kode_ruangan, r.lokasi, r.kapasitas')
            ->join('rooms r', 'r.id = b.room_id')
            ->orderBy('b.tanggal_mulai', 'DESC')
            ->orderBy('b.jam_mulai', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getTodayBookings(?int $userId = null): array
    {
        $today = date('Y-m-d');
        $builder = $this->db->table('bookings b')
            ->select('b.*, r.nama_ruangan, r.kode_ruangan, r.lokasi')
            ->join('rooms r', 'r.id = b.room_id')
            ->where('b.tanggal_mulai <=', $today)
            ->where('b.tanggal_selesai >=', $today)
            ->whereIn('b.status', ['pending', 'approved'])
            ->orderBy('b.jam_mulai', 'ASC');

        if ($userId) {
            $builder->where('b.user_id', $userId);
        }

        return $builder->get()->getResultArray();
    }

    public function hasConflict(int $roomId, string $tanggalMulai, string $tanggalSelesai, string $jamMulai, string $jamSelesai, int $excludeId = 0): bool
    {
        $builder = $this->db->table('bookings')
            ->where('room_id', $roomId)
            ->whereIn('status', ['pending', 'approved'])
            ->groupStart()
                ->where('tanggal_mulai <=', $tanggalSelesai)
                ->where('tanggal_selesai >=', $tanggalMulai)
            ->groupEnd()
            ->groupStart()
                ->where('jam_mulai <', $jamSelesai)
                ->where('jam_selesai >', $jamMulai)
            ->groupEnd();

        if ($excludeId > 0) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    public function getBookingById(int $id): ?array
    {
        return $this->db->table('bookings b')
            ->select('b.*, r.nama_ruangan, r.kode_ruangan, r.lokasi, r.kapasitas, r.fasilitas')
            ->join('rooms r', 'r.id = b.room_id')
            ->where('b.id', $id)
            ->get()
            ->getRowArray() ?: null;
    }

    public function getStats(?int $userId = null): array
    {
        $today = date('Y-m-d');

        $totalQ = $this->db->table('bookings');
        $pendingQ = $this->db->table('bookings')->where('status', 'pending');
        $approvedQ = $this->db->table('bookings')->where('status', 'approved');
        $todayQ = $this->db->table('bookings')
            ->where('tanggal_mulai <=', $today)
            ->where('tanggal_selesai >=', $today)
            ->whereIn('status', ['pending', 'approved']);

        if ($userId) {
            $totalQ->where('user_id', $userId);
            $pendingQ->where('user_id', $userId);
            $approvedQ->where('user_id', $userId);
            $todayQ->where('user_id', $userId);
        }

        return [
            'total'    => $totalQ->countAllResults(),
            'pending'  => $pendingQ->countAllResults(),
            'approved' => $approvedQ->countAllResults(),
            'today'    => $todayQ->countAllResults(),
        ];
    }
}
