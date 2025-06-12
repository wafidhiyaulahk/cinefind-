<?php

session_start(); // WAJIB di baris pertama



// Lanjutkan dengan sisa kode index.php jika pengguna sudah login
// ... (kode Anda yang sudah ada untuk menampilkan movie data arrays, dll.)

// Movie data arrays
$featuredMovies = [
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg',
        'alt' => 'FAST X',
        'title' => 'FAST X',
        'year' => '2023',
        'rating' => '8.5',
        'description' => 'Dom Toretto dan keluarganya menjadi target balas dendam putra raja narkoba Hernan Reyes. Mereka harus menghadapi musuh paling berbahaya mereka dan melindungi semua yang mereka cintai.'
    ],
    [
        'img_src' => 'gmbr/oppenheimer.jpeg',
        'alt' => 'Oppenheimer',
        'title' => 'Oppenheimer',
        'year' => '2023',
        'rating' => '8.9',
        'description' => 'Kisah ilmuwan Amerika J. Robert Oppenheimer dan perannya dalam pengembangan bom atom. Film biografi yang menggugah yang mengeksplorasi implikasi moral dari penemuan ilmiah.'
    ],
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg',
        'alt' => 'Avengers: Endgame',
        'title' => 'Avengers: Endgame',
        'year' => '2019',
        'rating' => '8.4',
        'description' => 'Setelah peristiwa menghancurkan di Avengers: Infinity War, semesta berada dalam kehancuran. Dengan bantuan sekutu yang tersisa, Avengers berkumpul lagi untuk membalikkan tindakan Thanos.'
    ],
    [
        'img_src' => 'gmbr/Spider-Man_ No Way Home.jpeg',
        'alt' => 'Spider-Man: No Way Home',
        'title' => 'Spider-Man: No Way Home',
        'year' => '2021',
        'rating' => '8.2',
        'description' => 'Dengan identitas Spider-Man yang terbongkar, Peter meminta bantuan Doctor Strange. Ketika mantra salah, musuh berbahaya dari dunia lain muncul, memaksa dia untuk menemukan arti sebenarnya menjadi Spider-Man.'
    ],
    [
        'img_src' => 'gmbr/matrix.jpg',
        'alt' => 'The Matrix',
        'title' => 'The Matrix',
        'year' => '1999',
        'rating' => '8.7',
        'description' => 'Seorang hacker komputer belajar dari para pemberontak misterius tentang sifat sebenarnya dari realitasnya dan perannya dalam perang melawan para pengendalinya. Film sci-fi revolusioner yang mengubah cara pandang kita terhadap aksi.'
    ],
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
        'alt' => 'Interstellar',
        'title' => 'Interstellar',
        'year' => '2014',
        'rating' => '8.6',
        'description' => 'Tim penjelajah melakukan perjalanan melalui lubang cacing di luar angkasa dalam upaya memastikan kelangsungan hidup umat manusia. Sebuah perjalanan yang membingungkan melalui ruang dan waktu.'
    ],
    [
        'img_src' => 'gmbr/parasite.jpg',
        'alt' => 'Parasite',
        'title' => 'Parasite',
        'year' => '2019',
        'rating' => '8.5',
        'description' => 'Keluarga Ki-taek yang menganggur mulai tertarik dengan keluarga Park yang kaya dan glamor. Saat mereka menyusup ke dalam kehidupan mereka, mereka terlibat dalam kejadian tak terduga yang mengubah hidup mereka.'
    ],
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/udDclJoHjfjb8Ekgsd4FDteOkCU.jpg',
        'alt' => 'Joker',
        'title' => 'Joker',
        'year' => '2019',
        'rating' => '8.4',
        'description' => 'Di Gotham City, komedian Arthur Fleck yang bermasalah mental diabaikan dan diperlakukan buruk oleh masyarakat. Dia kemudian memulai spiral ke bawah revolusi dan kejahatan berdarah.'
    ]
];

