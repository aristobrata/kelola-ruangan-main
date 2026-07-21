<?php

namespace App\Controllers;

use App\Models\RoomModel;
use App\Models\BookingModel;
use CodeIgniter\Database\BaseConnection;

class BookingController extends BaseController
{
    protected RoomModel      $roomModel;
    protected BookingModel   $bookingModel;
    protected BaseConnection $db;

    /** Pilihan kebutuhan konsumsi saat booking (bisa dipilih lebih dari satu) */
    protected array $konsumsiOptions = [
        'Snack Pagi'  => 'Snack Pagi',
        'Makan Siang' => 'Makan Siang',
        'Snack Sore'  => 'Snack Sore',
    ];

    /** Gabungkan pilihan konsumsi (checkbox) menjadi satu string untuk disimpan */
    protected function formatKonsumsi(): string
    {
        $selected = $this->request->getPost('konsumsi') ?? [];
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        $selected = array_values(array_filter(array_intersect($selected, array_keys($this->konsumsiOptions))));
        return implode(', ', $selected);
    }

    public function __construct()
    {
        $this->roomModel    = new RoomModel();
        $this->bookingModel = new BookingModel();
        $this->db           = \Config\Database::connect();
    }

    protected function isAdmin(): bool
    {
        return is_admin_role();
    }

    public function index()
    {
        $status   = $this->request->getGet('status') ?? '';
        $search   = $this->request->getGet('search') ?? '';

        $builder = $this->db->table('bookings b')
            ->select('b.*, r.nama_ruangan, r.kode_ruangan, r.lokasi')
            ->join('rooms r', 'r.id = b.room_id')
            ->orderBy('b.created_at', 'DESC');

        // User (pembooking) hanya melihat booking miliknya sendiri.
        // Admin melihat semua booking.
        if (!$this->isAdmin()) {
            $builder->where('b.user_id', session()->get('user_id'));
        }

        if ($status) {
            $builder->where('b.status', $status);
        }
        if ($search) {
            $builder->groupStart()
                ->like('b.nama_peminjam', $search)
                ->orLike('r.nama_ruangan', $search)
                ->orLike('b.keperluan', $search)
            ->groupEnd();
        }

        $bookings = $builder->get()->getResultArray();

        return view('bookings/index', [
            'title'    => 'Daftar Booking',
            'bookings' => $bookings,
            'status'   => $status,
            'search'   => $search,
        ]);
    }

    public function create()
    {
        $rooms = $this->roomModel->where('status', 'available')->findAll();
        $selectedRoom = $this->request->getGet('room_id') ?? null;

        return view('bookings/form', [
            'title'           => 'Buat Booking',
            'rooms'           => $rooms,
            'booking'         => null,
            'action'          => base_url('bookings/store'),
            'selectedRoom'    => $selectedRoom,
            'konsumsiOptions' => $this->konsumsiOptions,
        ]);
    }

