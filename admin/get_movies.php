<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/database.php';

$movies_data = [];

try {
    // Fetch movies from the local 'film' table
    $sql = "SELECT 
                f.id_film,
                f.judul AS title,
                f.deskripsi AS overview,
                GROUP_CONCAT(DISTINCT fg.genre SEPARATOR ', ') AS genre, -- Concatenate genres
                f.sutradara AS director,
                f.tahun_rilis AS release_date,
                f.durasi AS runtime,
                f.poster_url AS poster_path, -- Assuming this is the full URL or relative path from project root
                f.rating_avg AS rating,
                f.rating_count,
                f.created_at,
                'Released' as status -- Placeholder, or use your actual status column
            FROM film f
            LEFT JOIN film_genre fg ON f.id_film = fg.id_film
            GROUP BY f.id_film -- Group by film to get one row per film with concatenated genres
            ORDER BY f.tahun_rilis DESC, f.judul ASC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $formatted_movie = [
                'id' => (int)$row['id_film'],
                'title' => htmlspecialchars($row['title']),
                'original_title' => htmlspecialchars($row['title']), // Assuming same as title for local films
                'genre' => htmlspecialchars($row['genre'] ?? 'N/A'),
                'rating' => number_format((float)($row['rating'] ?? 0), 1),
                'rating_count' => (int)($row['rating_count'] ?? 0),
                'release_date' => $row['release_date'] ? $row['release_date'] . '-01-01' : 'N/A', // Assuming YEAR, format to YYYY-MM-DD
                'poster_path' => htmlspecialchars($row['poster_path'] ?? '../assets/images/default-poster.png'),
                'backdrop_path' => null, // You might not have this locally
                'overview' => htmlspecialchars($row['overview'] ?? 'No overview available.'),
                'runtime' => (int)($row['runtime'] ?? 0),
                'budget' => 'N/A', // Local films might not have this detailed
                'revenue' => 'N/A',
                'director' => htmlspecialchars($row['director'] ?? 'N/A'),
                'cast' => [], // You'd need a separate cast table and query for this
                'status' => htmlspecialchars($row['status'] ?? 'N/A'),
                'type' => 'local' // Indicate this is a local movie
            ];
            $movies_data[] = $formatted_movie;
        }
    }
    
    // You can still add TMDB fetching here if you want to mix or have an "import" feature
    // For simplicity, we'll stick to local for now.

    echo json_encode([
        'status' => 'success',
        'data' => $movies_data,
        'total' => count($movies_data)
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