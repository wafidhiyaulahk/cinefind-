<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Akses tidak diizinkan']);
    exit();
}

try {
    // Query untuk mengambil data watchlist dengan join ke tabel role untuk username
    $query = "
        SELECT w.id, w.user_id, r.username, w.movie_id, w.movie_title, w.poster_path, w.added_at 
        FROM watchlist w 
        INNER JOIN role r ON w.user_id = r.id_role 
        ORDER BY w.added_at DESC
    ";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Error executing query: " . mysqli_error($conn));
    }

    $watchlist = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $watchlist[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'username' => $row['username'] ?? 'Unknown',
            'movie_id' => $row['movie_id'],
            'movie_title' => $row['movie_title'],
            'poster_path' => $row['poster_path'],
            'added_date' => date('d/m/Y H:i', strtotime($row['added_at']))
        ];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $watchlist,
        'count' => count($watchlist)
    ]);

} catch (Exception $e) {
    error_log("Error in get_watchlist.php: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal mengambil data watchlist: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>