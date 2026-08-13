<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel foto tambahan (galeri) ruangan.
 * Foto utama tetap disimpan di kolom `rooms.foto` seperti sebelumnya —
 * tabel ini hanya menyimpan foto TAMBAHAN yang bisa digeser di halaman detail.
 */
class CreateRoomPhotosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'room_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'urutan' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('room_id');
        $this->forge->addForeignKey('room_id', 'rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('room_photos');
    }

    public function down()
    {
        $this->forge->dropTable('room_photos', true);
    }
}
