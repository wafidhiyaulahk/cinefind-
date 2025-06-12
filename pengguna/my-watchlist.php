<?php
session_start();
// 1. Standarisasi path ke database.php
require_once '../config/database.php'; // Diasumsikan path ini benar

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// $user_id sekarang diasumsikan adalah id_pengguna berdasarkan pernyataan Anda
$user_id_for_watchlist = $_SESSION['user_id'];

// ==============================================================================
// AMBIL DATA PENGGUNA UNTUK NAVBAR
// CATATAN PENTING: Bagian ini MUNGKIN PERLU PENYESUAIAN jika $_SESSION['user_id']
// benar-benar diubah menjadi id_pengguna secara global.
// Kueri di bawah ini mengasumsikan $_SESSION['user_id'] adalah id_role.
// Jika $_SESSION['user_id'] adalah id_pengguna, Anda perlu cara lain untuk mendapatkan
// username, email, dan foto_profil yang terkait dengan role untuk navbar,
// atau ubah cara navbar mendapatkan data.
// Untuk sementara, kita asumsikan navbar.php akan menangani logikanya sendiri
// atau Anda akan menyesuaikan ini nanti.
// ==============================================================================
$user_id_for_navbar = $_SESSION['user_id'];
$user_data_nav = null;
if ($conn) { // Hanya jalankan jika koneksi berhasil
    $user_nav_query = "SELECT r.username, r.email, p.foto_profil 
                       FROM role r 
                       LEFT JOIN pengguna p ON r.id_role = p.role_id 
                       WHERE r.id_role = ?"; // Kueri ini mengharapkan id_role
    $stmt_user_nav = $conn->prepare($user_nav_query);
    if ($stmt_user_nav) {
        // Jika $_SESSION['user_id'] adalah id_pengguna, maka Anda perlu query yang berbeda
        // untuk mendapatkan role_id terlebih dahulu, atau navbar.php harus punya logika sendiri.
        // Untuk contoh ini, kita tetap bind $user_id_for_navbar, namun ini perlu diperhatikan.
        $stmt_user_nav->bind_param("i", $user_id_for_navbar);
        $stmt_user_nav->execute();
        $user_nav_result = $stmt_user_nav->get_result();
        $user_data_nav = $user_nav_result->fetch_assoc();
        $stmt_user_nav->close();
    } else {
        error_log("Gagal mempersiapkan statement untuk user_nav_query: " . $conn->error);
    }
}
$default_avatar = 'cinefind.png'; // Pastikan path ini benar dari direktori pengguna
$user_avatar = (!empty($user_data_nav['foto_profil'])) ? '../' . htmlspecialchars($user_data_nav['foto_profil']) : $default_avatar;
// ==============================================================================
// AKHIR BAGIAN DATA NAVBAR
// ==============================================================================


// 2. Ambil daftar tonton pengguna menggunakan LEFT JOIN dan asumsi id_pengguna
//    Pastikan tabel `watchlist` memiliki kolom `id_pengguna`
//    dan `watchlist_handler.php` menyimpan judul & poster ke tabel watchlist.
$watchlist_items = [];
$watchlist_count = 0;

// ... (kode sebelumnya) ...
if ($conn) { // Hanya jalankan jika koneksi berhasil
    $watchlist_query = "SELECT 
                            w.id AS watchlist_id, 
                            w.movie_id AS tmdb_movie_id, 
                            w.movie_title AS watchlist_movie_title, 
                            w.poster_path AS watchlist_poster_path, 
                            w.added_at,
                            f.id_film AS local_film_id,
                            f.judul AS local_film_judul,
                            f.poster_url AS local_film_poster_url,
                            f.tahun_rilis AS local_film_tahun_rilis,
                            f.deskripsi AS local_film_deskripsi,
                            f.rating_avg as display_rating, -- Menggunakan rating_avg yang sudah ada
                            GROUP_CONCAT(DISTINCT fg.genre) as local_film_genres
                        FROM 
                            watchlist w
                        LEFT JOIN 
                            film f ON w.movie_id = f.id_film -- Asumsi w.movie_id (TMDB ID) cocok dengan f.id_film
                        LEFT JOIN 
                            film_genre fg ON f.id_film = fg.id_film
                        WHERE 
                            w.user_id = ?  -- <<< PERUBAHAN DI SINI: dari w.id_pengguna menjadi w.user_id
                        GROUP BY 
                            w.id -- GROUP BY pada primary key dari watchlist
                        ORDER BY 
                            w.added_at DESC";

    $stmt_watchlist = $conn->prepare($watchlist_query);

    if ($stmt_watchlist) {
        // $user_id_for_watchlist sudah diisi dengan $_SESSION['user_id'] (yaitu role_id)
        $stmt_watchlist->bind_param("i", $user_id_for_watchlist); 
        $stmt_watchlist->execute();
        $watchlist_result = $stmt_watchlist->get_result();
        while ($row = $watchlist_result->fetch_assoc()) {
            $watchlist_items[] = $row;
        }
        $watchlist_count = count($watchlist_items);
        $stmt_watchlist->close();
    } else {
        error_log("Gagal mempersiapkan statement untuk watchlist_query: " . $conn->error);
    }
} 
// ... (kode setelahnya) ...

