<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambahkan kolom untuk konfirmasi biaya konsumsi oleh admin:
 * - biaya_konsumsi   : nominal biaya konsumsi (diisi admin, hanya saat status booking masih 'pending')
 * - penanggung_biaya : siapa yang menanggung biaya, 'pusdiklat' atau 'unit_peminjam'
 */
class AddBiayaKonsumsiToBookings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('bookings', [
            'biaya_konsumsi' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'konsumsi',
            ],
            'penanggung_biaya' => [
                'type'       => 'ENUM',
                'constraint' => ['pusdiklat', 'unit_peminjam'],
                'null'       => true,
                'default'    => null,
                'after'      => 'biaya_konsumsi',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('bookings', ['biaya_konsumsi', 'penanggung_biaya']);
    }
}
