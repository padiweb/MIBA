-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 08, 2025 at 03:00 PM
-- Server version: 10.11.14-MariaDB-cll-lve
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rins8265_mutabaahsantri`
--

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `nama_kegiatan` varchar(100) NOT NULL,
  `kategori` enum('ibadah_wajib','ibadah_sunnah','kegiatan_sosial') NOT NULL,
  `poin` int(11) DEFAULT 1,
  `ada_udzur` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `nama_kegiatan`, `kategori`, `poin`, `ada_udzur`) VALUES
(1, 'Sholat Subuh', 'ibadah_wajib', 10, 1),
(2, 'Sholat Dzuhur', 'ibadah_wajib', 10, 1),
(3, 'Sholat Dhuha', 'ibadah_sunnah', 5, 1),
(4, 'Membantu Orang Tua', 'kegiatan_sosial', 5, 0),
(5, 'Sholat Ashar', 'ibadah_wajib', 10, 1),
(6, 'Sholat Maghrib', 'ibadah_wajib', 10, 1),
(7, 'Sholat Isya', 'ibadah_wajib', 10, 1),
(8, 'Membaca Al-Quran', 'ibadah_wajib', 5, 0),
(9, 'Dzikir Pagi', 'ibadah_sunnah', 5, 0),
(10, 'Dzikir Petang', 'ibadah_sunnah', 5, 0),
(11, 'Murojaah Hafalan', 'ibadah_wajib', 10, 0);

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`) VALUES
(1, '10 A'),
(2, '10 B'),
(3, '11 A'),
(4, '11 B');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kegiatan_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_jamaah` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `nama_sekolah` varchar(100) DEFAULT 'Pondok Pesantren Al-Hidayah',
  `nama_kegiatan` varchar(100) DEFAULT 'Mutaba''ah Liburan Semester Ganjil'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `tgl_mulai`, `tgl_selesai`, `nama_sekolah`, `nama_kegiatan`) VALUES
(1, '2025-12-07', '2026-01-16', 'MTQ Ibnu Abbas', 'Mutaba\'ah Liburan Semester Genap');

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id` int(11) NOT NULL,
  `pengirim_id` int(11) NOT NULL,
  `penerima_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT 'L',
  `role` enum('superadmin','guru','santri') NOT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `no_hp_ortu` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `jenis_kelamin`, `role`, `kelas_id`, `no_hp_ortu`) VALUES
(1, 'admin', '$2y$10$LSNH.m9fPjBhN79lGH2E3ueihirxgiSNacPXAXRvrLJkGqj74HtG.', 'Super Admin', 'L', 'superadmin', NULL, NULL),
(2, 'guru1', '$2y$10$PwBLQ9/ZV6va9r6DmZsT2Omrgt7/3IyHJydRLy/wsc.PFUwx1scMG', 'Ustadz Ahmad', 'L', 'guru', 2, NULL),
(3, 'santri1', '$2y$10$TRoGlH/jju6yabl2nfZes..XMfg3nAoaweSBTd2M8eKTIMeDYK7qe', 'Fulan bin Fulan', 'L', 'santri', 1, '6289652994041'),
(4, 'indah', '$2y$10$uC/1/ISruyW213ljk9MqQuCWu9Jn.palYItawp9w6VBNV/RLpuBSm', 'Indah', 'L', 'guru', 1, NULL),
(5, 'budi', '$2y$10$Y7k3gDjXq6BJmIoqvDU2dekP.83hyDOamLkdT2v2yZNkOWIvuxJ9G', 'Budi', 'L', 'santri', 1, '6281283701940'),
(6, 'rina', '$2y$10$xnYGLZeLkhIJkYU6vvxry.E.Le057NBv9bkvQjOIVFK1j0v2A/IR6', 'Rina', 'P', 'santri', 1, '6289652994041'),
(7, 'rinaa', '$2y$10$2J8pK/2KaQJZsSZRcmlNMuxA6.G8d0oyXcs2kJp.mljkRdLZPE6x6', 'Rina Ada', 'P', 'santri', 1, '6289666927360'),
(8, 'ahmad123', '$2y$10$bNR7bayJDE1TAn9LkNcU5u7vdI4dlx/oLRVWbdMihRns/UGlEaHpu', 'Ahmad Fulan', 'L', 'santri', 1, '6281283701940'),
(9, 'siti456', '$2y$10$q2WRQbyjsQ2y2puxvi4p7.hDIa4cErqP5Q5fwuC2CkoyIQ094AgI2', 'Siti Aminah', 'P', 'santri', 2, '6281283701940'),
(10, 'budi789', '$2y$10$XwTtCeB9TSCpJRIjvU1gyu0GLAmdvAfQO7/G9/NQ2vZ/1n2YmyXIm', 'Budi Santoso', 'L', 'santri', 1, '6281283701940'),
(11, 'nia', '$2y$10$BKOO/9RqBG8hbJ4BGd5YPu2tbmcZ2lrVJYddXdofxIbX0IA0Zs1XK', 'Nia', 'P', 'santri', 1, '6281283701940'),
(12, 'ucok', '$2y$10$WtRxTHOZSARMZyJuoZaZE.58qzj2/ZRFO3KX4KpBD0TZ093xkK6ky', 'Ucok', 'L', 'santri', 1, '6281283701940'),
(13, 'erlan', '$2y$10$rOIozJB2TOb/czvtczEZe.72MEt13zeeojc71QlnxNdtj4uswyCr6', 'Erlan', 'L', 'santri', 1, '6281283701940'),
(14, 'agus', '$2y$10$enAlVimKnVPQreRnn5wTX.88AZnknYkwwYTi12fviinPtv0CIrJAm', 'Agus', 'L', 'santri', 1, '6281283701940'),
(15, 'hindun', '$2y$10$qc0rsoa29ddWDuKVZp78yuKqS.xdwfuGx6Ap/iNB1WB.l9ekM0Ct2', 'Hindun', 'P', 'santri', 1, '6281283701940'),
(16, 'musa', '$2y$10$8r2s2Bl0lUGv1GdH4BRkFO2otL8A6qhEnqhpWGLAC1Juy6liepM/K', 'Musa', 'L', 'santri', 1, '6281283701940'),
(17, 'abri', '$2y$10$tnRNajK0fdEVXLZAN5azNu8Xs8Oyh5hZNgFa0Yu9BKHyBpjDJwqSu', 'Abri', 'L', 'santri', 1, '6281283701940'),
(18, 'eno', '$2y$10$E5IM7B.N/oCqQkZ24FycyOoYUKsmNOs8/QfYahlYA9HZ9zUZ1bJqy', 'Eno', 'P', 'santri', 1, '6281283701940'),
(19, 'dino', '$2y$10$AR5fyalTDiel5BZ7M.8MWu9qryOweR3ky6PyWmJpjwd8T245R8uga', 'Dino', 'L', 'santri', 1, '6281283701940'),
(20, 'minan', '$2y$10$E3Tv0jUdDoJHTRyFHC33l./FMtGokGDNpdGMtKTxJCB7V.a4318TS', 'Minan', 'L', 'santri', 1, '6281283701940'),
(21, 'ustadfulan', '$2y$10$tbBGkCQsWpXBpt7vcJgod./pS0T0ei9jO6FUsSMF.8w1JWGUFVy3i', 'Ustad FUlan', 'L', 'guru', 3, NULL),
(22, 'amin1', '$2y$10$AhY3KyBxrBR1ZZPv9JhZt.qcup/H9ZRvbbJYbE4UkdBWyrAM.Chpm', 'Amin', 'L', 'santri', 3, '62812345678');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tanggal` (`tanggal`),
  ADD KEY `kegiatan_id` (`kegiatan_id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pengirim_id` (`pengirim_id`),
  ADD KEY `penerima_id` (`penerima_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logs_ibfk_2` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `pesan_ibfk_1` FOREIGN KEY (`pengirim_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesan_ibfk_2` FOREIGN KEY (`penerima_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
