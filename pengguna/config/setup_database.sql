-- Create movies table
CREATE TABLE IF NOT EXISTS movies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    year VARCHAR(4),
    rating DECIMAL(3,1),
    description TEXT,
    poster_path VARCHAR(255),
    genre VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create watchlist table
CREATE TABLE IF NOT EXISTS watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    movie_title VARCHAR(255) NOT NULL,
    poster_path VARCHAR(255),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES role(id_role) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (user_id, movie_id)
);

-- Insert some sample movies
INSERT INTO movies (title, year, rating, description, poster_path, genre) VALUES
('FAST X', '2023', 7.2, 'Dom Toretto and his family are targeted by the vengeful son of drug kingpin Hernan Reyes.', 'https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg', 'Action'),
('Oppenheimer', '2023', 8.5, 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.', 'images/oppenheimer.jpeg', 'Drama'),
('Avengers: Endgame', '2019', 8.4, 'After the devastating events of Avengers: Infinity War, the universe is in ruins.', 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg', 'Action'),
('Spider-Man: No Way Home', '2021', 8.2, 'With Spider-Man\'s identity now revealed, Peter asks Doctor Strange for help.', 'images/Spider-Man_ No Way Home.jpeg', 'Action'); 