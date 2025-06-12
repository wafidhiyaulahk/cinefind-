<?php
session_start();
// 1. Perbaiki path ke database.php
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Silakan login terlebih dahulu', // Pesan dalam Bahasa Indonesia
        'debug' => 'No user_id in session'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $movie_id_from_post = $_POST['movie_id'] ?? ''; // ID film dari TMDB, biasanya integer
    $movie_title = $_POST['movie_title'] ?? '';
    $poster_path = $_POST['poster_path'] ?? '';
    // 2. Gunakan $_SESSION['user_id'] (yang merupakan role_id) secara langsung
    $session_user_id = $_SESSION['user_id'];

    // Debug information
    $debug_info = [
        'action' => $action,
        'movie_id_from_post' => $movie_id_from_post,
        'movie_title' => $movie_title,
        'poster_path' => $poster_path,
        'session_user_id' => $session_user_id
    ];

    if (empty($movie_id_from_post)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID Film dibutuhkan', // Pesan dalam Bahasa Indonesia
            'debug' => $debug_info
        ]);
        exit;
    }

    // Validasi movie_id sebagai integer
    if (!filter_var($movie_id_from_post, FILTER_VALIDATE_INT)) {
        echo json_encode([
            'success' => false,
            'message' => 'ID Film tidak valid', // Pesan dalam Bahasa Indonesia
            'debug' => $debug_info
        ]);
        exit;
    }
    $movie_id_int = (int)$movie_id_from_post;

    try {
        // Check database connection
        if (!$conn) {
            throw new Exception("Koneksi basis data gagal: " . mysqli_connect_error());
        }

        // Tidak perlu lagi mengambil id_pengguna dari tabel pengguna untuk operasi watchlist
        // karena watchlist.user_id langsung merujuk ke role.id_role (yaitu $_SESSION['user_id'])

        if ($action === 'add') {
            // Check if movie already exists in watchlist
            // 3. Gunakan kolom 'user_id' dan 'movie_id' yang benar
            $check_sql = "SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Prepare statement gagal (pengecekan): " . $conn->error);
            }
            // 4. Bind parameter dengan tipe yang benar (i untuk integer, s untuk string)
            $check_stmt->bind_param("ii", $session_user_id, $movie_id_int);
            if (!$check_stmt->execute()) {
                throw new Exception("Execute statement gagal (pengecekan): " . $check_stmt->error);
            }

            $result = $check_stmt->get_result();
            $check_stmt->close();

            if ($result->num_rows > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Film sudah ada di daftar tonton', // Pesan dalam Bahasa Indonesia
                    'debug' => $debug_info
                ]);
                exit;
            }

            // Add to watchlist
            // 3. Gunakan kolom 'user_id' dan 'movie_id' yang benar
            $sql = "INSERT INTO watchlist (user_id, movie_id, movie_title, poster_path) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement gagal (insert): " . $conn->error);
            }
            // 4. Bind parameter dengan tipe yang benar
            $stmt->bind_param("iiss", $session_user_id, $movie_id_int, $movie_title, $poster_path);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement gagal (insert): " . $stmt->error);
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'message' => 'Berhasil ditambahkan ke daftar tonton', // Pesan dalam Bahasa Indonesia
                'debug' => $debug_info
            ]);

        } elseif ($action === 'remove') {
            // Remove from watchlist
            // 3. Gunakan kolom 'user_id' dan 'movie_id' yang benar
            $sql = "DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare statement gagal (delete): " . $conn->error);
            }
            // 4. Bind parameter dengan tipe yang benar
            $stmt->bind_param("ii", $session_user_id, $movie_id_int);
            if (!$stmt->execute()) {
                throw new Exception("Execute statement gagal (delete): " . $stmt->error);
            }
            $stmt->close();

            echo json_encode([
                'success' => true,
                'message' => 'Berhasil dihapus dari daftar tonton', // Pesan dalam Bahasa Indonesia
                'debug' => $debug_info
            ]);
        } else {
            throw new Exception("Aksi tidak valid");
        }
    } catch (Exception $e) {
        error_log("Error in watchlist_handler.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            'debug' => $debug_info
        ]);
    }
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metode request tidak valid',
        'debug' => $debug_info
    ]);
}
?>