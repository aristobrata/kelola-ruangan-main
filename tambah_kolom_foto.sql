-- Jalankan file ini di phpMyAdmin pada database `kelola_ruangan`
-- jika tidak menjalankan migration CodeIgniter (php spark migrate).
-- Menambahkan kolom `foto` untuk menyimpan nama file foto ruangan.

ALTER TABLE `rooms`
  ADD COLUMN `foto` VARCHAR(255) NULL DEFAULT NULL AFTER `deskripsi`;
