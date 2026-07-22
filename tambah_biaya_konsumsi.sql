-- Jalankan file ini di phpMyAdmin pada database `kelola_ruangan`
-- jika tidak menjalankan migration CodeIgniter (php spark migrate).
--
-- Menambahkan kolom untuk konfirmasi biaya konsumsi oleh admin:
-- - biaya_konsumsi   : nominal biaya konsumsi (diisi admin, hanya saat status booking masih 'pending')
-- - penanggung_biaya : siapa yang menanggung biaya, 'pusdiklat' atau 'unit_peminjam'

ALTER TABLE `bookings`
  ADD COLUMN `biaya_konsumsi` DECIMAL(12,2) NULL DEFAULT NULL AFTER `konsumsi`,
  ADD COLUMN `penanggung_biaya` ENUM('pusdiklat','unit_peminjam') NULL DEFAULT NULL AFTER `biaya_konsumsi`;
