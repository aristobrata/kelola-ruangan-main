-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Jul 2026 pada 04.07
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kelola_ruangan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) UNSIGNED NOT NULL,
  `room_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `nama_peminjam` varchar(100) NOT NULL,
  `instansi` varchar(200) DEFAULT NULL,
  `keperluan` text NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `jumlah_peserta` int(5) NOT NULL DEFAULT 1,
  `konsumsi` varchar(50) DEFAULT NULL,
  `biaya_konsumsi` decimal(12,2) DEFAULT NULL,
  `penanggung_biaya` enum('pusdiklat','unit_peminjam') DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','selesai') NOT NULL DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `room_id`, `user_id`, `nama_peminjam`, `instansi`, `keperluan`, `tanggal_mulai`, `tanggal_selesai`, `jam_mulai`, `jam_selesai`, `jumlah_peserta`, `konsumsi`, `biaya_konsumsi`, `penanggung_biaya`, `status`, `catatan`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 'ariii', 'devisi sdm', 'seminar', '2026-07-08', '2026-07-08', '08:30:00', '17:30:00', 1, NULL, NULL, NULL, 'cancelled', '', '2026-07-06 13:23:55', '2026-07-06 13:25:16'),
(2, 1, 4, 'aristo', 'devisi sdm', 'seminar', '2026-07-07', '2026-07-08', '07:40:00', '16:40:00', 28, 'Coffee Break', NULL, NULL, 'selesai', 'kursi tersusun', '2026-07-07 10:41:07', '2026-07-07 14:57:16'),
(3, 1, 5, 'pak beni', 'diklat', 'rapat', '2026-07-21', '2026-07-21', '08:00:00', '17:00:00', 24, '', NULL, NULL, 'cancelled', '', '2026-07-20 15:32:49', '2026-07-20 15:42:49'),
(4, 1, 3, 'user', 'sdm', 'rapat', '2026-07-21', '2026-07-21', '08:00:00', '17:00:00', 1, '', NULL, NULL, 'approved', '', '2026-07-20 15:44:21', '2026-07-20 22:24:20'),
(5, 2, 3, 'user', 'sdm', 'rapat', '2026-07-21', '2026-07-21', '08:00:00', '17:00:00', 30, 'Snack Pagi, Makan Siang', NULL, NULL, 'approved', '', '2026-07-20 22:16:20', '2026-07-20 22:24:12'),
(6, 6, 3, 'user', 'devisi sdm', 'rapat', '2026-07-22', '2026-07-22', '08:00:00', '17:00:00', 12, '', NULL, NULL, 'approved', '', '2026-07-21 15:12:42', '2026-07-21 15:57:56'),
(7, 7, 3, 'ucup', 'listirk', 'pelatihan', '2026-07-22', '2026-07-23', '08:00:00', '17:00:00', 11, '', NULL, NULL, 'rejected', '', '2026-07-21 15:32:03', '2026-07-21 15:34:31'),
(8, 1, 3, 'haris', 'listirk', 'rapat', '2026-07-24', '2026-07-24', '08:00:00', '17:00:00', 1, 'Snack Pagi, Makan Siang', NULL, NULL, 'approved', '', '2026-07-21 15:34:00', '2026-07-21 15:34:37'),
(9, 7, 3, 'aree', 'semen', 'rapat', '2026-07-31', '2026-07-31', '08:00:00', '17:00:00', 11, 'Snack Pagi', NULL, NULL, 'approved', '', '2026-07-21 15:40:19', '2026-07-21 15:40:45'),
(10, 3, 3, 'julian', 'pemasaran', 'pelatihan', '2026-07-24', '2026-07-24', '08:00:00', '17:00:00', 8, 'Snack Pagi, Makan Siang', NULL, NULL, 'approved', '', '2026-07-21 15:59:56', '2026-07-21 16:00:18'),
(11, 4, 3, 'asep', 'mesin', 'pelatihan', '2026-07-23', '2026-07-23', '08:00:00', '17:00:00', 199, 'Snack Pagi, Makan Siang', NULL, NULL, 'approved', '', '2026-07-22 08:19:48', '2026-07-22 08:21:16'),
(12, 4, 3, 'joy', 'listirk', 'rapat', '2026-07-31', '2026-08-01', '08:00:00', '17:00:00', 149, 'Snack Pagi, Makan Siang, Snack Sore', NULL, 'unit_peminjam', 'approved', '', '2026-07-22 08:24:04', '2026-07-22 08:24:40'),
(13, 2, 3, 'akbar', 'pabrik', 'pelatihan', '2026-07-26', '2026-07-26', '08:00:00', '17:00:00', 79, 'Snack Pagi, Makan Siang, Snack Sore', NULL, 'pusdiklat', 'approved', '', '2026-07-22 09:31:23', '2026-07-22 09:57:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2024-01-01-000001', 'App\\Database\\Migrations\\CreateRoomsTable', 'default', 'App', 1782964844, 1),
(2, '2024-01-01-000002', 'App\\Database\\Migrations\\CreateBookingsTable', 'default', 'App', 1782964844, 1),
(3, '2026-04-20-053252', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1782964844, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) UNSIGNED NOT NULL,
  `kode_ruangan` varchar(20) NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `kapasitas` int(5) NOT NULL DEFAULT 0,
  `lokasi` varchar(200) NOT NULL,
  `fasilitas` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('available','maintenance') NOT NULL DEFAULT 'available',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rooms`