$indonesianMovies = [
    [
        'img_src' => 'gmbr/Miracle in Cell No_ 7.jpeg',
        'alt' => 'Miracle In Cell No.7',
        'title' => 'Miracle In Cell No.7',
        'year' => '2022',
        'rating' => '8.3',
        'description' => 'Kisah mengharukan tentang ayah berkebutuhan khusus yang difitnah membunuh dan hubungannya dengan putri kecilnya. Film yang menyentuh hati tentang cinta dan keadilan.'
    ],
    [
        'img_src' => 'gmbr/KKN Di Desa Penari.jpeg',
        'alt' => 'KKN di Desa Penari',
        'title' => 'KKN di Desa Penari',
        'year' => '2022',
        'rating' => '7.8',
        'description' => 'Sekelompok mahasiswa mengalami kejadian mistis saat melakukan KKN di desa terpencil. Film horor yang didasarkan pada thread Twitter viral.'
    ],
    [
        'img_src' => 'gmbr/dilan 1991.jpeg',
        'alt' => 'Dilan 1991',
        'title' => 'Dilan 1991',
        'year' => '2019',
        'rating' => '7.9',
        'description' => 'Kisah cinta Dilan dan Milea di tahun 1991, penuh romansa masa SMA dan nostalgia. Sebuah perjalanan nostalgia tentang cinta pertama.'
    ],
    [
        'img_src' => 'gmbr/Agak Laen (2024).jpeg',
        'alt' => 'Agak Laen',
        'title' => 'Agak Laen',
        'year' => '2024',
        'rating' => '8.1',
        'description' => 'Film komedi tentang sekelompok teman yang terlibat dalam situasi kocak tak terduga. Sebuah tawa segar dalam dunia komedi Indonesia.'
    ],
    [
        'img_src' => 'gmbr/pengabdi setan.jpg',
        'alt' => 'Pengabdi Setan',
        'title' => 'Pengabdi Setan',
        'year' => '2017',
        'rating' => '7.6',
        'description' => 'Sebuah keluarga pindah ke rumah baru, hanya untuk menemukan bahwa rumah tersebut dihuni oleh roh jahat. Film horor Indonesia modern yang mendefinisikan ulang genre.'
    ],
    [
        'img_src' => 'gmbr/gundala.jpg',
        'alt' => 'Gundala',
        'title' => 'Gundala',
        'year' => '2019',
        'rating' => '7.0',
        'description' => 'Kisah asal usul superhero pertama Indonesia, Gundala. Seorang pria mendapatkan kekuatan listrik dan harus melindungi kotanya dari kekuatan jahat.'
    ],
    [
        'img_src' => 'gmbr/Marlina Si Pembunuh Dalam Empat Babak (2017).jpg',
        'alt' => 'Marlina Si Pembunuh dalam Empat Babak',
        'title' => 'Marlina Si Pembunuh dalam Empat Babak',
        'year' => '2017',
        'rating' => '7.8',
        'description' => 'Seorang janda di desa terpencil harus mempertahankan diri dari sekelompok bandit. Kisah balas dendam feminis yang diceritakan dalam empat babak.'
    ],
    [
        'img_src' => 'gmbr/keluarga cemara.jpg',
        'alt' => 'Keluarga Cemara',
        'title' => 'Keluarga Cemara',
        'year' => '2019',
        'rating' => '7.7',
        'description' => 'Kisah mengharukan tentang keluarga yang harus beradaptasi dengan kehidupan yang lebih sederhana setelah kehilangan kekayaan mereka. Adaptasi modern dari novel Indonesia yang dicintai.'
    ]
];

