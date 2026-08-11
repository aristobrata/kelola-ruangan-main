<?php

namespace App\Commands;

use App\Models\BookingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Command CLI untuk membatalkan otomatis booking berstatus 'menunggu' yang
 * tanggal mulainya sudah lewat.
 *
 * Fitur ini SUDAH berjalan otomatis secara "lazy" setiap halaman Daftar Booking,
 * Dashboard, atau Pantau Jadwal dibuka — jadi command ini SIFATNYA OPSIONAL,
 * hanya diperlukan kalau ingin pembatalan terjadi tepat waktu (mis. jam 00:01)
 * walau tidak ada seorang pun yang membuka aplikasi hari itu.
 *
 * Contoh penjadwalan cron (jalan tiap hari jam 00:05):
 *   5 0 * * * php /path/ke/project/spark bookings:expire-pending
 */
class ExpireOverdueBookings extends BaseCommand
{
    protected $group       = 'Booking';
    protected $name        = 'bookings:expire-pending';
    protected $description = 'Membatalkan otomatis booking berstatus menunggu yang tanggal mulainya sudah lewat.';

    public function run(array $params)
    {
        $model = new BookingModel();
        $count = $model->expireOverduePending();

        if ($count > 0) {
            CLI::write("Selesai — {$count} booking 'menunggu' dibatalkan otomatis karena tanggal mulai sudah lewat.", 'green');
        } else {
            CLI::write('Selesai — tidak ada booking menunggu yang perlu dibatalkan.', 'yellow');
        }
    }
}
