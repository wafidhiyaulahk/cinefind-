<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'coda3424_cinefind';

// Create connection with error reporting
$conn = new mysqli($host, $username, $password, $database);

// Check connection and log any errors
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better character support
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error setting charset: " . $conn->error);
}

// Log successful connection
error_log("Database connection established successfully");

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    error_log("Error creating users table: " . $conn->error);
    die("Error creating table: " . $conn->error);
}
?> 