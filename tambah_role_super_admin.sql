-- Jalankan file ini di phpMyAdmin pada database `kelola_ruangan`
-- jika tidak menjalankan migration CodeIgniter (php spark migrate).
--
-- Menambahkan role 'super_admin' di atas 'admin'.
-- - super_admin : bisa membuat/mengubah akun menjadi admin (atau super_admin),
--                 hak akses lain sama dengan admin.
-- - admin       : hanya bisa membuat/mengelola akun dengan role 'user'.
--
-- Akun admin dengan id TERKECIL otomatis dipromosikan menjadi super_admin,
-- supaya sistem tidak pernah kehilangan akun pengelola tertinggi.

-- 1. Perluas enum agar 'super_admin' valid berdampingan dengan nilai lama
ALTER TABLE `users` MODIFY `role` ENUM('super_admin','admin','user') NOT NULL DEFAULT 'user';

-- 2. Promosikan admin dengan id terkecil menjadi super_admin
UPDATE `users`
SET `role` = 'super_admin'
WHERE `id` = (SELECT id FROM (SELECT MIN(id) AS id FROM `users` WHERE `role` = 'admin') AS t);
