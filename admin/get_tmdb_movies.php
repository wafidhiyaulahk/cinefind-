<?php
require_once 'tmdb_api.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'list';
$searchQuery = $_GET['query'] ?? '';
$page = $_GET['page'] ?? 1;
$type = $_GET['type'] ?? 'popular';

try {
    switch ($action) {
        case 'search':
            if (empty($searchQuery)) {
                throw new Exception('Search query is required');
            }
            $result = searchMovies($searchQuery);
            break;
            
        case 'list':
        default:
            $result = getMovieList($type, $page);
            break;
    }
    
    if (isset($result['results'])) {
        $formattedMovies = array_map('formatMovieData', $result['results']);
        echo json_encode([
            'status' => 'success',
            'data' => $formattedMovies,
            'total_pages' => $result['total_pages'],
            'current_page' => $result['page'],
            'total_results' => $result['total_results']
        ]);
    } else {
        throw new Exception('Invalid response from TMDB API');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 