--

INSERT INTO `rooms` (`id`, `kode_ruangan`, `nama_ruangan`, `kapasitas`, `lokasi`, `fasilitas`, `deskripsi`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 'R-001', 'Diklat Lokal A', 30, 'Gedung Diklat', 'Proyektor, AC, Whiteboard, Sound System, WiFi', 'Ruang rapat utama dengan kapasitas besar, cocok untuk rapat besar dan presentasi.', '1783238916_d9a64559d5b310c23efe.png', 'available', '2026-07-02 04:01:02', '2026-07-06 14:32:52'),
(2, 'R-002', 'Diklat Lokal B', 100, 'Gedung Diklat', 'Proyektor, AC, Microphone, Sound System, WiFi, Podium', 'Ruang seminar berkapasitas besar untuk acara seminar dan workshop.', '1783239066_b9445a363f3df1fbf580.png', 'available', '2026-07-02 04:01:02', '2026-07-06 14:33:25'),
(3, 'R-003', 'Diklat Lokal C', 10, 'Gedung Diklat', 'Whiteboard, AC, TV, WiFi', 'Ruang diskusi kecil untuk meeting tim atau diskusi kelompok kecil.', '1783239259_dca301e584c8172e25c3.png', 'available', '2026-07-02 04:01:02', '2026-07-20 08:56:16'),
(4, 'R-004', 'Diklat lokal D', 200, 'Gedung Diklat', 'Sound System, Proyektor, Microphone, AC, Panggung, WiFi', 'Aula serbaguna untuk acara besar seperti konferensi, wisuda, dan acara resmi.', '1783239506_b06ff9cb8f24b9e3509f.png', 'available', '2026-07-02 04:01:02', '2026-07-20 08:56:33'),
(5, 'R-005', 'Aula Diklat', 25, 'Gedung Diklat', 'Komputer, Proyektor, AC, Whiteboard, WiFi', 'Ruang training dengan fasilitas komputer untuk pelatihan dan workshop IT.', '1783240343_a84d8d72c76679c733f9.png', 'maintenance', '2026-07-02 04:01:02', '2026-07-20 08:56:48'),
(6, 'R-006', 'Rumah KM Expert 1', 20, 'Gedung Diklat', 'Smart TV, AC, Kamera Vicon', '', NULL, 'available', '2026-07-20 15:52:34', '2026-07-20 15:53:09'),
(7, 'R-007', 'Ruang Komunitas (Lesehan)', 20, 'Gedung Diklat', 'Proyektor', '', NULL, 'available', '2026-07-20 15:54:49', '2026-07-20 15:54:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `instansi` varchar(200) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','user') NOT NULL DEFAULT 'user',
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `instansi`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', NULL, 'admin', NULL, '$2y$10$Ue/GqiLHgFl.r9cDEc9J6OfqnnH18OcL2KMq8BuyPTmeo1huQvM1K', 'super_admin', 'aktif', '2026-07-02 04:01:01', '2026-07-02 04:01:01'),
(2, 'Staff LPD', NULL, 'staff', NULL, '$2y$10$6R3ErVmSMLi1Tt9jJaZA6eyR.szYsjyR6mfDLI2aGn22s3av6lPfC', 'user', 'aktif', '2026-07-02 04:01:01', '2026-07-02 04:01:01'),
(3, 'user', NULL, 'user', NULL, '$2y$10$S2w48hgXVvC42mfmTCu2AOAAPveeXqpIg9Frh6vD.hcQw5Sw4/xsu', 'user', 'aktif', NULL, '2026-07-22 14:08:09'),
(4, 'aristo', NULL, 'aristo', NULL, '$2y$10$Cbd35sLw8GzN1xvmtFrCvOIK9hKdPGeYpDQjjuVlhSEeREZbB8FdW', 'user', 'aktif', '2026-07-07 10:38:31', '2026-07-07 10:38:31'),
(5, 'pak beni', NULL, '345beni', NULL, '$2y$10$q7Q1c8Q3/ZqnN6XS3vSTl.u8A26plsFfySeFcEx5jaE8gVGoRWlWm', 'admin', 'aktif', '2026-07-20 15:11:46', '2026-07-21 09:39:51');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bookings_room_id_foreign` (`room_id`),
  ADD KEY `bookings_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_ruangan` (`kode_ruangan`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
