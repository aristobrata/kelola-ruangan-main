<?php

namespace App\Commands;

use App\Models\BookingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Command CLI untuk pemeliharaan status booking secara otomatis:
 *   1. Membatalkan booking berstatus 'menunggu' yang tanggal mulainya sudah lewat.
 *   2. Menandai 'selesai' booking berstatus 'disetujui' begitu jadwal pelaksanaan
 *      agendanya sudah terlewat — berlaku untuk semua booking, apapun penanggung
 *      biaya konsumsinya (Pusdiklat maupun Unit Peminjam).
 *
 * Kedua logic ini SUDAH berjalan otomatis secara "lazy" setiap halaman Daftar
 * Booking, Dashboard, atau Pantau Jadwal dibuka — jadi command ini SIFATNYA
 * OPSIONAL, hanya diperlukan kalau ingin perubahan status terjadi tepat waktu
 * (mis. jam 00:01) walau tidak ada seorang pun yang membuka aplikasi hari itu.
 *
 * Contoh penjadwalan cron (jalan tiap hari jam 00:05):
 *   5 0 * * * php /path/ke/project/spark bookings:expire-pending
 */
class ExpireOverdueBookings extends BaseCommand
{
    protected $group       = 'Booking';
    protected $name        = 'bookings:expire-pending';
    protected $description = 'Membatalkan booking menunggu yang tanggal mulainya lewat & menandai selesai booking disetujui yang jadwalnya lewat.';

    public function run(array $params)
    {
        $model = new BookingModel();

        $expired = $model->expireOverduePending();
        if ($expired > 0) {
            CLI::write("Selesai — {$expired} booking 'menunggu' dibatalkan otomatis karena tanggal mulai sudah lewat.", 'green');
        } else {
            CLI::write('Tidak ada booking menunggu yang perlu dibatalkan.', 'yellow');
        }

        $completed = $model->completeOverdueApprovedBookings();
        if ($completed > 0) {
            CLI::write("Selesai — {$completed} booking disetujui ditandai selesai otomatis karena jadwal agenda sudah lewat.", 'green');
        } else {
            CLI::write('Tidak ada booking disetujui yang perlu ditandai selesai.', 'yellow');
        }
    }
}
