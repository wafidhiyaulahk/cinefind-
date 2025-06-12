<?php
header('Content-Type: application/json');
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/database.php'; // Path to your database configuration

// Get all users with their role information
$query = "SELECT 
    p.id_pengguna as id,
    p.nama_lengkap as name,
    COALESCE(p.email, r.email) as email,
    r.role as role_text,
    p.created_at as join_date
FROM pengguna p
JOIN role r ON p.role_id = r.id_role
ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);

$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'role_text' => ucfirst($row['role_text']),
        'join_date' => date('Y-m-d', strtotime($row['join_date']))
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => $users
]);

if (isset($conn)) {
    $conn->close();
}
?>