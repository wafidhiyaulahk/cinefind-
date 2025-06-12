<?php
require_once 'tmdb_api.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? 'popular';

try {
    $result = getGenreDistribution($type);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 