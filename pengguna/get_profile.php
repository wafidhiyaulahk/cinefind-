<?php
session_start();
require_once '../config/database.php'; // Pastikan path ini benar relatif terhadap get_profile.php

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    $user_id_from_session = $_SESSION['user_id']; // Ini adalah id_role

    $query = "SELECT p.nama_lengkap, p.email AS pengguna_email, p.foto_profil,
                     r.username, r.email AS role_email, r.role
              FROM role r
              LEFT JOIN pengguna p ON r.id_role = p.role_id
              WHERE r.id_role = ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id_from_session);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $email_to_use = !empty($row['pengguna_email']) ? $row['pengguna_email'] : $row['role_email'];
        $name_to_use = !empty($row['nama_lengkap']) ? $row['nama_lengkap'] : $row['username'];
        // Path avatar relatif dari folder 'pengguna' jika file ada di root proyek.
        // Jika foto_profil kosong atau tidak ada, gunakan default.
        $avatar_path = !empty($row['foto_profil']) ? '../' . htmlspecialchars($row['foto_profil']) : '../assets/images/default-avatar.png'; // Pastikan path default avatar benar

        echo json_encode([
            'success' => true, // Tambahkan flag sukses
            'name' => $name_to_use,
            'username' => $row['username'],
            'email' => $email_to_use,
            'avatar' => $avatar_path,
            'role' => $row['role']
        ]);
    } else {
        echo json_encode(['error' => 'User data not found for session user ID: ' . $user_id_from_session]);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Error in get_profile.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error. Please check server logs.']);
}

$conn->close();
?>