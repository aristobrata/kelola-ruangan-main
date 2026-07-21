<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('users')->insertBatch([
            [
                'nama'       => 'Administrator',
                'username'   => 'admin',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'role'       => 'super_admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nama'       => 'Pegawai LPD',
                'username'   => 'user',
                'password'   => password_hash('user123', PASSWORD_DEFAULT),
                'role'       => 'user',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