$recommendedMovies = [
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg',
        'alt' => 'The Godfather',
        'title' => 'The Godfather',
        'year' => '1972',
        'rating' => '8.7',
        'description' => 'Patriark mafia menyerahkan kekuasaan imperium kriminal pada anaknya yang enggan. Sebuah mahakarya sinema yang mengeksplorasi keluarga, kekuasaan, dan mimpi Amerika.'
    ],
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
        'alt' => 'Inception',
        'title' => 'Inception',
        'year' => '2010',
        'rating' => '8.8',
        'description' => 'Pencuri yang mencuri rahasia perusahaan lewat teknologi berbagi mimpi diberi tugas sebaliknya. Sebuah film sci-fi yang membingungkan pikiran tentang realitas dan mimpi.'
    ],
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg',
        'alt' => 'The Shawshank Redemption',
        'title' => 'The Shawshank Redemption',
        'year' => '1994',
        'rating' => '8.7',
        'description' => 'Dua narapidana membangun ikatan selama bertahun-tahun di penjara. Sebuah kisah harapan dan persahabatan yang menyentuh hati.'
    ],
    [
        'img_src' => 'gmbr/the darknight.jpeg',
        'alt' => 'The Dark Knight',
        'title' => 'The Dark Knight',
        'year' => '2008',
        'rating' => '9.0',
        'description' => 'Batman menghadapi ujian terberat ketika Joker meneror Gotham. Film superhero yang mendalam tentang moralitas dan keadilan.'
    ],
    [
        'img_src' => 'gmbr/pulp.jpg',
        'alt' => 'Pulp Fiction',
        'title' => 'Pulp Fiction',
        'year' => '1994',
        'rating' => '8.9',
        'description' => 'Kisah kehidupan dua pembunuh bayaran mafia, seorang petinju, seorang gangster dan istrinya, serta sepasang perampok restoran yang saling terhubung dalam empat cerita tentang kekerasan dan penebusan.'
    ],
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/saHP97rTPS5eLmrLQEcANmKrsFl.jpg',
        'alt' => 'Forrest Gump',
        'title' => 'Forrest Gump',
        'year' => '1994',
        'rating' => '8.8',
        'description' => 'Kisah hidup Forrest Gump, seorang pria dengan IQ rendah yang secara tidak sengaja menjadi bagian dari peristiwa-peristiwa penting dalam sejarah Amerika, dari era Kennedy hingga era modern.'
    ],
    [
        'img_src' => 'gmbr/c36ddb7a-6e3b-4cd9-9825-f24e9a3498c4.jpg',
        'alt' => 'Spirited Away',
        'title' => 'Spirited Away',
        'year' => '2001',
        'rating' => '8.6',
        'description' => 'Selama perpindahan keluarganya ke pinggiran kota, seorang gadis berusia 10 tahun yang murung tersesat ke dunia yang dikuasai oleh dewa-dewa, penyihir, dan roh, di mana manusia diubah menjadi binatang.'
    ],
    [
        'img_src' => 'https://image.tmdb.org/t/p/w500/sKCr78MXSLixwmZ8DyJLrpMsd15.jpg',
        'alt' => 'The Lion King',
        'title' => 'The Lion King (1994)',
        'year' => '1994',
        'rating' => '8.5',
        'description' => 'Pangeran singa Simba dan ayahnya menjadi target pamannya yang pahit, yang ingin naik takhta sendiri. Kisah abadi tentang keluarga, tanggung jawab, dan penebusan.'
    ]
];

// Function to display movie cards
function displayMovieCards($movies) {
    foreach ($movies as $movie) {
        echo '<div class="movie-card" 
            data-title="' . htmlspecialchars($movie['title']) . '" 
            data-year="' . htmlspecialchars($movie['year']) . '" 
            data-rating="' . htmlspecialchars($movie['rating']) . '"
            data-description="' . htmlspecialchars($movie['description']) . '"
            onclick="showMovieDetails({
                title: \'' . htmlspecialchars($movie['title']) . '\',
                year: \'' . htmlspecialchars($movie['year']) . '\',
                rating: \'' . htmlspecialchars($movie['rating']) . '\',
                description: \'' . htmlspecialchars($movie['description']) . '\',
                img_src: \'' . htmlspecialchars($movie['img_src']) . '\'
            })">';
        echo '<img src="' . htmlspecialchars($movie['img_src']) . '" alt="' . htmlspecialchars($movie['alt']) . '" class="movie-poster">';
        echo '<div class="movie-info">';
        echo '<h3 class="movie-title">' . htmlspecialchars($movie['title']) . '</h3>';
        echo '<div class="movie-year">' . htmlspecialchars($movie['year']) . '</div>';
        echo '<div class="movie-rating"><i class="fas fa-star"></i>' . htmlspecialchars($movie['rating']) . '</div>';
        echo '<div class="movie-description">' . htmlspecialchars(substr($movie['description'], 0, 100)) . '...</div>';
        echo '<button class="trailer-btn" onclick="event.stopPropagation(); searchTrailer(\'' . htmlspecialchars($movie['title']) . '\')">
                <i class="fas fa-play"></i> Watch Trailer
              </button>';
        echo '</div></div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineFind - Rekomendasi Film</title>
    <link rel="icon" type="image/png" href="gmbr\cinefind.png">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style\index.css">
