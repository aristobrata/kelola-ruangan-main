<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambahkan role 'super_admin' di atas 'admin'.
 * - super_admin  : bisa membuat/mengubah akun menjadi admin (atau super_admin),
 *                  hak akses lain sama dengan admin.
 * - admin        : hanya bisa membuat/mengelola akun dengan role 'user'.
 *
 * Akun admin PALING LAMA (id terkecil) otomatis dipromosikan menjadi
 * super_admin, supaya sistem tidak pernah kehilangan akun pengelola
 * tertinggi setelah migration ini dijalankan.
 */
class AddSuperAdminRole extends Migration
{
    public function up()
    {
        // 1. Perluas enum agar 'super_admin' valid berdampingan dengan nilai lama
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('super_admin','admin','user') NOT NULL DEFAULT 'user'");

        // 2. Promosikan admin dengan id terkecil menjadi super_admin (bootstrap akun tertinggi)
        $firstAdminId = $this->db->table('users')
            ->select('id')
            ->where('role', 'admin')
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRow('id');

        if ($firstAdminId !== null) {
            $this->db->query("UPDATE `users` SET `role` = 'super_admin' WHERE `id` = ?", [$firstAdminId]);
        }
    }

    public function down()
    {
        $this->db->query("UPDATE `users` SET `role` = 'admin' WHERE `role` = 'super_admin'");
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('admin','user') NOT NULL DEFAULT 'user'");
    }
}
