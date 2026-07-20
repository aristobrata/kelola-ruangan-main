<?php

namespace App\Controllers;

use App\Models\RoomModel;
use CodeIgniter\Database\BaseConnection;

class RoomController extends BaseController
{
    protected RoomModel      $roomModel;
    protected BaseConnection $db;

    /** Folder fisik penyimpanan foto ruangan (public, agar bisa diakses langsung via URL) */
    protected string $uploadPath = FCPATH . 'uploads/rooms/';

    public function __construct()
    {
        $this->roomModel = new RoomModel();
        $this->db        = \Config\Database::connect();
    }

    /**
     * Memvalidasi & memindahkan file foto yang diunggah.
     * Mengembalikan nama file baru, atau null jika tidak ada file yang diunggah.
     * Melempar pesan error via session flashdata + return false jika file tidak valid.
     */
    protected function handleFotoUpload(string $fieldName = 'foto')
    {
        $file = $this->request->getFile($fieldName);

        if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null; // tidak ada file baru diunggah
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            return ['error' => 'Format foto harus JPG, PNG, atau WEBP.'];
        }

        if ($file->getSize() > 2048 * 1024) {
            return ['error' => 'Ukuran foto maksimal 2 MB.'];
        }

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($this->uploadPath, $newName);

        return ['filename' => $newName];
    }

    protected function deleteFotoFile(?string $filename): void
    {
        if ($filename && is_file($this->uploadPath . $filename)) {
            @unlink($this->uploadPath . $filename);
        }
    }

    public function index()
    {
        $rooms = $this->roomModel->getRoomsWithBookingStatus();
        return view('rooms/index', [
            'title' => 'Manajemen Ruangan',
            'rooms' => $rooms,
        ]);
    }

    public function create()
    {
        return view('rooms/form', [
            'title'  => 'Tambah Ruangan',
            'room'   => null,
            'action' => base_url('rooms/store'),
        ]);
    }

    public function store()
    {
        $rules = [
            'kode_ruangan' => 'required|max_length[20]|is_unique[rooms.kode_ruangan]',
            'nama_ruangan' => 'required|max_length[100]',
            'kapasitas'    => 'required|integer|greater_than[0]',
            'lokasi'       => 'required|max_length[200]',
            'status'       => 'required|in_list[available,maintenance]',
        ];

        if (!$this->validate($rules)) {
            return view('rooms/form', [
                'title'      => 'Tambah Ruangan',
                'room'       => null,
                'action'     => base_url('rooms/store'),
                'validation' => $this->validator,
            ]);
        }

        $foto  = null;
        $upload = $this->handleFotoUpload('foto');
        if (is_array($upload) && isset($upload['error'])) {
            return view('rooms/form', [
                'title'  => 'Tambah Ruangan',
                'room'   => null,
                'action' => base_url('rooms/store'),
                'error'  => $upload['error'],
            ]);
        }
        if (is_array($upload) && isset($upload['filename'])) {
            $foto = $upload['filename'];
        }

        $this->roomModel->insert([
            'kode_ruangan' => $this->request->getPost('kode_ruangan'),
            'nama_ruangan' => $this->request->getPost('nama_ruangan'),
            'kapasitas'    => $this->request->getPost('kapasitas'),
            'lokasi'       => $this->request->getPost('lokasi'),
            'fasilitas'    => $this->request->getPost('fasilitas'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'status'       => $this->request->getPost('status'),
            'foto'         => $foto,
        ]);

        return redirect()->to(base_url('rooms'))->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function edit(int $id)
    {
        $room = $this->roomModel->find($id);
        if (!$room) {
            return redirect()->to(base_url('rooms'))->with('error', 'Ruangan tidak ditemukan.');
        }

        return view('rooms/form', [
            'title'  => 'Edit Ruangan',
            'room'   => $room,
            'action' => base_url("rooms/update/{$id}"),
        ]);
    }

    public function update(int $id)
    {
        $room = $this->roomModel->find($id);
        if (!$room) {
            return redirect()->to(base_url('rooms'))->with('error', 'Ruangan tidak ditemukan.');
        }

        $rules = [
            'kode_ruangan' => "required|max_length[20]|is_unique[rooms.kode_ruangan,id,{$id}]",
            'nama_ruangan' => 'required|max_length[100]',
            'kapasitas'    => 'required|integer|greater_than[0]',
            'lokasi'       => 'required|max_length[200]',
            'status'       => 'required|in_list[available,maintenance]',
        ];

        if (!$this->validate($rules)) {
            return view('rooms/form', [
                'title'      => 'Edit Ruangan',
                'room'       => $room,
                'action'     => base_url("rooms/update/{$id}"),
                'validation' => $this->validator,
            ]);
        }

        $foto  = $room['foto'] ?? null; // pertahankan foto lama secara default
        $upload = $this->handleFotoUpload('foto');
        if (is_array($upload) && isset($upload['error'])) {
            return view('rooms/form', [
                'title'  => 'Edit Ruangan',
                'room'   => $room,
                'action' => base_url("rooms/update/{$id}"),
                'error'  => $upload['error'],
            ]);
        }
        if (is_array($upload) && isset($upload['filename'])) {
            $this->deleteFotoFile($room['foto'] ?? null); // hapus foto lama jika diganti
            $foto = $upload['filename'];
        }

        // Hapus foto jika pengguna mencentang "hapus foto"
        if ($this->request->getPost('hapus_foto') === '1') {
            $this->deleteFotoFile($room['foto'] ?? null);
            $foto = null;
        }

        $this->roomModel->update($id, [
            'kode_ruangan' => $this->request->getPost('kode_ruangan'),
            'nama_ruangan' => $this->request->getPost('nama_ruangan'),
            'kapasitas'    => $this->request->getPost('kapasitas'),
            'lokasi'       => $this->request->getPost('lokasi'),
            'fasilitas'    => $this->request->getPost('fasilitas'),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'status'       => $this->request->getPost('status'),
            'foto'         => $foto,
        ]);

        return redirect()->to(base_url('rooms'))->with('success', 'Ruangan berhasil diperbarui!');
    }

    public function delete(int $id)
    {
        $room = $this->roomModel->find($id);
        if (!$room) {
            return redirect()->to(base_url('rooms'))->with('error', 'Ruangan tidak ditemukan.');
        }

        $activeBookings = $this->db->table('bookings')
            ->where('room_id', $id)
            ->whereIn('status', ['pending', 'approved'])
            ->countAllResults();

        if ($activeBookings > 0) {
            return redirect()->to(base_url('rooms'))->with('error', 'Tidak dapat menghapus ruangan yang masih memiliki booking aktif.');
        }

        $this->deleteFotoFile($room['foto'] ?? null);
        $this->roomModel->delete($id);
        return redirect()->to(base_url('rooms'))->with('success', 'Ruangan berhasil dihapus!');
    }

    public function show(int $id)
    {
        $room = $this->roomModel->find($id);
        if (!$room) {
            return redirect()->to(base_url('rooms'))->with('error', 'Ruangan tidak ditemukan.');
        }

        $bookings = $this->db->table('bookings')
            ->where('room_id', $id)
            ->orderBy('tanggal_mulai', 'DESC')
            ->orderBy('jam_mulai', 'DESC')
            ->get()
            ->getResultArray();

        return view('rooms/show', [
            'title'    => 'Detail Ruangan',
            'room'     => $room,
            'bookings' => $bookings,
        ]);
    }
}
