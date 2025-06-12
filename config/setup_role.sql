-- Create role table if it doesn't exist
CREATE TABLE IF NOT EXISTS `role` (
    `id_role` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'pengguna') NOT NULL DEFAULT 'pengguna',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create pengguna table if it doesn't exist
CREATE TABLE IF NOT EXISTS `pengguna` (
    `id_pengguna` INT PRIMARY KEY AUTO_INCREMENT,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `role_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `role`(`id_role`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user if not exists
INSERT INTO `role` (`username`, `email`, `password`, `role`)
SELECT 'admin', 'admin@cinefind.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM `role` WHERE username = 'admin');

-- Insert default admin pengguna data if not exists
INSERT INTO `pengguna` (`nama_lengkap`, `role_id`)
SELECT 'Administrator', r.id_role
FROM `role` r
WHERE r.username = 'admin'
AND NOT EXISTS (SELECT 1 FROM `pengguna` WHERE role_id = r.id_role); 