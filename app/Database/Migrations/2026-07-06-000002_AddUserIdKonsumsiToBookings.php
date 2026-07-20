<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambahkan kolom `user_id` (relasi ke akun pembooking) dan
 * `konsumsi` (kebutuhan konsumsi saat booking) pada tabel bookings.
 */
class AddUserIdKonsumsiToBookings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('bookings', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'room_id',
            ],
            'konsumsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'jumlah_peserta',
            ],
        ]);

        $this->db->query(
            'ALTER TABLE `bookings`
             ADD CONSTRAINT `bookings_user_id_foreign`
             FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
             ON DELETE SET NULL ON UPDATE CASCADE'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `bookings` DROP FOREIGN KEY `bookings_user_id_foreign`');
        $this->forge->dropColumn('bookings', ['user_id', 'konsumsi']);
    }
}
