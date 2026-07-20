<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomModel extends Model
{
    protected $table      = 'rooms';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'kode_ruangan', 'nama_ruangan', 'kapasitas', 'lokasi',
        'fasilitas', 'deskripsi', 'status', 'foto',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'kode_ruangan' => 'required|max_length[20]',
        'nama_ruangan' => 'required|max_length[100]',
        'kapasitas'    => 'required|integer|greater_than[0]',
        'lokasi'       => 'required|max_length[200]',
        'status'       => 'required|in_list[available,maintenance]',
    ];

    protected $validationMessages = [
        'kode_ruangan' => ['required' => 'Kode ruangan wajib diisi.'],
        'nama_ruangan' => ['required' => 'Nama ruangan wajib diisi.'],
        'kapasitas'    => ['required' => 'Kapasitas wajib diisi.', 'greater_than' => 'Kapasitas harus lebih dari 0.'],
        'lokasi'       => ['required' => 'Lokasi wajib diisi.'],
    ];

    public function getRoomsWithBookingStatus(string $date = null): array
    {
        $date = $date ?? date('Y-m-d');
        $now  = date('H:i:s');

        $rooms = $this->findAll();

        foreach ($rooms as &$room) {
            if ($room['status'] === 'maintenance') {
                $room['booking_status'] = 'maintenance';
            } else {
                $booking = $this->db->table('bookings')
                    ->where('room_id', $room['id'])
                    ->where('tanggal_mulai <=', $date)
                    ->where('tanggal_selesai >=', $date)
                    ->where('jam_mulai <=', $now)
                    ->where('jam_selesai >', $now)
                    ->whereIn('status', ['approved'])
                    ->get()
                    ->getRowArray();

                $room['booking_status'] = $booking ? 'occupied' : 'available';
                $room['active_booking'] = $booking;
            }
        }

        return $rooms;
    }
}
