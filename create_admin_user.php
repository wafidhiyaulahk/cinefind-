<?php
require_once 'config/database.php';

$username = 'admin';
$password = 'admin123'; // Password default, GANTI SEGERA setelah login!
$role = 'admin';

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah user admin sudah ada
$stmt = $conn->prepare("SELECT id_role FROM role WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Pengguna admin dengan username '$username' sudah ada.\n";
} else {
    // Insert user admin baru
    $stmt = $conn->prepare("INSERT INTO role (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "Pengguna admin '$username' berhasil ditambahkan ke database.\n";
        echo "Username: $username\n";
        echo "Password: $password (Sangat Disarankan untuk Menggantinya Setelah Login)\n";
    } else {
        echo "Terjadi kesalahan saat menambahkan pengguna admin: " . $conn->error . "\n";
    }
}

$stmt->close();
$conn->close();
?> 