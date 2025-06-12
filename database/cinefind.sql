-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    id_role INT PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO roles (nama_role) VALUES 
('admin'),
('user');

-- Create users table if not exists
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create pengguna table if not exists
CREATE TABLE IF NOT EXISTS pengguna (
    id_pengguna INT PRIMARY KEY AUTO_INCREMENT,
    nama_lengkap VARCHAR(100) NOT NULL,
    foto_profil VARCHAR(255),
    role_id INT NOT NULL DEFAULT 2, -- Default to user role
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id_role)
);

-- Create watchlist table if not exists
CREATE TABLE IF NOT EXISTS watchlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_pengguna INT NOT NULL,
    movie_id INT NOT NULL,
    movie_title VARCHAR(255) NOT NULL,
    poster_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna) ON DELETE CASCADE,
    UNIQUE KEY unique_user_movie (id_pengguna, movie_id)
);

-- Create reviews table if not exists
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    review_text TEXT NOT NULL,
    rating DECIMAL(3,1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create ratings table if not exists
CREATE TABLE IF NOT EXISTS ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    rating DECIMAL(3,1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_watchlist_user ON watchlist(id_pengguna);
CREATE INDEX idx_watchlist_movie ON watchlist(movie_id);
CREATE INDEX idx_reviews_user ON reviews(user_id);
CREATE INDEX idx_reviews_movie ON reviews(movie_id);
CREATE INDEX idx_ratings_user ON ratings(user_id);
CREATE INDEX idx_ratings_movie ON ratings(movie_id); 