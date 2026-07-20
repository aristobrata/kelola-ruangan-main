<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBookingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'room_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_peminjam' => ['type' => 'VARCHAR', 'constraint' => 100],
            'instansi'      => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'keperluan'     => ['type' => 'TEXT'],
            'tanggal_mulai' => ['type' => 'DATE'],
            'tanggal_selesai'=> ['type' => 'DATE'],
            'jam_mulai'     => ['type' => 'TIME'],
            'jam_selesai'   => ['type' => 'TIME'],
            'jumlah_peserta'=> ['type' => 'INT', 'constraint' => 5, 'default' => 1],
            'status'        => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected', 'cancelled', 'selesai'], 'default' => 'pending'],
            'catatan'       => ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('room_id', 'rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bookings');
    }

    public function down()
    {
        $this->forge->dropTable('bookings');
    }
}
