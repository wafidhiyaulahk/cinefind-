<?php
session_start();
require_once '../config/database.php'; // Path to your database configuration

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$labels = [];
$values = [];

try {
    // Get user growth data for the last 6 months
    $query = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as total
              FROM pengguna 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY month ASC";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = date('M Y', strtotime($row['month'] . '-01'));
            $values[] = (int)$row['total'];
        }
    } else {
        // Provide some default empty data if no users in the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $labels[] = date('M Y', strtotime("-$i months"));
            $values[] = 0;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'labels' => $labels,
        'values' => $values
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'labels' => [], // Send empty arrays on error
        'values' => []
    ]);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>