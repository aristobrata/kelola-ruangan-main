<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambahkan kolom kontak (No. HP/WhatsApp) peminjam pada setiap booking,
 * supaya admin dapat menghubungi peminjam terkait pengajuannya.
 */
class AddKontakToBookings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('bookings', [
            'kontak' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
                'after'      => 'nama_peminjam',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('bookings', 'kontak');
    }
}
