<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses tidak diizinkan'
    ]);
    exit();
}

// Check if user_id is provided
if (!isset($_POST['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID Pengguna diperlukan'
    ]);
    exit();
}

$userId = $_POST['user_id'];

try {
    // Begin transaction
    $conn->begin_transaction();

    // Prevent deleting admin users
    $checkAdminQuery = "SELECT role_id FROM pengguna WHERE id_pengguna = ?";
    $stmt = $conn->prepare($checkAdminQuery);
    if (!$stmt) {
        throw new Exception("Error preparing admin check query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Error executing admin check query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['role_id'] == 1) {
        throw new Exception("Tidak dapat menghapus akun admin");
    }

    // Delete user's watchlist entries
    $deleteWatchlistQuery = "DELETE FROM watchlist WHERE id_pengguna = ?";
    $stmt = $conn->prepare($deleteWatchlistQuery);
    if (!$stmt) {
        throw new Exception("Error preparing watchlist delete query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting watchlist entries: " . $stmt->error);
    }

    // Delete user's reviews
    $deleteReviewsQuery = "DELETE FROM review WHERE id_pengguna = ?";
    $stmt = $conn->prepare($deleteReviewsQuery);
    if (!$stmt) {
        throw new Exception("Error preparing reviews delete query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting reviews: " . $stmt->error);
    }

    // Finally, delete the user
    $deleteUserQuery = "DELETE FROM pengguna WHERE id_pengguna = ?";
    $stmt = $conn->prepare($deleteUserQuery);
    if (!$stmt) {
        throw new Exception("Error preparing user delete query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception("Error deleting user: " . $stmt->error);
    }

    // If we got here, commit the transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Pengguna berhasil dihapus'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menghapus pengguna: ' . $e->getMessage()
    ]);
} finally {
    // Close statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 