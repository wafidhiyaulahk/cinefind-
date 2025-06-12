-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 09 Jun 2025 pada 10.29
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
-- Database: `coda3424_cinefind`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `film`
--

CREATE TABLE `film` (
  `id_film` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `sutradara` varchar(100) DEFAULT NULL,
  `tahun_rilis` year(4) DEFAULT NULL,
  `durasi` int(5) DEFAULT NULL COMMENT 'durasi dalam menit',
  `poster_url` varchar(255) DEFAULT NULL,
  `rating_avg` decimal(3,2) DEFAULT 0.00,
  `rating_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `film`
--

INSERT INTO `film` (`id_film`, `judul`, `deskripsi`, `genre`, `sutradara`, `tahun_rilis`, `durasi`, `poster_url`, `rating_avg`, `rating_count`, `created_at`, `updated_at`) VALUES
(1, 'FAST X', 'Dom Toretto dan keluarganya menghadapi musuh paling mematikan yang pernah mereka hadapi.', 'Action', 'Louis Leterrier', '2023', 141, 'https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg', 3.50, 4, '2025-06-02 17:21:57', '2025-06-02 17:27:43'),
(2, 'Oppenheimer', 'Kisah tentang ilmuwan Amerika J. Robert Oppenheimer dan perannya dalam pengembangan bom atom.', 'Drama', 'Christopher Nolan', '2023', 180, 'images/oppenheimer.jpeg', 5.00, 2, '2025-06-02 17:21:57', '2025-06-02 17:27:43'),
(3, 'Avengers: Endgame', 'Para Avengers harus mengembalikan keseimbangan alam semesta setelah Thanos menghancurkan setengah dari semua kehidupan.', 'Action', 'Anthony Russo', '2019', 181, 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg', 5.00, 2, '2025-06-02 17:21:57', '2025-06-02 17:27:43'),
(4, 'Spider-Man: No Way Home', 'Peter Parker meminta bantuan Doctor Strange untuk membuat dunia melupakan identitasnya sebagai Spider-Man.', 'Action', 'Jon Watts', '2021', 148, 'images/Spider-Man_ No Way Home.jpeg', 0.00, 0, '2025-06-02 17:21:57', '2025-06-02 17:21:57'),
(5, 'FAST X', 'Dom Toretto dan keluarganya menghadapi musuh paling mematikan yang pernah mereka hadapi.', 'Action', 'Louis Leterrier', '2023', 141, 'https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg', 0.00, 0, '2025-06-02 17:27:43', '2025-06-02 17:27:43'),
(6, 'Oppenheimer', 'Kisah tentang ilmuwan Amerika J. Robert Oppenheimer dan perannya dalam pengembangan bom atom.', 'Drama', 'Christopher Nolan', '2023', 180, 'images/oppenheimer.jpeg', 0.00, 0, '2025-06-02 17:27:43', '2025-06-02 17:27:43'),
(7, 'Avengers: Endgame', 'Para Avengers harus mengembalikan keseimbangan alam semesta setelah Thanos menghancurkan setengah dari semua kehidupan.', 'Action', 'Anthony Russo', '2019', 181, 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg', 0.00, 0, '2025-06-02 17:27:43', '2025-06-02 17:27:43'),
(8, 'Spider-Man: No Way Home', 'Peter Parker meminta bantuan Doctor Strange untuk membuat dunia melupakan identitasnya sebagai Spider-Man.', 'Action', 'Jon Watts', '2021', 148, 'images/Spider-Man_ No Way Home.jpeg', 0.00, 0, '2025-06-02 17:27:43', '2025-06-02 17:27:43'),
(9, 'FAST X', 'Dom Toretto dan keluarganya menghadapi musuh paling mematikan yang pernah mereka hadapi.', 'Action', 'Louis Leterrier', '2023', 141, 'https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg', 0.00, 0, '2025-06-02 17:28:54', '2025-06-02 17:28:54'),
(10, 'Oppenheimer', 'Kisah tentang ilmuwan Amerika J. Robert Oppenheimer dan perannya dalam pengembangan bom atom.', 'Drama', 'Christopher Nolan', '2023', 180, 'images/oppenheimer.jpeg', 0.00, 0, '2025-06-02 17:28:54', '2025-06-02 17:28:54'),
(11, 'Avengers: Endgame', 'Para Avengers harus mengembalikan keseimbangan alam semesta setelah Thanos menghancurkan setengah dari semua kehidupan.', 'Action', 'Anthony Russo', '2019', 181, 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg', 0.00, 0, '2025-06-02 17:28:54', '2025-06-02 17:28:54'),
(12, 'Spider-Man: No Way Home', 'Peter Parker meminta bantuan Doctor Strange untuk membuat dunia melupakan identitasnya sebagai Spider-Man.', 'Action', 'Jon Watts', '2021', 148, 'images/Spider-Man_ No Way Home.jpeg', 0.00, 0, '2025-06-02 17:28:54', '2025-06-02 17:28:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `film_genre`
--

CREATE TABLE `film_genre` (
  `id` int(11) NOT NULL,
  `id_film` int(11) NOT NULL,
  `genre` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `film_genre`
--

INSERT INTO `film_genre` (`id`, `id_film`, `genre`, `created_at`) VALUES
(1, 1, 'Action', '2025-06-02 17:27:43'),
(2, 1, 'Crime', '2025-06-02 17:27:43'),
(3, 1, 'Thriller', '2025-06-02 17:27:43'),
(4, 2, 'Drama', '2025-06-02 17:27:43'),
(5, 2, 'Biography', '2025-06-02 17:27:43'),
(6, 2, 'History', '2025-06-02 17:27:43'),
(7, 3, 'Action', '2025-06-02 17:27:43'),
(8, 3, 'Adventure', '2025-06-02 17:27:43'),
(9, 3, 'Sci-Fi', '2025-06-02 17:27:43'),
(10, 4, 'Action', '2025-06-02 17:27:43'),
(11, 4, 'Adventure', '2025-06-02 17:27:43'),
(12, 4, 'Fantasy', '2025-06-02 17:27:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(7, 'wafibinrehan@gmail.com', 'bba7d401c8449e2b73b8d6c5f87d54095848a306d709e6e0b93982d4866ce4e0', '2025-06-01 19:02:34', '2025-06-01 16:02:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `email`, `foto_profil`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 'wafidhiyaulhak', 'wafibinrehan@gmail.com', 'uploads/profiles/avatar_683f01e2221462.18209553.png', 2, '2025-06-01 21:38:40', '2025-06-03 21:09:27'),
(8, 'Administrator', NULL, NULL, 6, '2025-06-03 22:47:00', '2025-06-03 22:47:00'),
(9, 'sriaruyanti', NULL, NULL, 14, '2025-06-04 00:04:37', '2025-06-04 00:04:37'),
(10, 'fajriyah', NULL, NULL, 15, '2025-06-09 14:22:48', '2025-06-09 14:22:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `review`
--

CREATE TABLE `review` (
  `id_review` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_film` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` between 1 and 5),
  `komentar` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `review`
--

INSERT INTO `review` (`id_review`, `id_pengguna`, `id_film`, `rating`, `komentar`, `status`, `created_at`) VALUES
(1, 1, 1, 4, 'Film action yang seru dan menegangkan!', 'pending', '2025-06-02 17:21:57'),
(2, 1, 2, 5, 'Karya masterpiece dari Christopher Nolan', 'pending', '2025-06-02 17:21:57'),
(5, 1, 1, 4, 'Film action yang seru dan menegangkan!', 'pending', '2025-06-02 17:27:43'),
(6, 1, 2, 5, 'Karya masterpiece dari Christopher Nolan', 'pending', '2025-06-02 17:27:43'),
(9, 1, 1, 4, 'Film action yang seru dan menegangkan!', 'pending', '2025-06-02 17:28:54'),
(10, 1, 2, 5, 'Karya masterpiece dari Christopher Nolan', 'pending', '2025-06-02 17:28:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role`
--

CREATE TABLE `role` (
  `id_role` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','pengguna') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `role`
--

INSERT INTO `role` (`id_role`, `username`, `password`, `role`, `created_at`, `updated_at`, `email`) VALUES
(2, 'wafidhiyaulhak', '$2y$10$5RbfPeCVpZVeh3j2lDGfduxp8w49E1yYhi4ABKxnwurnubOowL/vu', 'pengguna', '2025-06-01 21:38:40', '2025-06-03 20:33:57', ''),
(6, 'admin', '$2y$10$6pM7suA4jIWGsrs0PoVjNee0tOrDxzeVQ0SoPpwhDckJ9Vbx655Fm', 'admin', '2025-06-01 22:31:01', '2025-06-01 22:31:01', ''),
(14, 'sriariyanti', '$2y$10$4ERsA4sheI4wbRwf37qqoeB/L4BZf7X1bLHOlwfCtKotn//MfeBkK', 'pengguna', '2025-06-04 00:04:37', '2025-06-04 00:04:37', 'wafibinrehan@gmail.com'),
(15, 'fajriyah', '$2y$10$wLEcG0m6.BOocWWAgjX69OlnQtH7Pdta6gx7kKfkK3s/MJZx8F.Wa', 'pengguna', '2025-06-09 14:22:48', '2025-06-09 14:22:48', 'fajriyah@gmail.com');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `watchlist`
--

CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `movie_title` varchar(255) NOT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_pengguna` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `watchlist`
--

INSERT INTO `watchlist` (`id`, `user_id`, `movie_id`, `movie_title`, `poster_path`, `added_at`, `id_pengguna`) VALUES
(1, 2, 1257960, 'Sikandar', '/t48miSSfe7COqgbgMyRIyPVTBoM.jpg', '2025-06-03 15:12:20', 0),
(3, 2, 950387, 'A Minecraft Movie', '/yFHHfHcUgGAxziP1C3lLt0q2T4s.jpg', '2025-06-03 15:12:54', 0),
(4, 2, 552524, 'Lilo & Stitch', '/tUae3mefrDVTgm5mRzqWnZK6fOP.jpg', '2025-06-03 15:14:46', 0),
(7, 2, 1232546, 'Until Dawn', '/juA4IWO52Fecx8lhAsxmDgy3M3.jpg', '2025-06-03 17:02:47', 0),
(8, 2, 436969, 'The Suicide Squad', '/q61qEyssk2ku3okWICKArlAdhBn.jpg', '2025-06-09 06:01:30', 0),
(10, 2, 870028, 'The Accountant²', '/ieYaJz2nzs4wcqpWaofagzGoGPi.jpg', '2025-06-09 06:04:46', 0),
(11, 2, 1376434, 'Predator: Killer of Killers', '/lIBtgpfiB92xNoB3Wa2ZtRtcyYP.jpg', '2025-06-09 06:04:47', 0),
(12, 2, 1317938, 'Norma: Antara Mertua dan Menantu', '/vWfe6onIjMwJNrb1ZVtQFDI3rMa.jpg', '2025-06-09 06:11:44', 0),
(13, 2, 321612, 'Beauty and the Beast', '/hKegSKIDep2ewJWPUQD7u0KqFIp.jpg', '2025-06-09 06:26:02', 0),
(14, 2, 315635, 'Spider-Man: Homecoming', '/c24sv2weTHPsmDa7jEMN0m2P3RT.jpg', '2025-06-09 06:26:04', 0),
(15, 2, 1315988, 'Mikaela', '/xG8olkWOmoW78GbozKbS2UxYGEo.jpg', '2025-06-09 06:26:09', 0),
(17, 2, 302946, 'The Accountant', '/fceheXB5fC4WrLVuWJ6OZv9FXYr.jpg', '2025-06-09 06:35:15', 0),
(18, 15, 299536, 'Avengers: Infinity War', '/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg', '2025-06-09 07:23:22', 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`id_film`),
  ADD KEY `idx_tahun_rilis` (`tahun_rilis`),
  ADD KEY `idx_genre` (`genre`);

--
-- Indeks untuk tabel `film_genre`
--
ALTER TABLE `film_genre`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_film_genre` (`id_film`,`genre`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_pengguna_role` (`role_id`),
  ADD KEY `idx_email` (`email`);

--
-- Indeks untuk tabel `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `fk_review_user` (`id_pengguna`),
  ADD KEY `fk_review_film` (`id_film`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_movie` (`user_id`,`movie_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `film`
--
ALTER TABLE `film`
  MODIFY `id_film` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `film_genre`
--
ALTER TABLE `film_genre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `review`
--
ALTER TABLE `review`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `film_genre`
--
ALTER TABLE `film_genre`
  ADD CONSTRAINT `fk_film_genre_film` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD CONSTRAINT `fk_pengguna_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id_role`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengguna_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id_role`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `fk_review_film` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `watchlist`
--
ALTER TABLE `watchlist`
  ADD CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `role` (`id_role`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
