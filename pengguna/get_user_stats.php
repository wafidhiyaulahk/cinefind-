<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get watchlist count
    $query_watchlist = "SELECT COUNT(*) as count FROM watchlist WHERE user_id = ?";
    $stmt_watchlist = $conn->prepare($query_watchlist);
    $stmt_watchlist->bind_param("i", $user_id);
    $stmt_watchlist->execute();
    $watchlist_result = $stmt_watchlist->get_result();
    $watchlist_count = $watchlist_result->fetch_assoc()['count'];
    
    // Get reviews count
    $query_reviews = "SELECT COUNT(*) as count FROM review WHERE id_pengguna = ?";
    $stmt_reviews = $conn->prepare($query_reviews);
    $stmt_reviews->bind_param("i", $user_id);
    $stmt_reviews->execute();
    $reviews_result = $stmt_reviews->get_result();
    $reviews_count = $reviews_result->fetch_assoc()['count'];
    
    // Get ratings count (which is the same as reviews count in this structure)
    $query_ratings = "SELECT COUNT(*) as count FROM review WHERE id_pengguna = ?";
    $stmt_ratings = $conn->prepare($query_ratings);
    $stmt_ratings->bind_param("i", $user_id);
    $stmt_ratings->execute();
    $ratings_result = $stmt_ratings->get_result();
    $ratings_count = $ratings_result->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'watchlist_count' => $watchlist_count,
        'reviews_count' => $reviews_count,
        'ratings_count' => $ratings_count
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

// Close all statements
if (isset($stmt_watchlist)) $stmt_watchlist->close();
if (isset($stmt_reviews)) $stmt_reviews->close();
if (isset($stmt_ratings)) $stmt_ratings->close();

$conn->close();
?> 