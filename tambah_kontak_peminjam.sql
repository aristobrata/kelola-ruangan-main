-- Jalankan file ini di phpMyAdmin pada database `kelola_ruangan`
-- jika tidak menjalankan migration CodeIgniter (php spark migrate).
--
-- Menambahkan kolom kontak (No. HP/WhatsApp) peminjam pada setiap booking.

ALTER TABLE `bookings`
  ADD COLUMN `kontak` VARCHAR(30) NULL DEFAULT NULL AFTER `nama_peminjam`;
