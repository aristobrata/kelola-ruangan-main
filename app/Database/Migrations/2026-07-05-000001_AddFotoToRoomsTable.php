<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFotoToRoomsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('rooms', [
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'deskripsi',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('rooms', 'foto');
    }
}
