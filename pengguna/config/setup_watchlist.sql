-- Create watchlist table
CREATE TABLE IF NOT EXISTS watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    movie_title VARCHAR(255) NOT NULL,
    poster_path VARCHAR(255),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES role(id_role) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (user_id, movie_id)
); 