-- Jalankan file ini di phpMyAdmin pada database `kelola_ruangan`
-- jika tidak menjalankan migration CodeIgniter (php spark migrate).
--
-- Tabel foto tambahan (galeri) ruangan. Foto utama tetap di kolom `rooms.foto`
-- seperti sebelumnya — tabel ini hanya untuk foto TAMBAHAN yang bisa digeser
-- di halaman Detail Ruangan.

CREATE TABLE `room_photos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` INT(11) UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `urutan` INT(5) NOT NULL DEFAULT 0,
  `created_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `room_photos_room_id_foreign` (`room_id`),
  CONSTRAINT `room_photos_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
