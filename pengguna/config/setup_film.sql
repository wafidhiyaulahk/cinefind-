-- Drop existing film table if exists
DROP TABLE IF EXISTS `film`;

-- Create new film table with complete structure
CREATE TABLE `film` (
    `id_film` INT PRIMARY KEY AUTO_INCREMENT,
    `judul` VARCHAR(255) NOT NULL,
    `deskripsi` TEXT,
    `genre` VARCHAR(100),
    `sutradara` VARCHAR(100),
    `tahun_rilis` YEAR,
    `durasi` INT COMMENT 'durasi dalam menit',
    `poster_url` VARCHAR(255),
    `rating_imdb` DECIMAL(3,1),
    `rating_tmdb` DECIMAL(3,1),
    `rating_rt` DECIMAL(3,1),
    `budget` DECIMAL(15,2) COMMENT 'dalam USD',
    `pendapatan` DECIMAL(15,2) COMMENT 'dalam USD',
    `bahasa` VARCHAR(50),
    `negara` VARCHAR(100),
    `status` ENUM('Released', 'Upcoming', 'Post Production', 'In Production', 'Planned') DEFAULT 'Released',
    `trailer_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create film_cast table for actors and crew
CREATE TABLE `film_cast` (
    `id_cast` INT PRIMARY KEY AUTO_INCREMENT,
    `id_film` INT NOT NULL,
    `nama` VARCHAR(100) NOT NULL,
    `peran` VARCHAR(100) NOT NULL,
    `tipe` ENUM('Actor', 'Actress', 'Director', 'Writer', 'Producer', 'Cinematographer', 'Editor', 'Composer') NOT NULL,
    `foto_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_film`) REFERENCES `film`(`id_film`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create film_genre table for multiple genres per film
CREATE TABLE `film_genre` (
    `id_film_genre` INT PRIMARY KEY AUTO_INCREMENT,
    `id_film` INT NOT NULL,
    `genre` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_film`) REFERENCES `film`(`id_film`) ON DELETE CASCADE,
    UNIQUE KEY `unique_film_genre` (`id_film`, `genre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create film_rating table for aggregated ratings
CREATE TABLE `film_rating` (
    `id_rating` INT PRIMARY KEY AUTO_INCREMENT,
    `id_film` INT NOT NULL,
    `rating_imdb` DECIMAL(3,1),
    `rating_tmdb` DECIMAL(3,1),
    `rating_rt` DECIMAL(3,1),
    `rating_user` DECIMAL(3,1) COMMENT 'Average user rating',
    `total_votes` INT DEFAULT 0,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_film`) REFERENCES `film`(`id_film`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create film_review table for user reviews
CREATE TABLE `film_review` (
    `id_review` INT PRIMARY KEY AUTO_INCREMENT,
    `id_film` INT NOT NULL,
    `id_user` INT NOT NULL,
    `rating` DECIMAL(3,1) NOT NULL CHECK (`rating` BETWEEN 1 AND 10),
    `review_text` TEXT,
    `likes` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_film`) REFERENCES `film`(`id_film`) ON DELETE CASCADE,
    FOREIGN KEY (`id_user`) REFERENCES `role`(`id_role`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_film_review` (`id_film`, `id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create film_award table for awards and nominations
CREATE TABLE `film_award` (
    `id_award` INT PRIMARY KEY AUTO_INCREMENT,
    `id_film` INT NOT NULL,
    `nama_award` VARCHAR(100) NOT NULL,
    `kategori` VARCHAR(100) NOT NULL,
    `tahun` YEAR NOT NULL,
    `hasil` ENUM('Won', 'Nominated', 'Pending') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_film`) REFERENCES `film`(`id_film`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data for film table
INSERT INTO `film` (`judul`, `deskripsi`, `genre`, `sutradara`, `tahun_rilis`, `durasi`, `poster_url`, `rating_imdb`, `rating_tmdb`, `rating_rt`, `bahasa`, `negara`, `status`) VALUES
('FAST X', 'Dom Toretto dan keluarganya menjadi target balas dendam putra raja narkoba Hernan Reyes. Mereka harus menghadapi musuh paling berbahaya mereka dan melindungi semua yang mereka cintai.', 'Action, Crime, Thriller', 'Louis Leterrier', 2023, 141, 'https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg', 6.2, 7.1, 58, 'English', 'United States', 'Released'),
('Oppenheimer', 'Kisah ilmuwan Amerika J. Robert Oppenheimer dan perannya dalam pengembangan bom atom. Film biografi yang menggugah yang mengeksplorasi implikasi moral dari penemuan ilmiah.', 'Biography, Drama, History', 'Christopher Nolan', 2023, 180, 'gmbr/oppenheimer.jpeg', 8.4, 8.2, 93, 'English', 'United States', 'Released'),
('Avengers: Endgame', 'Setelah peristiwa menghancurkan di Avengers: Infinity War, semesta berada dalam kehancuran. Dengan bantuan sekutu yang tersisa, Avengers berkumpul lagi untuk membalikkan tindakan Thanos.', 'Action, Adventure, Drama', 'Anthony Russo, Joe Russo', 2019, 181, 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg', 8.4, 8.4, 94, 'English', 'United States', 'Released'),
('Spider-Man: No Way Home', 'Dengan identitas Spider-Man yang terbongkar, Peter meminta bantuan Doctor Strange. Ketika mantra salah, musuh berbahaya dari dunia lain muncul, memaksa dia untuk menemukan arti sebenarnya menjadi Spider-Man.', 'Action, Adventure, Fantasy', 'Jon Watts', 2021, 148, 'gmbr/Spider-Man_ No Way Home.jpeg', 8.2, 8.3, 93, 'English', 'United States', 'Released');

-- Insert sample data for film_genre
INSERT INTO `film_genre` (`id_film`, `genre`) VALUES
(1, 'Action'),
(1, 'Crime'),
(1, 'Thriller'),
(2, 'Biography'),
(2, 'Drama'),
(2, 'History'),
(3, 'Action'),
(3, 'Adventure'),
(3, 'Drama'),
(4, 'Action'),
(4, 'Adventure'),
(4, 'Fantasy');

-- Insert sample data for film_cast
INSERT INTO `film_cast` (`id_film`, `nama`, `peran`, `tipe`, `foto_url`) VALUES
(1, 'Vin Diesel', 'Dominic Toretto', 'Actor', 'https://image.tmdb.org/t/p/w500/7rwSXluNWZAluYMOEWBxkPmckES.jpg'),
(1, 'Jason Momoa', 'Dante Reyes', 'Actor', 'https://image.tmdb.org/t/p/w500/6d1xbGwEPoO5QxzlxV6yQZqXxrX.jpg'),
(2, 'Cillian Murphy', 'J. Robert Oppenheimer', 'Actor', 'https://image.tmdb.org/t/p/w500/mDCBQNhR6R0PVFucJl0O4Hp5klZ.jpg'),
(2, 'Robert Downey Jr.', 'Lewis Strauss', 'Actor', 'https://image.tmdb.org/t/p/w500/5qHNjhtjMD4YWH3UP0rm4tKwxCL.jpg'),
(3, 'Robert Downey Jr.', 'Tony Stark / Iron Man', 'Actor', 'https://image.tmdb.org/t/p/w500/5qHNjhtjMD4YWH3UP0rm4tKwxCL.jpg'),
(3, 'Chris Evans', 'Steve Rogers / Captain America', 'Actor', 'https://image.tmdb.org/t/p/w500/3bOGNsHlrswhyW79uvIHH1V43JI.jpg'),
(4, 'Tom Holland', 'Peter Parker / Spider-Man', 'Actor', 'https://image.tmdb.org/t/p/w500/2qhIDp44cAqP2clOgt2afQI07X8.jpg'),
(4, 'Zendaya', 'MJ', 'Actress', 'https://image.tmdb.org/t/p/w500/6kzup2ZR5eqG3qyJwqXzVqJt7Ix.jpg');

-- Insert sample data for film_rating
INSERT INTO `film_rating` (`id_film`, `rating_imdb`, `rating_tmdb`, `rating_rt`, `rating_user`, `total_votes`) VALUES
(1, 6.2, 7.1, 58, 7.5, 150000),
(2, 8.4, 8.2, 93, 8.8, 450000),
(3, 8.4, 8.4, 94, 9.0, 800000),
(4, 8.2, 8.3, 93, 8.7, 600000);

-- Insert sample data for film_award
INSERT INTO `film_award` (`id_film`, `nama_award`, `kategori`, `tahun`, `hasil`) VALUES
(2, 'Academy Awards', 'Best Picture', 2024, 'Won'),
(2, 'Academy Awards', 'Best Director', 2024, 'Won'),
(2, 'Academy Awards', 'Best Actor', 2024, 'Won'),
(3, 'Academy Awards', 'Best Visual Effects', 2020, 'Nominated'),
(4, 'Academy Awards', 'Best Visual Effects', 2022, 'Nominated');

-- Create indexes for better performance
CREATE INDEX idx_film_judul ON film(judul);
CREATE INDEX idx_film_tahun ON film(tahun_rilis);
CREATE INDEX idx_film_genre ON film_genre(genre);
CREATE INDEX idx_film_cast_nama ON film_cast(nama);
CREATE INDEX idx_film_rating ON film_rating(rating_user);
CREATE INDEX idx_film_review_rating ON film_review(rating);
CREATE INDEX idx_film_award_tahun ON film_award(tahun); 