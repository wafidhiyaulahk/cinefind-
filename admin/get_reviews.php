<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/database.php';

$reviews_data = [];

try {
    $sql = "SELECT 
                rev.id_review,
                COALESCE(p.nama_lengkap, r.username) as user_name,
                f.judul as movie_title,
                f.poster_url as movie_poster, -- Assuming poster_url is in film table
                rev.komentar as review_text,
                rev.rating,
                rev.created_at,
                'approved' as status -- Placeholder: you might want a real status column in your review table
            FROM review rev
            JOIN pengguna p ON rev.id_pengguna = p.id_pengguna
            JOIN role r ON p.role_id = r.id_role
            JOIN film f ON rev.id_film = f.id_film
            ORDER BY rev.created_at DESC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $review = [
                'id' => (int)$row['id_review'],
                'user_name' => htmlspecialchars($row['user_name']),
                'movie_title' => htmlspecialchars($row['movie_title']),
                'movie_poster' => htmlspecialchars($row['movie_poster']),
                'review_text' => htmlspecialchars($row['review_text']),
                'rating' => (float)$row['rating'],
                'status' => htmlspecialchars($row['status']), // 'approved', 'pending', 'rejected'
                'created_at' => $row['created_at'] ? date('Y-m-d H:i:s', strtotime($row['created_at'])) : 'N/A',
            ];
            $reviews_data[] = $review;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => $reviews_data,
        'total' => count($reviews_data)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>