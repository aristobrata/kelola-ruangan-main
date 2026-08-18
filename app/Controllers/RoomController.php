<?php

namespace App\Controllers;

use App\Models\RoomModel;
use App\Models\RoomPhotoModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;

class RoomController extends BaseController
{
    protected RoomModel      $roomModel;
    protected RoomPhotoModel $roomPhotoModel;
    protected BaseConnection $db;

    /** Folder fisik penyimpanan foto ruangan (public, agar bisa diakses langsung via URL) */
    protected string $uploadPath = FCPATH . 'uploads/rooms/';

    /** Maksimal jumlah foto tambahan (galeri) per ruangan */
    protected int $maxGalleryPhotos = 8;

    public function __construct()
    {
        $this->roomModel      = new RoomModel();
        $this->roomPhotoModel = new RoomPhotoModel();
        $this->db             = \Config\Database::connect();
    }

    /**
     * Validasi & pindahkan satu file foto yang sudah diunggah.
     * Mengembalikan ['filename' => ...] jika berhasil, atau ['error' => ...] jika gagal.
     */
    protected function processUploadedFile(UploadedFile $file): array
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            return ['error' => 'Format foto harus JPG, PNG, atau WEBP.'];
        }

        if ($file->getSize() > 2048 * 1024) {
            return ['error' => 'Ukuran foto maksimal 2 MB per file.'];
        }

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        $newName = $file->getRandomName();
        $file->move($this->uploadPath, $newName);

        return ['filename' => $newName];
    }

    /**
     * Memvalidasi & memindahkan file foto UTAMA yang diunggah.
     * Mengembalikan nama file baru, atau null jika tidak ada file yang diunggah.
     * Melempar pesan error via session flashdata + return false jika file tidak valid.
     */
    protected function handleFotoUpload(string $fieldName = 'foto')
    {
        $file = $this->request->getFile($fieldName);

        if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return null; // tidak ada file baru diunggah
        }

        return $this->processUploadedFile($file);
    }

    /**
     * Memvalidasi & memindahkan beberapa file foto TAMBAHAN (galeri) sekaligus.
     * Mengembalikan ['filenames' => [...]] jika semua berhasil, atau ['error' => ...]
     * pada kegagalan pertama (file yang sudah sempat dipindah sebelum error akan dihapus lagi).
     */
    protected function handleGalleryUpload(string $fieldName = 'foto_tambahan'): array
    {
        $files = $this->request->getFileMultiple($fieldName) ?? [];
        $moved = [];

        foreach ($files as $file) {
            if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $result = $this->processUploadedFile($file);
            if (isset($result['error'])) {
                foreach ($moved as $fn) {
                    $this->deleteFotoFile($fn);
                }
                return ['error' => $result['error']];
            }
            $moved[] = $result['filename'];
        }

        return ['filenames' => $moved];
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

        $filter = $this->request->getGet('filter');
        if (in_array($filter, ['available', 'occupied', 'maintenance'], true)) {
            $rooms = array_values(array_filter($rooms, fn ($r) => $r['booking_status'] === $filter));
        } else {
            $filter = '';
        }

        return view('rooms/index', [
            'title'  => 'Manajemen Ruangan',
            'rooms'  => $rooms,
            'filter' => $filter,
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
        $roomId = $this->roomModel->getInsertID();

        $gallery = $this->handleGalleryUpload('foto_tambahan');
        if (isset($gallery['filenames'])) {
            $toInsert = array_slice($gallery['filenames'], 0, $this->maxGalleryPhotos);
            foreach ($toInsert as $i => $fn) {
                $this->roomPhotoModel->insert(['room_id' => $roomId, 'filename' => $fn, 'urutan' => $i]);
            }
            foreach (array_slice($gallery['filenames'], $this->maxGalleryPhotos) as $fn) {
                $this->deleteFotoFile($fn); // buang file yang melebihi batas maksimal
            }
        }

        if (isset($gallery['error'])) {
            return redirect()->to(base_url('rooms'))
                ->with('success', 'Ruangan berhasil ditambahkan!')
                ->with('error', 'Namun foto tambahan gagal diunggah: ' . $gallery['error']);
        }

        return redirect()->to(base_url('rooms'))->with('success', 'Ruangan berhasil ditambahkan!');
    }

    public function edit(int $id)
    {
        $room = $this->roomModel->find($id);
        if (!$room) {
            return redirect()->to(base_url('rooms'))->with('error', 'Ruangan tidak ditemukan.');
        }

        return view('rooms/form', [
            'title'      => 'Edit Ruangan',
            'room'       => $room,
            'roomPhotos' => $this->roomPhotoModel->getForRoom($id),
            'action'     => base_url("rooms/update/{$id}"),
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
                'roomPhotos' => $this->roomPhotoModel->getForRoom($id),
                'action'     => base_url("rooms/update/{$id}"),
                'validation' => $this->validator,
            ]);
        }

        $foto  = $room['foto'] ?? null; // pertahankan foto lama secara default
        $upload = $this->handleFotoUpload('foto');
        if (is_array($upload) && isset($upload['error'])) {
            return view('rooms/form', [
                'title'      => 'Edit Ruangan',
                'room'       => $room,
                'roomPhotos' => $this->roomPhotoModel->getForRoom($id),
                'action'     => base_url("rooms/update/{$id}"),
                'error'      => $upload['error'],
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

        // Tambahkan foto galeri baru (jika ada) — foto lama yang sudah tersimpan tidak terpengaruh
        $existingCount = count($this->roomPhotoModel->getForRoom($id));
        $remainingSlots = max($this->maxGalleryPhotos - $existingCount, 0);
        $gallery = $this->handleGalleryUpload('foto_tambahan');
        $galleryLimitNote = '';
        if (isset($gallery['filenames'])) {
            $toInsert = array_slice($gallery['filenames'], 0, $remainingSlots);
            foreach ($toInsert as $i => $fn) {
                $this->roomPhotoModel->insert(['room_id' => $id, 'filename' => $fn, 'urutan' => $existingCount + $i]);
            }
            $skipped = array_slice($gallery['filenames'], $remainingSlots);
            foreach ($skipped as $fn) {
                $this->deleteFotoFile($fn); // buang file yang melebihi batas maksimal
            }
            if (count($skipped) > 0) {
                $galleryLimitNote = count($skipped) . ' foto tidak disimpan karena galeri sudah mencapai batas maksimal ' . $this->maxGalleryPhotos . ' foto.';
            }
        }
        if (isset($gallery['error'])) {
            return redirect()->to(base_url('rooms'))
                ->with('success', 'Ruangan berhasil diperbarui!')
                ->with('error', 'Namun foto tambahan gagal diunggah: ' . $gallery['error']);
        }
        if ($galleryLimitNote !== '') {
            return redirect()->to(base_url('rooms'))
                ->with('success', 'Ruangan berhasil diperbarui!')
                ->with('error', $galleryLimitNote);
        }

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
        foreach ($this->roomPhotoModel->getForRoom($id) as $p) {
            $this->deleteFotoFile($p['filename']);
        }
        $this->roomPhotoModel->where('room_id', $id)->delete();
        $this->roomModel->delete($id);
        return redirect()->to(base_url('rooms'))->with('success', 'Ruangan berhasil dihapus!');
    }

    /** Hapus satu foto galeri (bukan foto utama) milik sebuah ruangan. */
    public function deletePhoto(int $photoId)
    {
        $photo = $this->roomPhotoModel->find($photoId);
        if (!$photo) {
            return redirect()->back()->with('error', 'Foto tidak ditemukan.');
        }

        $roomId = $photo['room_id'];
        $this->deleteFotoFile($photo['filename']);
        $this->roomPhotoModel->delete($photoId);

        return redirect()->to(base_url("rooms/edit/{$roomId}"))->with('success', 'Foto galeri berhasil dihapus.');
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
            'title'      => 'Detail Ruangan',
            'room'       => $room,
            'roomPhotos' => $this->roomPhotoModel->getForRoom($id),
            'bookings'   => $bookings,
        ]);
    }
}
