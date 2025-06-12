<?php
session_start();
require_once '../config/database.php'; // Path to your database configuration

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Get genre distribution data
$query = "SELECT 
    genre,
    COUNT(*) as total
FROM film 
WHERE genre IS NOT NULL
GROUP BY genre
ORDER BY total DESC
LIMIT 6";

$result = mysqli_query($conn, $query);

$labels = [];
$values = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['genre'];
    $values[] = (int)$row['total'];
}

echo json_encode([
    'status' => 'success',
    'labels' => $labels,
    'values' => $values
]);

if (isset($conn)) {
    $conn->close();
}
?>