// Tambahkan fungsi untuk mendapatkan data streaming
function getStreamingAvailability($movieId) {
    $api_key = 'ba6f7d3b063751fb2bea48683e263f63';
    $url = "https://api.themoviedb.org/3/movie/{$movieId}/watch/providers?api_key={$api_key}";
    
    $response = file_get_contents($url);
    if ($response === false) {
        return [];
    }
    
    $data = json_decode($response, true);
    $providers = [];
    
    if (isset($data['results']['ID']['flatrate'])) {
        foreach ($data['results']['ID']['flatrate'] as $provider) {
            $providers[] = [
                'name' => $provider['provider_name'],
                'logo' => $provider['logo_path']
            ];
        }
    }
    
    return $providers;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Watchlist - CineFind</title>
    <link rel="icon" type="image/png" href="cinefind.png">
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="css/style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Salin semua CSS spesifik dari my-watchlist.php yang lama ke sini */
        /* Atau pastikan semua style yang relevan ada di styles.css / css/style.css */

        body {
            background-color: #141414; /* Warna latar belakang Netflix-like */
            color: #fff;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .watchlist-container {
            padding-top: 80px; /* Sesuaikan dengan tinggi navbar Anda */
            padding-bottom: 3rem;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 180px); /* Sesuaikan footer height jika ada */
        }

        .watchlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding: 1.5rem 2rem; /* Disesuaikan paddingnya */
            background: rgba(20, 20, 20, 0.8); /* Sedikit transparan */
            border-radius: 8px; /* Lebih subtle */
        }

        .watchlist-title {
            font-size: 2rem; /* Sedikit lebih kecil */
            color: #fff;
            margin: 0;
            font-weight: 600; /* Sedikit lebih ringan */
        }

        .watchlist-count {
            color: #b3b3b3; /* Abu-abu lebih terang */
            font-size: 1rem;
            margin-top: 0.25rem; /* Lebih dekat dengan judul */
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .watchlist-count i {
            color: #e50914; /* Warna merah Cinefind */
            font-size: 1.1rem;
        }

        .movie-grid {
            display: grid;
            /* Menggunakan minmax untuk responsivitas yang lebih baik */
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 1.5rem; /* Sedikit lebih rapat */
            padding: 0 0.5rem; /* Padding untuk grid */
        }

        .watchlist-movie-card {
            background: #181818; /* Warna kartu yang lebih gelap */
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            transition: transform 0.2s ease-out, box-shadow 0.2s ease-out;
        }
        .watchlist-movie-card:hover {
            transform: scale(1.03); /* Efek hover yang lebih subtle */
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .watchlist-movie-card .movie-poster {
            width: 100%;
            /* Menggunakan aspect-ratio untuk poster yang konsisten */
            aspect-ratio: 2 / 3; 
            object-fit: cover;
            display: block; /* Menghilangkan whitespace di bawah img */
        }
        .watchlist-movie-card .movie-info {
            padding: 1rem;
        }
        .watchlist-movie-card .movie-title {
            font-size: 1.1rem; /* Ukuran judul yang pas */
            color: #fff;
            margin: 0 0 0.5rem 0;
            font-weight: 500;
            /* Mencegah judul terlalu panjang */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .movie-year, .movie-rating, .movie-genres, .movie-description {
            font-size: 0.85rem;
            color: #a0a0a0; /* Warna teks info yang lebih lembut */
            margin-bottom: 0.3rem;
            line-height: 1.4;
        }
        .movie-description {
             /* Batasi deskripsi menjadi 2 baris */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: calc(0.85rem * 1.4 * 2); /* Perkiraan tinggi 2 baris */
        }
        .movie-actions {
            display: flex;
            gap: 0.5rem; /* Jarak antar tombol */
            margin-top: 1rem;
        }
        .action-btn {
            flex: 1;
            padding: 0.6rem 0; /* Padding vertikal lebih kecil */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            font-size: 0.8rem; /* Font tombol lebih kecil */
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .remove-btn {
            background-color: #e50914; /* Tombol hapus */
            color: white;
        }
        .remove-btn:hover {
            background-color: #f40612;
        }
        .trailer-btn {
            background-color: rgba(109, 109, 110, 0.7); /* Tombol trailer */
            color: white;
        }
        .trailer-btn:hover {
            background-color: rgba(109, 109, 110, 0.5);
        }

        .empty-watchlist {
            text-align: center;
            padding: 3rem 1.5rem;
            background: #181818;
            border-radius: 8px;
            margin: 2rem auto;
            max-width: 500px; /* Lebih ramping */
        }
        .empty-watchlist i.main-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: #e50914;
        }
        .empty-watchlist p.empty-title {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: #fff;
            font-weight: 500;
        }
        .empty-watchlist p.empty-subtitle {
            font-size: 1rem;
            color: #a0a0a0;
            margin-bottom: 1.5rem;
        }
        .browse-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: #e50914;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        .browse-btn:hover {
            background-color: #f40612;
        }

        /* Notifikasi dari css/style.css (jika ada) atau definisikan di sini jika belum */
        .notification {
            position: fixed;
            bottom: 20px; /* Disesuaikan dari my-watchlist.php lama */
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            background: #333; /* Warna notifikasi yang lebih gelap */
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); /* Shadow lebih gelap */
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
            z-index: 2000; /* Pastikan di atas elemen lain */
            border-left: 4px solid transparent; /* Untuk tipe notifikasi */
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification i {
            font-size: 20px;
        }

        .notification.success {
            border-left-color: #4CAF50; /* Hijau untuk sukses */
        }
        .notification.success i {
            color: #4CAF50;
        }
        .notification.error {
            border-left-color: #f44336; /* Merah untuk error */
        }
        .notification.error i {
            color: #f44336;
        }
        .notification.info {
            border-left-color: #2196F3; /* Biru untuk info */
        }
        .notification.info i {
            color: #2196F3;
        }

        /* Confirm Dialog Styles */
        .confirm-dialog {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85); /* Lebih gelap */
            backdrop-filter: blur(3px); /* Blur lebih ringan */
            display: none; /* Defaultnya disembunyikan */
            align-items: center;
            justify-content: center;
            z-index: 3000; /* Di atas notifikasi */
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .confirm-dialog.show {
            display: flex;
            opacity: 1;
        }

        .confirm-dialog-content {
            background: #181818; /* Warna konten dialog */
            border-radius: 8px;
            padding: 1.5rem; /* Padding lebih kecil */
            max-width: 380px; /* Lebih ramping */
            width: 90%;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5);
            transform: scale(0.95);
            transition: transform 0.2s ease;
        }
        .confirm-dialog.show .confirm-dialog-content {
            transform: scale(1);
        }

        .confirm-dialog h3 {
            color: #fff;
            font-size: 1.3rem; /* Judul lebih kecil */
            margin: 0 0 0.75rem;
            font-weight: 500;
        }

        .confirm-dialog p {
            color: #b3b3b3;
            margin: 0 0 1.25rem; /* Jarak bawah lebih besar */
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .confirm-dialog-buttons {
            display: flex;
            gap: 0.75rem; /* Jarak antar tombol */
            justify-content: flex-end;
        }

        .confirm-dialog button {
            padding: 0.6rem 1.2rem; /* Tombol lebih kecil */
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: background-color 0.2s ease;
            font-size: 0.85rem;
        }

        .cancel-btn {
            background-color: rgba(109, 109, 110, 0.7);
            color: #fff;
        }
        .cancel-btn:hover {
            background-color: rgba(109, 109, 110, 0.5);
        }
        .confirm-btn {
            background-color: #e50914;
            color: white;
        }
        .confirm-btn:hover {
            background-color: #f40612;
        }

        .platform-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(229, 9, 20, 0.9);
            color: #fff;
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .platform-label.streaming-platform {
            background: rgba(44, 44, 44, 0.9);
            border: 1px solid #e50914;
        }

        .platform-label.streaming-platform:hover {
            background: #e50914;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(229, 9, 20, 0.3);
        }

        .platform-label i {
            font-size: 1rem;
        }

        .movie-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 12px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .trailer-btn {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .trailer-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .remove-btn {
            background: rgba(229, 9, 20, 0.9);
            color: #fff;
        }

        .remove-btn:hover {
            background: #e50914;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(229, 9, 20, 0.3);
        }

        .action-btn i {
            font-size: 0.9rem;
        }

        /* Animasi hover untuk kartu film */
        .watchlist-movie-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .watchlist-movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .watchlist-movie-card:hover .movie-poster {
            transform: scale(1.05);
        }

        .movie-poster {
            transition: transform 0.3s ease;
            border-radius: 8px 8px 0 0;
        }

        /* Responsive design improvements */
        @media (max-width: 768px) {
            .movie-actions {
                grid-template-columns: 1fr;
            }
            
            .action-btn {
                padding: 10px;
            }
            
            .platform-label {
                font-size: 0.75rem;
                padding: 3px 10px;
            }
        }

    </style>
</head>
<body>
    <?php
    if ($conn) { // Hanya include navbar jika koneksi utama berhasil
        // Jika $user_data_nav tidak ter-set karena error query navbar,
        // navbar.php mungkin perlu penanganan error sendiri atau nilai default.
        include 'navlist.php';
        // Move the connection close here, after navbar is included
        $conn->close();
    } else {
        echo "<p style='text-align:center; color:white; padding-top:100px;'>Tidak dapat memuat navigasi. Masalah koneksi basis data.</p>";
    }
    ?>

    <div id="aboutModal" class="modal">
        <div class="modal-content about-modal">
            <span class="close" onclick="closeAboutModal()">×</span>
            <div class="about-content">
                <h2>About CineFind</h2>
                <p>CineFind is your ultimate destination for discovering and exploring movies. Our platform is designed to help you find your next favorite film through personalized recommendations and comprehensive movie information.</p>
                <h3>Key Features:</h3>
                <ul>
                    <li><i class="fas fa-search"></i><div><strong>Smart Search:</strong> Find movies easily with our advanced search functionality that filters by genre, year, and rating.</div></li>
                    <li><i class="fas fa-star"></i><div><strong>Personalized Recommendations:</strong> Get movie suggestions tailored to your preferences and watching history.</div></li>
                    <li><i class="fas fa-list"></i><div><strong>My List:</strong> Create and manage your personal watchlist of movies you want to see.</div></li>
                    <li><i class="fas fa-fire"></i><div><strong>New & Popular:</strong> Stay updated with the latest releases and trending movies.</div></li>
                </ul>
                <h3>How It Works:</h3>
                <ol>
                    <li>Browse through our extensive movie collection</li>
                    <li>Use filters to find movies that match your interests</li>
                    <li>Save movies to your watchlist for later</li>
                    <li>Rate and review movies to improve recommendations</li>
                </ol>
                <p>Join our community of movie enthusiasts and discover your next favorite film with CineFind!</p>
            </div>
        </div>
    </div>


    <div class="watchlist-container">
        <?php if ($conn): // Hanya tampilkan konten utama jika koneksi berhasil ?>
        <div class="watchlist-header">
            <div>
                <h1 class="watchlist-title">My Watchlist</h1>
                <p class="watchlist-count">
                    <i class="fas fa-film"></i>
                    <span id="watchlistMovieCount"><?php echo $watchlist_count; ?></span> movies
                </p>
            </div>
        </div>

        <div class="movie-grid" id="watchlistGrid">
            <?php if ($watchlist_count > 0): ?>
                <?php foreach ($watchlist_items as $movie): ?>
                    <?php
                    // Logika tampilan dengan fallback
                    $judul_tampil = !empty($movie['local_film_judul']) ? $movie['local_film_judul'] : (!empty($movie['watchlist_movie_title']) ? $movie['watchlist_movie_title'] : 'Judul Tidak Tersedia');
                    $poster_tampil = !empty($movie['local_film_poster_url']) ? $movie['local_film_poster_url'] : (!empty($movie['watchlist_poster_path']) ? $movie['watchlist_poster_path'] : 'cinefind.png');
                    
                    // Tambahkan base URL jika poster dari watchlist tidak lengkap (misalnya hanya path)
                    if (!preg_match("~^(?:f|ht)tps?://~i", $poster_tampil) && $poster_tampil !== 'cinefind.png' && !empty($movie['watchlist_poster_path'])) {
                       $poster_tampil = 'https://image.tmdb.org/t/p/w500' . $movie['watchlist_poster_path'];
                    } elseif (empty($poster_tampil)) {
                        $poster_tampil = 'cinefind.png'; // Fallback jika semua kosong
                    }
                    
                    $tahun_tampil = !empty($movie['local_film_tahun_rilis']) ? $movie['local_film_tahun_rilis'] : 'N/A';
                    $deskripsi_tampil = !empty($movie['local_film_deskripsi']) ? substr($movie['local_film_deskripsi'], 0, 100) . '...' : 'Deskripsi tidak tersedia.';
                    $rating_tampil = number_format((float)$movie['display_rating'], 1);
                    $genres_tampil = !empty($movie['local_film_genres']) ? str_replace(',', ', ', $movie['local_film_genres']) : 'N/A';
                    ?>
                    <div class="movie-card watchlist-movie-card" data-movie-id="<?php echo htmlspecialchars($movie['tmdb_movie_id']); ?>" data-watchlist-id="<?php echo htmlspecialchars($movie['watchlist_id']); ?>">
                        <img src="<?php echo htmlspecialchars($poster_tampil); ?>" 
                             alt="<?php echo htmlspecialchars($judul_tampil); ?>" 
                             class="movie-poster"
                             loading="lazy"
                             onerror="this.onerror=null;this.src='cinefind.png';">
                        <div class="movie-info">
                            <h3 class="movie-title" title="<?php echo htmlspecialchars($judul_tampil); ?>"><?php echo htmlspecialchars($judul_tampil); ?></h3>
                            <div class="movie-platform">
                                <?php
                                if (!empty($movie['local_film_judul'])) {
                                    echo '<span class="platform-label"><i class="fas fa-film"></i> Bioskop</span>';
                                } else {
                                    // Dapatkan data streaming yang sebenarnya
                                    $streaming_providers = getStreamingAvailability($movie['tmdb_movie_id']);
                                    
                                    if (!empty($streaming_providers)) {
                                        foreach ($streaming_providers as $provider) {
                                            $provider_icons = [
                                                'Netflix' => 'fab fa-netflix',
                                                'Disney+' => 'fab fa-disney',
                                                'HBO Max' => 'fas fa-play-circle',
                                                'Prime Video' => 'fab fa-amazon'
                                            ];
                                            
                                            $icon = isset($provider_icons[$provider['name']]) ? $provider_icons[$provider['name']] : 'fas fa-play';
                                            
                                            echo '<span class="platform-label streaming-platform" title="Tersedia di ' . htmlspecialchars($provider['name']) . '">';
                                            echo '<i class="' . $icon . '"></i> ' . htmlspecialchars($provider['name']);
                                            echo '</span>';
                                        }
                                    } else {
                                        echo '<span class="platform-label"><i class="fas fa-info-circle"></i> Belum tersedia di streaming</span>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="movie-actions">
                                <button class="action-btn trailer-btn" onclick="searchTrailerOnYouTube('<?php echo htmlspecialchars(addslashes($judul_tampil)); ?>')" title="Tonton Trailer">
                                    <i class="fas fa-play"></i> Trailer
                                </button>
                                <button class="action-btn remove-btn" onclick="confirmRemoveFromWatchlist('<?php echo htmlspecialchars($movie['tmdb_movie_id']); ?>', this)" title="Hapus dari Watchlist">
                                    <i class="fas fa-times"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($watchlist_count == 0): ?>
            <div class="empty-watchlist" id="emptyWatchlistMessage">
                <i class="fas fa-film main-icon"></i>
                <p class="empty-title">Daftar Tonton Anda Kosong</p>
                <p class="empty-subtitle">Temukan dan tambahkan film favorit Anda untuk memulai koleksi.</p>
                <a href="indexpengguna.php" class="browse-btn">
                    <i class="fas fa-search"></i> Cari Film
                </a>
            </div>
        <?php endif; ?>
        <?php else: ?>
            <div class="empty-watchlist" style="margin-top: 50px;">
                <i class="fas fa-database main-icon" style="color: #f44336;"></i>
                <p class="empty-title">Gagal Memuat Daftar Tonton</p>
                <p class="empty-subtitle">Terjadi masalah saat menghubungkan ke basis data. Silakan coba lagi nanti.</p>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About CineFind</h3>
                <p>Your personalized movie recommendation platform</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="indexpengguna.php">Home</a></li>
                    <li><a href="my-watchlist.php">My List</a></li>
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
            <p>© <?php echo date("Y"); ?> CineFind. All rights reserved.</p>
        </div>
    </footer>
    
    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-dialog-content">
            <h3 id="confirmDialogTitle">Konfirmasi</h3>
            <p id="confirmDialogMessage">Apakah Anda yakin?</p>
            <div class="confirm-dialog-buttons">
                <button id="confirmDialogCancelBtn" class="cancel-btn"><i class="fas fa-times"></i> Batal</button>
                <button id="confirmDialogConfirmBtn" class="confirm-btn"><i class="fas fa-check"></i> Ya</button>
            </div>
        </div>
    </div>


    <script>
        // Fungsi-fungsi JavaScript yang sudah ada di script.js bisa dipanggil di sini
        // jika script.js di-include sebelum blok script ini.
        // Jika tidak, definisikan fungsi yang dibutuhkan di sini.

        // Fungsi toggle profile dropdown dari navbar.php (jika dipindahkan ke script.js global)
        // function toggleProfileDropdown() { ... }

        // Fungsi open/close about modal
        function openAboutModal() {
            const modal = document.getElementById('aboutModal');
            if(modal) modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeAboutModal() {
            const modal = document.getElementById('aboutModal');
            if(modal) modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        // Pastikan window.onclick juga menghandle aboutModal jika ada
        window.onclick = function(event) {
            const aboutModal = document.getElementById('aboutModal');
            if (event.target == aboutModal) {
                closeAboutModal();
            }
            // Jika ada modal lain, tambahkan logikanya di sini
            // const profileDropdown = document.getElementById('profileDropdown');
            // if (profileDropdown && !event.target.closest('.profile-dropdown')) { ... }
        };


        function searchTrailerOnYouTube(movieTitle) {
            const searchQuery = encodeURIComponent(movieTitle + ' trailer');
            window.open(`https://www.youtube.com/results?search_query=${searchQuery}`, '_blank');
        }

        function showNotification(message, type = 'info') {
            const notificationArea = document.body; // Atau elemen spesifik jika ada
            const notification = document.createElement('div');
            notification.className = `notification ${type}`; // misal: notification success
            
            let iconClass = 'fa-info-circle';
            if (type === 'success') iconClass = 'fa-check-circle';
            else if (type === 'error') iconClass = 'fa-exclamation-circle';

            notification.innerHTML = `
                <i class="fas ${iconClass}"></i>
                <span>${message}</span>
            `;
            notificationArea.appendChild(notification);

            setTimeout(() => notification.classList.add('show'), 10); // Munculkan notifikasi

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300); // Hapus dari DOM setelah transisi selesai
            }, 3000); // Notifikasi hilang setelah 3 detik
        }
        
        function updateWatchlistCountAndMessage() {
            const countElement = document.getElementById('watchlistMovieCount');
            const watchlistGrid = document.getElementById('watchlistGrid');
            let currentCount = watchlistGrid.querySelectorAll('.movie-card').length;
            
            if (countElement) {
                countElement.textContent = currentCount;
            }

            let emptyMessageEl = document.getElementById('emptyWatchlistMessage');
            if (currentCount === 0) {
                if (!emptyMessageEl) {
                    const container = document.querySelector('.watchlist-container > .movie-grid'); // Target grid
                     if (container) { // Pastikan container ada sebelum menambah HTML
                        const emptyWatchlistHTML = `
                            <div class="empty-watchlist" id="emptyWatchlistMessage" style="grid-column: 1 / -1;"> {/* Span full width if grid */}
                                <i class="fas fa-film main-icon"></i>
                                <p class="empty-title">Daftar Tonton Anda Kosong</p>
                                <p class="empty-subtitle">Temukan dan tambahkan film favorit Anda untuk memulai koleksi.</p>
                                <a href="indexpengguna.php" class="browse-btn">
                                    <i class="fas fa-search"></i> Cari Film
                                </a>
                            </div>`;
                        // Masukkan setelah grid atau di dalam container jika gridnya yang dikosongkan
                        watchlistGrid.innerHTML = emptyWatchlistHTML; 
                    }
                } else {
                    emptyMessageEl.style.display = 'block'; // Atau 'flex' tergantung stylenya
                }
            } else {
                if (emptyMessageEl) {
                    emptyMessageEl.remove(); // Hapus pesan kosong jika ada item
                }
            }
        }

        // Fungsi untuk menampilkan dialog konfirmasi kustom
        function showCustomConfirm(title, message, onConfirm, onCancel) {
            const dialog = document.getElementById('confirmDialog');
            const dialogTitle = document.getElementById('confirmDialogTitle');
            const dialogMessage = document.getElementById('confirmDialogMessage');
            const confirmBtn = document.getElementById('confirmDialogConfirmBtn');
            const cancelBtn = document.getElementById('confirmDialogCancelBtn');

            dialogTitle.textContent = title;
            dialogMessage.textContent = message;

            dialog.classList.add('show');
            document.body.style.overflow = 'hidden'; // Mencegah scroll background

            confirmBtn.onclick = () => {
                dialog.classList.remove('show');
                document.body.style.overflow = 'auto';
                if (onConfirm) onConfirm();
            };
            cancelBtn.onclick = () => {
                dialog.classList.remove('show');
                document.body.style.overflow = 'auto';
                if (onCancel) onCancel();
            };
        }


        function confirmRemoveFromWatchlist(movieId, buttonElement) {
            showCustomConfirm(
                'Hapus Film?',
                'Apakah Anda yakin ingin menghapus film ini dari daftar tonton?',
                async function() { // onConfirm
                    try {
                        const formData = new FormData();
                        formData.append('action', 'remove');
                        formData.append('movie_id', movieId);

                        // Pastikan path ke watchlist_handler.php benar
                        const response = await fetch('watchlist_handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            const movieCard = buttonElement.closest('.movie-card');
                            if (movieCard) {
                                movieCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                movieCard.style.opacity = '0';
                                movieCard.style.transform = 'scale(0.8)';
                                
                                setTimeout(() => {
                                    movieCard.remove();
                                    updateWatchlistCountAndMessage();
                                    showNotification('Film dihapus dari daftar tonton', 'success');
                                }, 300);
                            }
                        } else {
                            throw new Error(data.message || 'Gagal menghapus film dari daftar tonton');
                        }
                    } catch (error) {
                        console.error('Error removing from watchlist:', error);
                        showNotification(error.message || 'Gagal menghapus film. Coba lagi.', 'error');
                    }
                }
            );
        }

        // Panggil updateWatchlistCountAndMessage saat DOM siap untuk memastikan tampilan awal benar
        document.addEventListener('DOMContentLoaded', function() {
            updateWatchlistCountAndMessage();
        });

        // Update fungsi handleStreamingPlatformClick
        function handleStreamingPlatformClick(platform) {
            const platformUrls = {
                'Netflix': 'https://www.netflix.com/search?q=',
                'Disney+': 'https://www.disneyplus.com/search?q=',
                'HBO Max': 'https://play.hbomax.com/search?q=',
                'Prime Video': 'https://www.primevideo.com/search?q='
            };

            const movieTitle = platform.closest('.movie-card').querySelector('.movie-title').textContent;
            const platformName = platform.textContent.trim();
            
            if (platformUrls[platformName]) {
                const searchUrl = platformUrls[platformName] + encodeURIComponent(movieTitle);
                window.open(searchUrl, '_blank');
            }
        }

        // Tambahkan event listener untuk platform streaming
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.streaming-platform').forEach(platform => {
                platform.addEventListener('click', () => handleStreamingPlatformClick(platform));
            });
        });

    </script>
</body>
</html>