    public function store()
    {
        $rules = [
            'room_id'         => 'required|integer',
            'keperluan'       => 'required',
            'tanggal_mulai'   => 'required|valid_date[Y-m-d]',
            'tanggal_selesai' => 'required|valid_date[Y-m-d]',
            'jam_mulai'       => 'required',
            'jam_selesai'     => 'required',
            'jumlah_peserta'  => 'required|integer|greater_than[0]',
        ];

        $rooms = $this->roomModel->where('status', 'available')->findAll();

        if (!$this->validate($rules)) {
            return view('bookings/form', [
                'title'           => 'Buat Booking',
                'rooms'           => $rooms,
                'booking'         => null,
                'action'          => base_url('bookings/store'),
                'validation'      => $this->validator,
                'selectedRoom'    => $this->request->getPost('room_id'),
                'konsumsiOptions' => $this->konsumsiOptions,
            ]);
        }

        $data = [
            'room_id'         => $this->request->getPost('room_id'),
            'user_id'         => session()->get('user_id'),
            'nama_peminjam'   => session()->get('nama'),
            'instansi'        => $this->request->getPost('instansi'),
            'keperluan'       => $this->request->getPost('keperluan'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'jam_mulai'       => $this->request->getPost('jam_mulai'),
            'jam_selesai'     => $this->request->getPost('jam_selesai'),
            'jumlah_peserta'  => $this->request->getPost('jumlah_peserta'),
            'konsumsi'        => $this->formatKonsumsi(),
            'catatan'         => $this->request->getPost('catatan'),
        ];

        if ($this->bookingModel->hasConflict(
            $data['room_id'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['jam_mulai'],
            $data['jam_selesai']
        )) {
            return view('bookings/form', [
                'title'           => 'Buat Booking',
                'rooms'           => $rooms,
                'booking'         => null,
                'action'          => base_url('bookings/store'),
                'selectedRoom'    => $data['room_id'],
                'konsumsiOptions' => $this->konsumsiOptions,
                'conflict_error'  => 'Ruangan sudah dibooking pada waktu tersebut. Silakan pilih waktu atau ruangan lain.',
            ]);
        }

        $room = $this->roomModel->find($data['room_id']);
        if ($room && (int)$data['jumlah_peserta'] > (int)$room['kapasitas']) {
            return view('bookings/form', [
                'title'           => 'Buat Booking',
                'rooms'           => $rooms,
                'booking'         => null,
                'action'          => base_url('bookings/store'),
                'selectedRoom'    => $data['room_id'],
                'konsumsiOptions' => $this->konsumsiOptions,
                'conflict_error'  => "Jumlah peserta melebihi kapasitas ruangan ({$room['kapasitas']} orang).",
            ]);
        }

        $this->bookingModel->insert($data);
        return redirect()->to(base_url('bookings'))->with('success', 'Booking berhasil dibuat! Menunggu konfirmasi.');
    }

    public function show(int $id)
    {
        $booking = $this->bookingModel->getBookingById($id);
        if (!$booking) {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking tidak ditemukan.');
        }

        if (!$this->isAdmin() && (int)($booking['user_id'] ?? 0) !== (int)session()->get('user_id')) {
            return redirect()->to(base_url('bookings'))->with('error', 'Anda tidak memiliki akses ke booking ini.');
        }

        return view('bookings/show', [
            'title'   => 'Detail Booking',
            'booking' => $booking,
        ]);
    }

    public function edit(int $id)
    {
        $booking = $this->bookingModel->find($id);
        if (!$booking) {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking tidak ditemukan.');
        }

        if (!$this->isAdmin() && (int)($booking['user_id'] ?? 0) !== (int)session()->get('user_id')) {
            return redirect()->to(base_url('bookings'))->with('error', 'Anda tidak memiliki akses ke booking ini.');
        }

        if (!in_array($booking['status'], ['pending'])) {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking yang sudah diproses tidak dapat diedit.');
        }

        $rooms = $this->roomModel->where('status', 'available')->findAll();

        return view('bookings/form', [
            'title'           => 'Edit Booking',
            'rooms'           => $rooms,
            'booking'         => $booking,
            'action'          => base_url("bookings/update/{$id}"),
            'selectedRoom'    => $booking['room_id'],
            'konsumsiOptions' => $this->konsumsiOptions,
        ]);
    }

    public function update(int $id)
    {
        $booking = $this->bookingModel->find($id);
        if (!$booking) {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking tidak ditemukan.');
        }

        if (!$this->isAdmin() && (int)($booking['user_id'] ?? 0) !== (int)session()->get('user_id')) {
            return redirect()->to(base_url('bookings'))->with('error', 'Anda tidak memiliki akses ke booking ini.');
        }

        $rules = [
            'room_id'         => 'required|integer',
            'keperluan'       => 'required',
            'tanggal_mulai'   => 'required|valid_date[Y-m-d]',
            'tanggal_selesai' => 'required|valid_date[Y-m-d]',
            'jam_mulai'       => 'required',
            'jam_selesai'     => 'required',
            'jumlah_peserta'  => 'required|integer|greater_than[0]',
        ];

        $rooms = $this->roomModel->where('status', 'available')->findAll();

        if (!$this->validate($rules)) {
            return view('bookings/form', [
                'title'           => 'Edit Booking',
                'rooms'           => $rooms,
                'booking'         => $booking,
                'action'          => base_url("bookings/update/{$id}"),
                'validation'      => $this->validator,
                'selectedRoom'    => $this->request->getPost('room_id'),
                'konsumsiOptions' => $this->konsumsiOptions,
            ]);
        }

        $data = [
            'room_id'         => $this->request->getPost('room_id'),
            'instansi'        => $this->request->getPost('instansi'),
            'keperluan'       => $this->request->getPost('keperluan'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'jam_mulai'       => $this->request->getPost('jam_mulai'),
            'jam_selesai'     => $this->request->getPost('jam_selesai'),
            'jumlah_peserta'  => $this->request->getPost('jumlah_peserta'),
            'konsumsi'        => $this->formatKonsumsi(),
            'catatan'         => $this->request->getPost('catatan'),
        ];

        if ($this->bookingModel->hasConflict(
            $data['room_id'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $id
        )) {
            return view('bookings/form', [
                'title'           => 'Edit Booking',
                'rooms'           => $rooms,
                'booking'         => $booking,
                'action'          => base_url("bookings/update/{$id}"),
                'selectedRoom'    => $data['room_id'],
                'konsumsiOptions' => $this->konsumsiOptions,
                'conflict_error'  => 'Ruangan sudah dibooking pada waktu tersebut.',
            ]);
        }

        $this->bookingModel->update($id, $data);
        return redirect()->to(base_url("bookings/{$id}"))->with('success', 'Booking berhasil diperbarui!');
    }

    public function approve(int $id)
    {
        $booking = $this->bookingModel->find($id);
        if (!$booking || $booking['status'] !== 'pending') {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking tidak dapat disetujui.');
        }

        $this->bookingModel->update($id, ['status' => 'approved']);
        return redirect()->to(base_url("bookings/{$id}"))->with('success', 'Booking telah disetujui!');
    }

    public function reject(int $id)
    {
        $booking = $this->bookingModel->find($id);
        if (!$booking || $booking['status'] !== 'pending') {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking tidak dapat ditolak.');
        }

        $this->bookingModel->update($id, ['status' => 'rejected']);
        return redirect()->to(base_url("bookings/{$id}"))->with('success', 'Booking telah ditolak.');
    }

    public function cancel(int $id)
    {
        $booking = $this->bookingModel->find($id);
        if (!$booking || !in_array($booking['status'], ['pending', 'approved'])) {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking tidak dapat dibatalkan.');
        }

        if (!$this->isAdmin() && (int)($booking['user_id'] ?? 0) !== (int)session()->get('user_id')) {
            return redirect()->to(base_url('bookings'))->with('error', 'Anda tidak memiliki akses ke booking ini.');
        }

        $this->bookingModel->update($id, ['status' => 'cancelled']);
        return redirect()->to(base_url("bookings/{$id}"))->with('success', 'Booking telah dibatalkan.');
    }

    public function selesai(int $id)
    {
        $booking = $this->bookingModel->find($id);
        if (!$booking || $booking['status'] !== 'approved') {
            return redirect()->to(base_url('bookings'))->with('error', 'Booking tidak dapat ditandai selesai.');
        }

        $this->bookingModel->update($id, ['status' => 'selesai']);
        return redirect()->to(base_url("bookings/{$id}"))->with('success', 'Booking ditandai selesai.');
    }
}
