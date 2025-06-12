<?php
session_start();
require_once '../config/database.php'; // Pastikan path ini benar
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Memerlukan ID pengguna dari sesi untuk semua operasi
$user_id = $_SESSION['pengguna_id'] ?? null;
if ($method === 'POST' && !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk memberikan ulasan.']);
    exit();
}

if ($method === 'GET') {
    // Ambil semua ulasan untuk movie_id tertentu
    $movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;
    if (!$movie_id) {
        echo json_encode(['success' => false, 'message' => 'Movie ID tidak valid.']);
        exit();
    }
    
    // Query untuk mengambil ulasan beserta nama pengguna
    // Menggunakan JOIN yang benar melalui tabel pengguna dan role
    $sql = "SELECT rev.rating, rev.komentar, rev.created_at, r.username 
            FROM review rev
            JOIN pengguna p ON rev.id_pengguna = p.id_pengguna
            JOIN role r ON p.role_id = r.id_role
            WHERE rev.id_film = ? AND rev.status = 'approved' -- Hanya tampilkan ulasan yang sudah disetujui
            ORDER BY rev.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = [
            'username' => htmlspecialchars($row['username']),
            'rating' => (int)$row['rating'],
            'komentar' => htmlspecialchars($row['komentar']),
            'created_at' => $row['created_at']
        ];
    }
    echo json_encode(['success' => true, 'reviews' => $reviews]);

} elseif ($method === 'POST') {
    // Tambah ulasan baru
    $movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $komentar = isset($_POST['komentar']) ? trim($_POST['komentar']) : '';

    if (!$movie_id || !$rating || empty($komentar)) {
        echo json_encode(['success' => false, 'message' => 'ID Film, rating, dan komentar wajib diisi.']);
        exit();
    }

    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating harus antara 1 dan 5.']);
        exit();
    }

    // Menggunakan id_pengguna dari session yang sudah diset saat login
    $id_pengguna = $_SESSION['pengguna_id'];

    // Query untuk memasukkan ulasan baru
    $sql = "INSERT INTO review (id_pengguna, id_film, rating, komentar, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiis', $id_pengguna, $movie_id, $rating, $komentar);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Ulasan berhasil dikirim dan sedang menunggu persetujuan.']);
    } else {
        // Cek jika error disebabkan oleh duplikasi entri
        if ($conn->errno == 1062) { // 1062 adalah kode error untuk duplikasi entri
             echo json_encode(['success' => false, 'message' => 'Anda sudah memberikan ulasan untuk film ini.']);
        } else {
             echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ulasan: ' . $stmt->error]);
        }
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak didukung.']);
}

$conn->close();
?>