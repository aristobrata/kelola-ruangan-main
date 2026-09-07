<?php

namespace App\Controllers;

use App\Models\RoomModel;
use App\Models\BookingModel;
use App\Libraries\RoomRecommender;

class RecommendationController extends BaseController
{
    protected RoomModel    $roomModel;
    protected BookingModel $bookingModel;

    public function __construct()
    {
        $this->roomModel    = new RoomModel();
        $this->bookingModel = new BookingModel();
    }

    public function index()
    {
        $allRooms = $this->roomModel->orderBy('nama_ruangan', 'ASC')->findAll();

        // Kumpulkan daftar fasilitas unik dari seluruh ruangan untuk pilihan checklist
        $facilitySet = [];
        foreach ($allRooms as $r) {
            foreach (explode(',', (string) $r['fasilitas']) as $f) {
                $f = trim($f);
                if ($f !== '') {
                    $facilitySet[$f] = true;
                }
            }
        }
        $availableFacilities = array_keys($facilitySet);
        sort($availableFacilities);

        $jumlahPeserta    = $this->request->getGet('jumlah_peserta');
        $tanggalMulai     = $this->request->getGet('tanggal_mulai');
        $tanggalSelesai   = $this->request->getGet('tanggal_selesai');
        $jamMulai         = $this->request->getGet('jam_mulai');
        $jamSelesai       = $this->request->getGet('jam_selesai');
        $fasilitasDiminta = $this->request->getGet('fasilitas') ?? [];
        if (!is_array($fasilitasDiminta)) {
            $fasilitasDiminta = [];
        }

        $results  = [];
        $searched = false;
        $errorMsg = '';
        $recommender = new RoomRecommender();

        if ($jumlahPeserta && $tanggalMulai && $tanggalSelesai && $jamMulai && $jamSelesai) {
            $searched = true;
            $jumlahPesertaInt = (int) $jumlahPeserta;

            if ($tanggalSelesai < $tanggalMulai) {
                $errorMsg = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
            } elseif ($jamSelesai <= $jamMulai) {
                $errorMsg = 'Jam selesai harus lebih besar dari jam mulai.';
            } elseif ($jumlahPesertaInt < 1) {
                $errorMsg = 'Jumlah peserta minimal 1 orang.';
            } else {
                // ── Filter keras: status tersedia, kapasitas cukup, tidak bentrok jadwal ──
                $candidates = [];
                foreach ($allRooms as $r) {
                    if ($r['status'] === 'maintenance') {
                        continue;
                    }
                    if ((int) $r['kapasitas'] < $jumlahPesertaInt) {
                        continue;
                    }
                    if ($this->bookingModel->hasConflict((int) $r['id'], $tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai)) {
                        continue;
                    }

                    $r['jumlah_booking'] = $this->bookingModel
                        ->where('room_id', $r['id'])
                        ->whereIn('status', ['pending', 'approved', 'selesai'])
                        ->where('tanggal_mulai >=', date('Y-m-d', strtotime('-30 days')))
                        ->countAllResults();

                    $candidates[] = $r;
                }

                if (empty($candidates)) {
                    $errorMsg = 'Tidak ada ruangan yang tersedia & memenuhi kapasitas untuk kriteria yang dipilih. Coba longgarkan jam/tanggal atau kurangi jumlah peserta.';
                } else {
                    $results = $recommender->rank($candidates, $jumlahPesertaInt, $fasilitasDiminta);
                }
            }
        }

        return view('recommendation/index', [
            'title'               => 'Rekomendasi Ruangan',
            'availableFacilities' => $availableFacilities,
            'jumlahPeserta'       => $jumlahPeserta,
            'tanggalMulai'        => $tanggalMulai,
            'tanggalSelesai'      => $tanggalSelesai,
            'jamMulai'            => $jamMulai,
            'jamSelesai'          => $jamSelesai,
            'fasilitasDiminta'    => $fasilitasDiminta,
            'results'             => $results,
            'searched'            => $searched,
            'errorMsg'            => $errorMsg,
            'weights'             => $recommender->getWeights(),
        ]);
    }
}