</head>
<body>
    <!-- Navigasi -->
    <nav class="navbar">
        <div class="navbar-brand">
                <img src="gmbr\cinefind.png" alt="Logo CineFind" class="logo" style="width: 100px; height: 100px;">
            </a>
        </div>
    </nav>

    <!-- Bagian Hero -->
    <header class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Selamat Datang di CineFind</h1>
                <p class="hero-subtitle">Temukan film favorit Anda berikutnya berdasarkan rating pengguna</p>
            </div>
            
            <div class="premium-features">
                <div class="premium-header">
                    <h3>Buka Fitur Premium</h3>
                    <p>Daftar atau masuk untuk mengakses konten eksklusif</p>
                </div>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-film"></i>
                        <span>Trailer Eksklusif</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-info-circle"></i>
                        <span>Info Detail</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-star"></i>
                        <span>Daftar Tonton</span>
                    </div>
                </div>

                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                    </a>
                    <a href="register.php" class="btn btn-register">
                        <i class="fas fa-user-plus"></i>
                        <span>Register</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <style>
        .hero {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .hero-content {
            max-width: 1200px;
            width: 100%;
            text-align: center;
        }

        .hero-text {
            margin-bottom: 3rem;
        }

        .hero-title {
            font-size: 3.5rem;
            color: #fff;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: #ccc;
        }

        .premium-features {
            background: rgba(0,0,0,0.8);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .premium-header {
            margin-bottom: 2rem;
        }

        .premium-header h3 {
            color: #e50914;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .premium-header p {
            color: #ccc;
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
        }

        .feature-item i {
            font-size: 2rem;
            color: #e50914;
        }

        .feature-item span {
            color: #fff;
            font-size: 1rem;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-login {
            background: #e50914;
            color: #fff;
        }

        .btn-register {
            background: #2c2c2c;
            color: #fff;
            border: 1px solid #e50914;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(229,9,20,0.3);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .auth-buttons {
                flex-direction: column;
            }
        }
    </style>

   
    <!-- Main Content -->
    <main id="mainContent">
        <!-- Featured Movies Section -->
        <section class="featured-movies" style="text-align: center; margin: auto;">
            <h2>Featured Movies</h2>
            <div class="movie-grid" id="featuredMovies">
                <?php displayMovieCards($featuredMovies); ?>
            </div>
        </section>

        <!-- Indonesian Movies Section -->
        <section class="recommended-movies" style="text-align: center; margin: auto;">
            <h2>Indonesian Movies</h2>
            <div class="movie-grid" id="indonesianMovies">
                <?php displayMovieCards($indonesianMovies); ?>
            </div>
        </section>

        <!-- Recommended Movies Section -->
        <section class="recommended-movies" style="text-align: center; margin: auto;">
            <h2>Recommended for You</h2>
            <div class="movie-grid" id="recommendedMovies">
                <?php displayMovieCards($recommendedMovies); ?>
            </div>
        </section>

    <!-- Movie Detail Modal -->
    <div id="movieModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeMovieModal()">&times;</span>
            <div class="modal-body">
                <!-- Movie details will be dynamically added here -->
            </div>
            <!-- Add Review Section -->
            <div class="review-section">
                <h3>User Reviews</h3>
                <div class="review-form">
                    <textarea id="reviewText" placeholder="Write your review about this movie..." rows="4"></textarea>
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
                <p>A personalized movie recommendation platform for you</p>
            </div>
            <div class="footer-section">
                <h3>Movie Trailers</h3>
                <p>Watch exclusive movie trailers and get a sneak peek into upcoming releases. Our trailer section helps you discover new films and make informed decisions about what to watch next.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Movies</a></li>
                    <li><a href="#">My List</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/cinefind_film/"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 CineFind. All rights reserved.</p>
        </div>
    </footer>
    <script src="layout/js/cinema.js"></script>
    <style>
        /* Remove search-related styles */
        .movie-card {
            position: relative;
            transition: transform 0.3s ease;
        }

        .movie-card:hover {
            transform: scale(1.05);
        }

        .trailer-btn {
            background-color: #e50914;
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

        .trailer-btn:hover {
            background-color: #f40612;
        }

        .trailer-btn i {
            font-size: 1rem;
        }

        .movie-info {
            padding: 15px;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 0 0 8px 8px;
        }

        .movie-section {
            transition: opacity 0.3s ease;
        }

        .movie-section.hidden {
            display: none;
            opacity: 0;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            background-color: #1f1f1f;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            max-width: 800px;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #fff;
        }

        .movie-details {
            display: flex;
            gap: 20px;
            color: white;
        }

        .modal-poster {
            max-width: 300px;
            border-radius: 4px;
        }

        .modal-info {
            flex: 1;
        }

        .modal-info h2 {
            margin: 0 0 10px 0;
            color: #fff;
        }

        .modal-year, .modal-rating {
            margin: 5px 0;
            color: #b3b3b3;
        }

        @media (max-width: 768px) {
            .movie-details {
                flex-direction: column;
            }

            .modal-poster {
                max-width: 100%;
            }
        }

        .movie-description {
            color: #ccc;
            font-size: 0.9rem;
            margin: 0.5rem 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .movie-card:hover .movie-description {
            color: #fff;
        }

        .modal-content .movie-description {
            font-size: 1rem;
            margin: 1rem 0;
            color: #fff;
            line-height: 1.6;
            -webkit-line-clamp: unset;
            overflow: visible;
        }

        @media (max-width: 768px) {
            .movie-description {
                font-size: 0.8rem;
                -webkit-line-clamp: 2;
            }
        }

        /* Update login button styles */
        .login-button {
            background-color: #e50914;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .login-button:hover {
            background-color: #f40612;
        }

        .login-button i {
            font-size: 1.1em;
        }

        /* Add login required message */
        .login-required-message {
            background-color: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
            display: none;
        }

        .login-required-message.show {
            display: block;
        }

        .login-required-message i {
            color: #e50914;
            margin-right: 5px;
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
    </style>
    <script>
    // Add function to show login required message
    function showLoginRequired() {
        const message = document.createElement('div');
        message.className = 'login-required-message show';
        message.innerHTML = `
            <i class="fas fa-lock"></i>
           Login Now
            <a href="login.php" class="login-link">Login Now</a>
        `;
        
        // Insert message after the hero section
        const hero = document.querySelector('.hero');
        hero.parentNode.insertBefore(message, hero.nextSibling);
        
        // Remove message after 5 seconds
        setTimeout(() => {
            message.remove();
        }, 5000);
    }

    // Modify searchTrailer function
    function searchTrailer(movieTitle) {
        <?php if (!isset($_SESSION['user_id'])): ?>
            showLoginRequired();
            return;
        <?php endif; ?>
        
        const searchQuery = encodeURIComponent(movieTitle + ' trailer');
        window.open(`https://www.youtube.com/results?search_query=${searchQuery}`, '_blank');
    }

    // Add these functions to your existing JavaScript
    let currentRating = 0;

    function initializeStarRating() {
        const stars = document.querySelectorAll('.star-rating i');
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const rating = parseInt(star.dataset.rating);
                currentRating = rating;
                updateStarDisplay();
            });
        });
    }

    function updateStarDisplay() {
        const stars = document.querySelectorAll('.star-rating i');
        stars.forEach(star => {
            const rating = parseInt(star.dataset.rating);
            star.classList.toggle('active', rating <= currentRating);
        });
    }

    function submitReview() {
        const reviewText = document.getElementById('reviewText').value;
        if (!reviewText.trim()) {
            showNotification('Please write a review', 'error');
            return;
        }
        if (currentRating === 0) {
            showNotification('Please select a rating', 'error');
            return;
        }

        // Here you would typically send the review to your server
        // For now, we'll just add it to the UI
        addReviewToUI({
            reviewer: '<?php echo htmlspecialchars($nama_display); ?>',
            rating: currentRating,
            content: reviewText,
            date: new Date().toLocaleDateString()
        });

        // Clear the form
        document.getElementById('reviewText').value = '';
        currentRating = 0;
        updateStarDisplay();
        showNotification('Review submitted successfully', 'success');
    }

    function addReviewToUI(review) {
        const reviewsList = document.getElementById('reviewsList');
        const reviewElement = document.createElement('div');
        reviewElement.className = 'review-item';
        reviewElement.innerHTML = `
            <div class="review-header">
                <span class="reviewer-name">${review.reviewer}</span>
                <span class="review-date">${review.date}</span>
            </div>
            <div class="review-rating">
                ${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}
            </div>
            <div class="review-content">${review.content}</div>
        `;
        reviewsList.insertBefore(reviewElement, reviewsList.firstChild);
    }

    // Initialize star rating when modal opens
    document.addEventListener('DOMContentLoaded', function() {
        initializeStarRating();
    });
    </script>
</body>
</html>