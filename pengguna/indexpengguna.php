<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Perbaikan: Path ke login.php
    exit;
}

// Get user information from both tables
// Perbaikan: Menggunakan r.id_role untuk dicocokkan dengan $_SESSION['user_id']
$user_query = "SELECT p.*, r.username, r.email AS role_email_from_role_table, r.role 
               FROM pengguna p 
               JOIN role r ON p.role_id = r.id_role 
               WHERE r.id_role = ?"; // Diubah dari p.id_pengguna menjadi r.id_role
$stmt = $conn->prepare($user_query);

if (!$stmt) { // Tambahkan pengecekan prepare statement
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']); // $_SESSION['user_id'] adalah id_role
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

if (!$user_data) {
    // Jika query utama tidak menghasilkan data (misalnya, ada role tapi tidak ada entri pengguna terkait)
    // Cobalah untuk mengambil data minimal dari tabel role untuk mencegah die total jika memungkinkan
    // Ini lebih relevan untuk edit-profile, tapi sebagai tindakan pencegahan:
    $role_fallback_query = "SELECT username, email AS role_email_from_role_table, role FROM role WHERE id_role = ?";
    $stmt_fallback = $conn->prepare($role_fallback_query);
    if ($stmt_fallback) {
        $stmt_fallback->bind_param("i", $_SESSION['user_id']);
        $stmt_fallback->execute();
        $user_data = $stmt_fallback->get_result()->fetch_assoc();
        $stmt_fallback->close();
    }
    
    if (!$user_data) { // Jika setelah fallback pun tidak ada data
        die("User not found. Please make sure you are logged in correctly. (indexpengguna.php)");
    }
    // Jika fallback berhasil, $user_data akan memiliki username, email, role, tapi tidak ada p.* (nama_lengkap, foto_profil)
    // Ini perlu ditangani di bagian HTML jika field tersebut wajib.
}

// Set default avatar path
// Gunakan email dari tabel role jika email pengguna tidak ada, atau nama_lengkap jika tidak ada di p.*
$email_display = $user_data['role_email_from_role_table'] ?? 'N/A';
$nama_display = $user_data['nama_lengkap'] ?? $user_data['username'] ?? 'User';


$default_avatar = '../assets/images/default-avatar.png'; // Pastikan path ini benar
$user_avatar = (!empty($user_data['foto_profil'])) ? '../' . htmlspecialchars($user_data['foto_profil']) : $default_avatar;


$pengguna_id_for_watchlist = $_SESSION['pengguna_id'] ?? null;
if ($pengguna_id_for_watchlist === null) {
    // Handle jika pengguna_id tidak ada di session, mungkin user belum lengkap datanya
    // atau perlu logout dan login ulang.
    // Untuk sementara, set watchlist kosong.
    $user_watchlist = [];
} else {
    $watchlist_query = "SELECT w.movie_id, f.judul, f.poster_url, 
                       f.rating_avg, f.rating_count
                       FROM watchlist w 
                       INNER JOIN film f ON w.movie_id = f.id_film 
                       WHERE w.id_pengguna = ?"; // Menggunakan id_pengguna
    $stmt_watchlist = $conn->prepare($watchlist_query);
    $stmt_watchlist->bind_param("i", $pengguna_id_for_watchlist); // Bind id_pengguna
    $stmt_watchlist->execute();
    $watchlist_result = $stmt_watchlist->get_result();

    $user_watchlist = array();
    while ($row = $watchlist_result->fetch_assoc()) {
        $user_watchlist[$row['movie_id']] = array(
            'judul' => $row['judul'],
            'poster_url' => $row['poster_url'],
            'rating' => $row['rating_avg'], // asumsikan rating adalah rating_avg
            'rating_count' => $row['rating_count']
        );
    }
    $stmt_watchlist->close();
}


// Function to display movie cards (pastikan ini ada atau di-include)
function displayMovieCards($movies_result) { // Diubah untuk menerima hasil query mysqli
    global $user_watchlist, $conn; // Tambahkan $conn jika diperlukan di dalam fungsi
    // Contoh implementasi jika $movies adalah hasil dari $conn->query()
    // Jika $movies sudah array, tidak perlu fetch_assoc() lagi.

    // Anda perlu menyesuaikan bagaimana $movies diproses.
    // Jika $movies adalah objek hasil mysqli, loop seperti ini:
    // while ($movie = $movies_result->fetch_assoc()) { ... }
    // Jika $movies sudah array of arrays:
    // foreach ($movies_result as $movie) { ... }

    // Contoh jika $movies_result adalah objek hasil mysqli
    if ($movies_result instanceof mysqli_result) {
        while ($movie = $movies_result->fetch_assoc()) {
            $isInWatchlist = isset($user_watchlist[$movie['id_film']]);
            $watchlistClass = $isInWatchlist ? 'in-list' : '';
            $watchlistIcon = $isInWatchlist ? 'fa-check' : 'fa-plus';
            $watchlistText = $isInWatchlist ? 'In My List' : 'Add to List';
            
            $ratingDisplay = number_format($movie['rating_avg'] ?? 0, 1); // rating_avg dari tabel film
            $ratingCount = $movie['rating_count'] ?? 0; // rating_count dari tabel film
            
            echo '<div class="movie-card" 
                data-movie-id="' . htmlspecialchars($movie['id_film']) . '"
                data-title="' . htmlspecialchars($movie['judul']) . '" 
                data-year="' . htmlspecialchars($movie['tahun_rilis']) . '" 
                data-rating="' . htmlspecialchars($ratingDisplay) . '"
                data-description="' . htmlspecialchars($movie['deskripsi']) . '"
                data-poster-path="' . htmlspecialchars($movie['poster_url']) . '"
                onclick="showMovieDetails({
                    id: \'' . htmlspecialchars($movie['id_film']) . '\', // tambahkan id
                    title: \'' . htmlspecialchars($movie['judul']) . '\',
                    year: \'' . htmlspecialchars($movie['tahun_rilis']) . '\',
                    rating: \'' . htmlspecialchars($ratingDisplay) . '\',
                    ratingCount: \'' . htmlspecialchars($ratingCount) . '\',
                    description: \'' . htmlspecialchars(addslashes($movie['deskripsi'])) . '\',
                    img_src: \'' . htmlspecialchars($movie['poster_url']) . '\'
                })">';
            echo '<img src="' . htmlspecialchars($movie['poster_url']) . '" 
                  alt="' . htmlspecialchars($movie['judul']) . '" 
                  class="movie-poster">';
            echo '<div class="movie-info">';
            echo '<h3 class="movie-title">' . htmlspecialchars($movie['judul']) . '</h3>';
            echo '<div class="movie-year">' . htmlspecialchars($movie['tahun_rilis']) . '</div>';
            if (($movie['rating_avg'] ?? 0) > 0) {
                echo '<div class="movie-rating">
                        <i class="fas fa-star"></i>' . htmlspecialchars($ratingDisplay) . 
                        '<span class="rating-count">(' . htmlspecialchars($ratingCount) . ')</span>
                      </div>';
            }
            if (!empty($movie['deskripsi'])) {
                echo '<div class="movie-description">' . htmlspecialchars(substr($movie['deskripsi'], 0, 100)) . '...</div>';
            }
            echo '<button class="trailer-btn" onclick="event.stopPropagation(); searchTrailer(\'' . htmlspecialchars(addslashes($movie['judul'])) . '\')">
                        <i class="fas fa-play"></i> Watch Trailer
                      </button>';
             // Tombol Watchlist
             echo '<div style="display:flex;gap:8px;align-items:center;margin-top:8px;">';
             echo '<button class="watchlist-btn ' . $watchlistClass . '" 
                    onclick="event.stopPropagation(); handleWatchlistClick(this)"
                    data-movie-id="' . htmlspecialchars($movie['id_film']) . '"
                    data-movie-title="' . htmlspecialchars(addslashes($movie['judul'])) . '"
                    data-poster-path="' . htmlspecialchars($movie['poster_url']) . '">
                    <i class="fas ' . $watchlistIcon . '"></i> ' . $watchlistText . '
                  </button>';
             echo '<button class="review-link-btn" style="background:#222;color:#ffd700;border:none;padding:8px 14px;border-radius:4px;cursor:pointer;display:flex;align-items:center;gap:6px;font-weight:500;" onclick="event.stopPropagation(); openReviewModal(\'' . htmlspecialchars($movie['id_film']) . '\', \'' . htmlspecialchars(addslashes($movie['judul'])) . '\', \'' . htmlspecialchars(addslashes($movie['poster_url'])) . '\')">
                    <i class="fas fa-star"></i> Lihat Ulasan
                  </button>';
             echo '</div>';
            echo '</div></div>';
            loadReviewSummary($movie['id_film']);
        }
    } else {
        // Tambahkan penanganan jika $movies_result bukan objek hasil mysqli yang valid
        // echo "<p>Data film tidak tersedia.</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineFind - Movie Recommendations</title>
    <link rel="icon" type="image/png" href="cinefind.png">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .navbar-profile {
        margin-left: auto;
        padding-right: 2rem;
    }

    .profile-dropdown {
        position: relative;
        display: inline-block;
    }

    .profile-button {
        background: none;
        border: none;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 1rem;
        cursor: pointer;
        padding: 0.5rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
    }

    .profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #666;
        overflow: hidden;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
    }

    .profile-info span {
        font-weight: 500;
    }

    .profile-info i {
        font-size: 0.8rem;
        transition: transform 0.3s ease;
    }

    .profile-button:hover .profile-info i {
        transform: rotate(180deg);
    }

    .profile-button:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #e50914;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 0.5rem);
        background: rgba(0, 0, 0, 0.95);
        min-width: 280px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        z-index: 1000;
        animation: dropdownFade 0.2s ease;
        backdrop-filter: blur(10px);
    }

    @keyframes dropdownFade {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-menu.show {
        display: block;
    }

    .profile-preview {
        padding: 1.5rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-preview-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2.5rem;
        color: #666;
        overflow: hidden;
        border: 2px solid rgba(0, 0, 0, 0.1);
    }

    .profile-preview-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-preview-info h4 {
        color: #fff;
        margin: 0 0 0.25rem;
        font-size: 1.1rem;
        font-weight: 600;
    }

    .profile-preview-info p {
        color: #999;
        margin: 0;
        font-size: 0.9rem;
    }

    .dropdown-menu-items {
        padding: 0.5rem 0;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.8rem 1.5rem;
        color: #fff;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #e50914;
        padding-left: 2rem;
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    .dropdown-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 0.5rem 0;
    }

    /* Add styles for login state */
    .navbar-profile.not-logged-in .profile-button {
        background: #e50914;
        border-radius: 4px;
        padding: 0.5rem 1.5rem;
    }

    .navbar-profile.not-logged-in .profile-button:hover {
        background: #f40612;
    }

    .navbar-profile.not-logged-in .profile-info {
        justify-content: center;
    }
    
    .navbar-profile.not-logged-in .profile-avatar {
        display: none;
    }

    /* Tambahkan style untuk tombol login required */
    .trailer-btn.login-required {
        background-color: #666;
        cursor: not-allowed;
    }

    .trailer-btn.login-required:hover {
        background-color: #666;
    }

    .trailer-btn.login-required i {
        margin-right: 5px;
    }

    .add-list-btn {
        background-color: #2f2f2f;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        margin-top: 10px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background-color 0.3s ease;
    }

    .add-list-btn:hover {
        background-color: #404040;
    }

    .add-list-btn i {
        font-size: 1rem;
    }

    .watchlist-btn {
        background: linear-gradient(135deg, #e50914, #f40612);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(229, 9, 20, 0.3);
    }

    .watchlist-btn:hover {
        background: linear-gradient(135deg, #f40612, #e50914);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(229, 9, 20, 0.4);
    }

    .watchlist-btn.in-list {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    }

    .watchlist-btn.in-list:hover {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
        box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
    }

    .watchlist-btn i {
        font-size: 1.1rem;
    }

    .notification {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        background: rgba(26, 26, 46, 0.95);
        backdrop-filter: blur(10px);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(1, 1, 1, 0.1);
        transform: translateY(100%);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
    }

    .notification.show {
        transform: translateY(0);
        opacity: 1;
    }

    .notification.success {
        border-left: 4px solid #2ecc71;
    }

    .notification.error {
        border-left: 4px solid #e50914;
    }

    .notification i {
        font-size: 1.2rem;
    }

    .notification.success i {
        color: #2ecc71;
    }

    .notification.error i {
        color: #e50914;
    }

    .movie-actions {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
        justify-content: center;
    }

    .hero {
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.9)), url('path/to/your/background-image.jpg');
        background-size: cover;
        background-position: center;
        padding: 4rem 2rem;
        color: #fff;
        position: relative;
    }

    .hero-content {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .hero-content h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .hero-content p {
        font-size: 1.2rem;
        color: #e0e0e0;
        margin-bottom: 2rem;
    }

    .search-container {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 2rem;
    }

    .hero-search {
        width: 100%;
        max-width: 500px;
        padding: 12px 20px;
        border: none;
        border-radius: 25px;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        font-size: 1rem;
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
    }

    .hero-search::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .hero-search:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.15);
        box-shadow: 0 0 15px rgba(229, 9, 20, 0.3);
    }

    .search-button {
        padding: 12px 30px;
        border: none;
        border-radius: 25px;
        background: #e50914;
        color: #fff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-button:hover {
        background: #f40612;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(229, 9, 20, 0.4);
    }

    .navbar-filters {
        background: rgba(0, 0, 0, 0.5);
        padding: 1rem;
        border-radius: 10px;
        backdrop-filter: blur(10px);
        margin-top: 1rem;
    }

    .filter-container {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        position: relative;
    }

    .filter-group select {
        padding: 10px 35px 10px 15px;
        border: none;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        font-size: 0.95rem;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        transition: all 0.3s ease;
    }

    .filter-group select:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .filter-group select:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 0 10px rgba(229, 9, 20, 0.2);
    }

    .filter-group::after {
        content: '\f078';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #fff;
        pointer-events: none;
    }

    .filter-group select option {
        background: #181818;
        color: #fff;
        padding: 10px;
    }

    @media (max-width: 768px) {
        .hero {
            padding: 3rem 1rem;
        }

        .hero-content h1 {
            font-size: 2rem;
        }

        .hero-content p {
            font-size: 1rem;
        }

        .search-container {
            flex-direction: column;
            align-items: center;
        }

        .hero-search {
            width: 100%;
            margin-bottom: 10px;
        }

        .search-button {
            width: 100%;
        }

        .filter-container {
            flex-direction: column;
            align-items: center;
        }

        .filter-group {
            width: 100%;
            max-width: 300px;
        }

        .filter-group select {
            width: 100%;
        }
    }

    /* Add styles for review section */
    .review-section {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .review-section h3 {
        color: #fff;
        margin-bottom: 1rem;
    }

    .review-form {
        background: rgba(255, 255, 255, 0.05);
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .review-form textarea {
        width: 100%;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        color: #fff;
        padding: 1rem;
        margin-bottom: 1rem;
        resize: vertical;
    }

    .rating-input {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .rating-input span {
        color: #fff;
    }

    .star-rating {
        display: flex;
        gap: 0.5rem;
    }

    .star-rating i {
        color: #666;
        cursor: pointer;
        transition: color 0.3s ease;
    }

    .star-rating i:hover,
    .star-rating i.active {
        color: #e50914;
    }

    .submit-review-btn {
        background: #e50914;
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .submit-review-btn:hover {
        background: #f40612;
        transform: translateY(-2px);
    }

    .reviews-list {
        margin-top: 2rem;
    }

    .review-item {
        background: rgba(255, 255, 255, 0.05);
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .reviewer-name {
        color: #fff;
        font-weight: 600;
    }

    .review-date {
        color: #999;
        font-size: 0.9rem;
    }

    .review-rating {
        color: #e50914;
        margin-bottom: 0.5rem;
    }

    .review-content {
        color: #ccc;
        line-height: 1.5;
    }

    /* Review Button Styles */
    .review-button-container {
        text-align: center;
        margin: 20px 0;
        padding: 10px;
    }

    .review-button {
        background: linear-gradient(135deg, #e50914, #f40612);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 25px;
        font-size: 1.1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(229, 9, 20, 0.3);
    }

    .review-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(229, 9, 20, 0.4);
    }

    .review-button i {
        font-size: 1.2rem;
    }

    /* Review Section Styles */
    .review-section {
        background: rgba(0, 0, 0, 0.8);
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }

    .review-section h3 {
        color: #fff;
        font-size: 1.5rem;
        margin-bottom: 20px;
        text-align: center;
    }

    .review-form {
        background: rgba(255, 255, 255, 0.05);
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .review-form textarea {
        width: 100%;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: #fff;
        padding: 15px;
        margin-bottom: 15px;
        resize: vertical;
        font-size: 1rem;
    }

    .review-form textarea:focus {
        outline: none;
        border-color: #e50914;
        box-shadow: 0 0 10px rgba(229, 9, 20, 0.3);
    }

    .rating-input {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .rating-input span {
        color: #fff;
        font-size: 1.1rem;
    }

    .star-rating {
        display: flex;
        gap: 8px;
    }

    .star-rating i {
        color: #666;
        cursor: pointer;
        font-size: 1.5rem;
        transition: all 0.3s ease;
    }

    .star-rating i:hover,
    .star-rating i.active {
        color: #e50914;
        transform: scale(1.1);
    }

    .submit-review-btn {
        background: #e50914;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 25px;
        font-size: 1.1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .submit-review-btn:hover {
        background: #f40612;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(229, 9, 20, 0.3);
    }

    .reviews-list {
        margin-top: 30px;
    }

    .review-item {
        background: rgba(255, 255, 255, 0.05);
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .review-item:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateY(-2px);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .reviewer-name {
        color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .review-date {
        color: #999;
        font-size: 0.9rem;
    }

    .review-rating {
        color: #e50914;
        margin-bottom: 10px;
        font-size: 1.2rem;
    }

    .review-content {
        color: #ccc;
        line-height: 1.6;
        font-size: 1rem;
    }

    .movie-rating-summary {
        margin: 0.5em 0;
        font-size: 1.1em;
        display: flex;
        align-items: center;
        gap: 0.5em;
    }

    .review-link-btn {
        background: #222;
        color: #ffd700;
        border: none;
        padding: 8px 14px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .review-link-btn:hover {
        background: #333;
    }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <?php include 'navbar.php'; ?>

    <!-- About Modal -->
    <div id="aboutModal" class="modal">
        <div class="modal-content about-modal">
            <span class="close" onclick="closeAboutModal()">&times;</span>
            <div class="about-content">
                <h2>About CineFind</h2>
                <p>
                    CineFind is your ultimate destination for discovering and exploring movies. Our platform is designed to help you find your next favorite film through personalized recommendations and comprehensive movie information.
                </p>
                
                <h3>Key Features:</h3>
                <ul>
                    <li>
                        <i class="fas fa-search"></i>
                        <div>
                            <strong>Smart Search:</strong> Find movies easily with our advanced search functionality that filters by genre, year, and rating.
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-star"></i>
                        <div>
                            <strong>Personalized Recommendations:</strong> Get movie suggestions tailored to your preferences and watching history.
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-list"></i>
                        <div>
                            <strong>My List:</strong> Create and manage your personal watchlist of movies you want to see.
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-fire"></i>
                        <div>
                            <strong>New & Popular:</strong> Stay updated with the latest releases and trending movies.
                        </div>
                    </li>
                </ul>

                <h3>How It Works:</h3>
                <ol>
                    <li>Browse through our extensive movie collection</li>
                    <li>Use filters to find movies that match your interests</li>
                    <li>Save movies to your watchlist for later</li>
                    <li>Rate and review movies to improve recommendations</li>
                </ol>

                <p>
                    Join our community of movie enthusiasts and discover your next favorite film with CineFind!
                </p>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <header class="hero">
        <div class="hero-content">
            <h1>Selamat Datang di CineFind, <?php echo htmlspecialchars($nama_display); ?>!</h1>
            <p>Temukan film favorit Anda berdasarkan rating pengguna</p>
            <div class="search-container">
                <input type="text" placeholder="Cari film..." class="hero-search" id="searchInputGlobal">
                <button class="search-button" id="searchButtonGlobal">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
            <div class="navbar-filters">
                <div class="filter-container">
                    <div class="filter-group">
                        <select id="genreFilter">
                            <option value="">Semua Genre</option>
                            <option value="action">Action</option>
                            <option value="comedy">Comedy</option>
                            <option value="drama">Drama</option>
                            <option value="horror">Horror</option>
                            <option value="romance">Romance</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select id="yearFilter">
                            <option value="">Semua Tahun</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                            <option value="2020">2020</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select id="ratingFilter">
                            <option value="">Semua Rating</option>
                            <option value="9">9+ Bintang</option>
                            <option value="8">8+ Bintang</option>
                            <option value="7">7+ Bintang</option>
                            <option value="6">6+ Bintang</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Featured Movies Section -->
        <section id="featured-movies" class="featured-movies" style="text-align: center; margin: auto;">
            <h2>Featured Movies</h2>
            <div class="movie-grid" id="featuredMovies">
                <!-- Sample Featured Movies -->
                <div class="movie-card">
                    <img src="https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg" alt="FAST X" class="movie-poster">
                    <div class="movie-info">
                        <h3 class="movie-title">FAST X</h3>
                        <div class="movie-year">2023</div>
                    </div>
                </div>
                <div class="movie-card">
                    <img src="images\oppenheimer.jpeg" alt="Oppenheimer" class="movie-poster">
                    <div class="movie-info">
                        <h3 class="movie-title">Oppenheimer</h3>
                        <div class="movie-year">2023</div>
                    </div>
                </div>
                <div class="movie-card">
                    <img src="https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg" alt="Avengers: Endgame" class="movie-poster">
                    <div class="movie-info">
                        <h3 class="movie-title">Avengers: Endgame</h3>
                        <div class="movie-year">2019</div>
                    </div>
                </div>
                <div class="movie-card">
                    <img src="images\Spider-Man_ No Way Home.jpeg" alt="Spider-Man: No Way Home" class="movie-poster">
                    <div class="movie-info">
                        <h3 class="movie-title">Spider-Man: No Way Home</h3>
                        <div class="movie-year">2021</div>
                    </div>
                </div>
            </div>
            <button class="load-more-btn" id="loadMoreFeatured">Load More</button>
        </section>

        <!-- Indonesian Movies Section -->
        <section class="indonesian-movies" style="text-align: center; margin: auto;">
            <h2>Indonesian Movies</h2>
            <div class="movie-grid" id="indonesianMovies">
                <!-- Indonesian movies will be loaded here -->
            </div>
            <button class="load-more-btn" id="loadMoreIndonesian">Load More</button>
        </section>

        <!-- Top Rated Movies Section -->
        <section class="top-rated-movies" style="text-align: center; margin: auto;">
            <h2>Top Rated Movies</h2>
            <div class="movie-grid" id="topRatedMovies">
                <!-- Top rated movies will be loaded here -->
            </div>
            <button class="load-more-btn" id="loadMoreTopRated">Load More</button>
        </section>

        <!-- Coming Soon Movies Section -->
        <section class="coming-soon-movies" style="text-align: center; margin: auto;">
            <h2>Coming Soon</h2>
            <div class="movie-grid" id="upcomingMovies">
                <!-- Coming soon movies will be loaded here -->
            </div>
            <button class="load-more-btn" id="loadMoreUpcoming">Load More</button>
        </section>
    </main>

    <!-- Movie Detail Modal -->
    <div id="movieModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeMovieModal()">×</span>
        <div class="modal-body">
            </div>
        <div id="review-container"></div>
    </div>
</div>
            <!-- Review Section -->
            <div class="review-section" id="reviewSection" style="display:none;">
                <h3>Write Your Review</h3>
                <div class="review-form">
                    <textarea id="reviewText" placeholder="Share your thoughts about this movie..." rows="4"></textarea>
                    <div class="rating-input">
                        <span>Your Rating:</span>
                        <div class="star-rating">
                            <i class="fas fa-star" data-rating="1"></i>
                            <i class="fas fa-star" data-rating="2"></i>
                            <i class="fas fa-star" data-rating="3"></i>
                            <i class="fas fa-star" data-rating="4"></i>
                            <i class="fas fa-star" data-rating="5"></i>
                        </div>
                    </div>
                    <button class="submit-review-btn" onclick="submitReview()">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                </div>
                <div class="reviews-list" id="reviewsList">
                    <!-- Reviews will be dynamically added here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About CineFind</h3>
                <p>Your personalized movie recommendation platform</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                
                    <li><a href="pengguna/my-watchlist.php">My Watchlist</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-x-twitter"></i></a>
                    <a href="https://www.instagram.com/cinefind_film/" target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 CineFind. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
    // Update the profile information functions
    function updateProfileInfo() {
        const profileUsername = document.getElementById('profileUsername');
        const previewName = document.getElementById('previewName');
        const previewEmail = document.getElementById('previewEmail');
        
        // These values are now set from PHP session data
        profileUsername.textContent = '<?php echo htmlspecialchars($user_data['username']); ?>';
        previewName.textContent = '<?php echo htmlspecialchars($user_data['username']); ?>';
        previewEmail.textContent = '<?php echo htmlspecialchars($user_data['role_email']); ?>';
    }

    // Toggle profile dropdown
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.profile-button') && !event.target.matches('.profile-button *')) {
            const dropdowns = document.getElementsByClassName('dropdown-menu');
            for (let dropdown of dropdowns) {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }
    }

    // Call updateProfileInfo when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateProfileInfo();
    });

    // Search trailer function
    function searchTrailer(movieTitle) {
        const searchQuery = encodeURIComponent(movieTitle + ' trailer');
        window.open(`https://www.youtube.com/results?search_query=${searchQuery}`, '_blank');
    }

    // Add this new function to handle the click event
    function handleWatchlistClick(buttonElement) {
        if (!buttonElement) return;
        
        const movieId = buttonElement.dataset.movieId;
        const movieTitle = buttonElement.dataset.movieTitle;
        const posterPath = buttonElement.dataset.posterPath;
        
        if (!movieId || !movieTitle || !posterPath) {
            showNotification('Movie title and poster path are required for adding to watchlist', 'error');
            return;
        }
        
        toggleWatchlist(movieId, movieTitle, posterPath, buttonElement);
    }

    async function toggleWatchlist(movieId, movieTitle, posterPath, buttonElement) {
        if (!buttonElement || !buttonElement.classList) {
            console.error('Invalid button element');
            return;
        }

        try {
            const isInList = buttonElement.classList.contains('in-list');
            const action = isInList ? 'remove' : 'add';
            
            buttonElement.style.opacity = '0.7';
            buttonElement.style.pointerEvents = 'none';

            const formData = new FormData();
            formData.append('action', action);
            formData.append('movie_id', movieId);
            if (action === 'add') {
                formData.append('movie_title', movieTitle);
                formData.append('poster_path', posterPath);
            }

            const response = await fetch('watchlist_handler.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                if (action === 'add') {
                    buttonElement.classList.add('in-list');
                    buttonElement.innerHTML = '<i class="fas fa-check"></i> In My List';
                    showNotification('Added to watchlist', 'success');
                } else {
                    buttonElement.classList.remove('in-list');
                    buttonElement.innerHTML = '<i class="fas fa-plus"></i> Add to List';
                    showNotification('Removed from watchlist', 'success');
                }
            } else {
                throw new Error(data.message || 'Failed to update watchlist');
            }
        } catch (error) {
            console.error('Error updating watchlist:', error);
            showNotification(error.message || 'Failed to update watchlist. Please try again.', 'error');
        } finally {
            buttonElement.style.opacity = '1';
            buttonElement.style.pointerEvents = 'auto';
        }
    }

    // Initialize userWatchlist array from PHP
    const userWatchlist = <?php echo json_encode($user_watchlist); ?>;

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);

        // Trigger animation
        setTimeout(() => notification.classList.add('show'), 10);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Update displayMovieCards function to include watchlist status
    function displayMovieCards(movies) {
        const movieGrid = document.getElementById('movieGrid');
        movieGrid.innerHTML = '';

        movies.forEach(movie => {
            const isInWatchlist = isset($user_watchlist[$movie['id_film']]);
            const watchlistClass = isInWatchlist ? 'in-list' : '';
            const watchlistIcon = isInWatchlist ? 'fa-check' : 'fa-plus';
            const watchlistText = isInWatchlist ? 'In My List' : 'Add to List';

            const movieCard = document.createElement('div');
            movieCard.className = 'movie-card';
            movieCard.innerHTML = `
                <img src="${movie.poster}" alt="${movie.judul}" class="movie-poster" loading="lazy">
                <div class="movie-info">
                    <h3 class="movie-title">${movie.judul}</h3>
                    <div class="movie-year">${movie.tahun_rilis}</div>
                    <div class="movie-rating-summary" id="review-summary-${movie.id}">
                        <!-- Ringkasan ulasan akan diisi JS -->
                    </div>
                    <div class="movie-description">${movie.deskripsi.substring(0, 100)}...</div>
                    <div class="movie-actions">
                        <button class="trailer-btn" onclick="searchTrailer('${movie.judul}')">
                            <i class="fas fa-play"></i> Watch Trailer
                        </button>
                    </div>
                </div>
            `;
            movieGrid.appendChild(movieCard);
            loadReviewSummary(movie.id);
        });
    }


    // Variabel global untuk menyimpan ID film saat ini di modal
let currentMovieId = null;
let currentRating = 0;

// Fungsi utama untuk menampilkan detail film + review
function showMovieDetails(movie) {
    console.log('showMovieDetails', movie);
    currentMovieId = movie.id;
    const modal = document.getElementById('movieModal');
    const modalBody = modal.querySelector('.modal-body');

    // Isi detail film dan trailer
    modalBody.innerHTML = `
        <div class="movie-details">
            <img src="${movie.img_src}" alt="${movie.title}" class="modal-poster">
            <div class="modal-info">
                <h2>${movie.title}</h2>
                <div class="modal-year">${movie.year}</div>
                <div class="modal-rating"><i class="fas fa-star"></i>${movie.rating}</div>
                <div class="modal-description">${movie.description}</div>
            </div>
        </div>
        <div class="trailer" style="margin-top: 2rem;">
            <h3>Trailer</h3>
            <div id="trailerContainer"></div>
        </div>
        <div class="movie-reviews-section" style="margin-top:2rem;">
            <div class="movie-actions" style="text-align:right;">
                <button class="review-button" onclick="toggleReviewSection()">
                    <i class="fas fa-comment-alt"></i> Tulis Ulasan
                </button>
            </div>
            <div class="review-section" id="reviewSection" style="display:block;">
                <h3>Ulasan Anda</h3>
                <form id="reviewForm">
                    <textarea name="komentar" placeholder="Bagikan pendapat Anda tentang film ini..." rows="4" required></textarea>
                    <div class="rating-input">
                        <span>Beri Rating:</span>
                        <div class="star-rating">
                            <i class="fas fa-star" data-rating="1"></i>
                            <i class="fas fa-star" data-rating="2"></i>
                            <i class="fas fa-star" data-rating="3"></i>
                            <i class="fas fa-star" data-rating="4"></i>
                            <i class="fas fa-star" data-rating="5"></i>
                        </div>
                    </div>
                    <button type="submit" class="submit-review-btn">
                        <i class="fas fa-paper-plane"></i> Kirim Ulasan
                    </button>
                </form>
            </div>
            <div class="reviews-list" id="reviewsList"></div>
        </div>
    `;

    modal.style.display = 'block';
    setupReviewForm(currentMovieId);
    loadMovieReviews(currentMovieId);
}

// Tampilkan/sembunyikan form ulasan
function toggleReviewSection() {
    const reviewSection = document.getElementById('reviewSection');
    if (reviewSection.style.display === 'none' || reviewSection.style.display === '') {
        reviewSection.style.display = 'block';
    } else {
        reviewSection.style.display = 'none';
    }
}

// Setup event bintang & submit form ulasan
function setupReviewForm(movieId) {
    const form = document.getElementById('reviewForm');
    const stars = form.querySelectorAll('.star-rating i');
    currentRating = 0;
    updateStarDisplay(stars, 0);

    stars.forEach(star => {
        star.onclick = function() {
            currentRating = parseInt(this.dataset.rating);
            updateStarDisplay(stars, currentRating);
        };
    });

    form.onsubmit = async function(e) {
        e.preventDefault();
        const komentar = form.querySelector('textarea[name=\"komentar\"]').value.trim();
        if (currentRating === 0 || !komentar) {
            showNotification('Harap berikan rating dan tulis ulasan Anda.', 'error');
            return;
        }
        const formData = new FormData();
        formData.append('movie_id', movieId);
        formData.append('rating', currentRating);
        formData.append('komentar', komentar);

        try {
            const response = await fetch('pengguna/review_handler.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            showNotification(result.message, result.success ? 'success' : 'error');
            if (result.success) {
                form.reset();
                currentRating = 0;
                updateStarDisplay(stars, 0);
                toggleReviewSection();
                loadMovieReviews(movieId);
            }
        } catch (error) {
            showNotification('Terjadi kesalahan saat mengirim ulasan.', 'error');
        }
    };
}

// Update tampilan bintang rating
function updateStarDisplay(stars, rating) {
    stars.forEach(star => {
        star.classList.toggle('active', parseInt(star.dataset.rating) <= rating);
    });
}

// Load review dari backend
async function loadMovieReviews(movieId) {
    const reviewsList = document.getElementById('reviewsList');
    reviewsList.innerHTML = '<p>Memuat ulasan...</p>';
    try {
        const response = await fetch(`pengguna/review_handler.php?movie_id=${movieId}`);
        const result = await response.json();
        if (result.success && result.reviews.length > 0) {
            reviewsList.innerHTML = result.reviews.map(review => `
                <div class="review-item">
                    <div class="review-header">
                        <span class="reviewer-name">${review.username}</span>
                        <span class="review-date">${(new Date(review.created_at)).toLocaleDateString()}</span>
                    </div>
                    <div class="review-rating">${'★'.repeat(review.rating)}${'☆'.repeat(5 - review.rating)}</div>
                    <div class="review-content">${review.komentar}</div>
                </div>
            `).join('');
        } else {
            reviewsList.innerHTML = '<p>Belum ada ulasan untuk film ini. Jadilah yang pertama!</p>';
        }
    } catch (error) {
        reviewsList.innerHTML = '<p>Gagal memuat ulasan.</p>';
    }
}

// Notifikasi
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span>`;
    document.body.appendChild(notification);
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function loadReviewSummary(movieId) {
    fetch(`pengguna/review_handler.php?summary=1&movie_id=${movieId}`)
        .then(res => res.json())
        .then(data => {
            const summaryDiv = document.getElementById(`review-summary-${movieId}`);
            if (data.success) {
                summaryDiv.innerHTML = `
                    <span title="Rata-rata rating">
                        <i class="fas fa-star" style="color:#ffd700"></i> ${data.avg_rating}
                    </span>
                    <span style="color:#aaa;font-size:0.9em;">(${data.total_review} ulasan)</span>
                `;
            } else {
                summaryDiv.innerHTML = `<span style="color:#aaa;">Belum ada ulasan</span>`;
            }
        });
}

function openReviewModal(movieId, title, posterUrl) {
    console.log('openReviewModal', movieId, title, posterUrl);
    showMovieDetails({
        id: movieId,
        title: title,
        year: '',
        rating: '',
        description: '',
        img_src: posterUrl
    });
    setTimeout(() => {
        const reviewSection = document.getElementById('reviewSection');
        if (reviewSection) reviewSection.style.display = 'block';
    }, 300);
}
    </script>
</body>
</html> 