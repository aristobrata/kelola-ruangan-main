<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Mengubah role 'staff' menjadi 'user' karena sekarang peran ini
 * digunakan sebagai pihak yang melakukan booking (pembooking),
 * sedangkan 'admin' mengelola ruangan & menyetujui/menolak booking.
 */
class UpdateUsersRoleToUser extends Migration
{
    public function up()
    {
        // 1. Perluas enum sementara agar 'staff' & 'user' sama-sama valid
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('admin','staff','user') NOT NULL DEFAULT 'user'");

        // 2. Migrasikan data lama
        $this->db->query("UPDATE `users` SET `role` = 'user' WHERE `role` = 'staff'");

        // 3. Persempit kembali enum ke nilai final
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('admin','user') NOT NULL DEFAULT 'user'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('admin','staff','user') NOT NULL DEFAULT 'staff'");
        $this->db->query("UPDATE `users` SET `role` = 'staff' WHERE `role` = 'user'");
        $this->db->query("ALTER TABLE `users` MODIFY `role` ENUM('admin','staff') NOT NULL DEFAULT 'staff'");
    }
}
