    // API Configuration
    const API_KEY = 'YOUR_TMDB_API_KEY'; // Replace with your TMDB API key
    const BASE_URL = 'https://api.themoviedb.org/3';
    const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p';
    
    // DOM Elements
    const featuredMoviesContainer = document.getElementById('featuredMovies');
    const recommendedMoviesContainer = document.getElementById('recommendedMovies');
    const searchInput = document.querySelector('.hero-search');
    const searchButton = document.querySelector('.search-button');
    const modal = document.getElementById('movieModal');
    const closeModal = document.querySelector('.close');
    const modalBody = document.querySelector('.modal-body');
    
    // State Management
    let currentUser = null;
    let watchlist = JSON.parse(localStorage.getItem('watchlist')) || [];
    
    // Event Listeners
    document.addEventListener('DOMContentLoaded', () => {
        loadFeaturedMovies();
        loadRecommendedMovies();
        setupEventListeners();
    });
    
    // Setup Event Listeners
    function setupEventListeners() {
        // Search functionality
        searchButton.addEventListener('click', handleSearch);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') handleSearch();
        });
    
        // Modal close button
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    
        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    
        // Filter change handlers
        document.getElementById('genreFilter').addEventListener('change', handleFilterChange);
        document.getElementById('yearFilter').addEventListener('change', handleFilterChange);
        document.getElementById('ratingFilter').addEventListener('change', handleFilterChange);
    
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }
    
    // API Functions
    async function fetchMovies(endpoint, params = {}) {
        const queryParams = new URLSearchParams({
            api_key: API_KEY,
            ...params
        });
    
        try {
            const response = await fetch(`${BASE_URL}${endpoint}?${queryParams}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching movies:', error);
            return null;
        }
    }
    
    // Load Featured Movies
    async function loadFeaturedMovies() {
        const data = await fetchMovies('/movie/popular');
        if (data && data.results) {
            displayMovies(data.results.slice(0, 6), featuredMoviesContainer);
        }
    }
    
    // Load Recommended Movies
    async function loadRecommendedMovies() {
        const data = await fetchMovies('/movie/top_rated');
        if (data && data.results) {
            displayMovies(data.results.slice(0, 6), recommendedMoviesContainer);
        }
    }
    
    // Display Movies
    function displayMovies(movies, container) {
        container.innerHTML = '';
        movies.forEach(movie => {
            const movieCard = createMovieCard(movie);
            container.appendChild(movieCard);
        });
    }
    
    // Create Movie Card
    function createMovieCard(movie) {
        const card = document.createElement('div');
        card.className = 'movie-card';

        // Map movie titles to their image sources
        const imageMap = {
            'FAST X': 'https://image.tmdb.org/t/p/w500/1E5baAaEse26fej7uHcjOgEE2t2.jpg',
            'Oppenheimer': 'gmbr/oppenheimer.jpeg',
            'Avengers: Endgame': 'https://image.tmdb.org/t/p/w500/7WsyChQLEftFiDOVTGkv3hFpyyt.jpg',
            'Spider-Man: No Way Home': 'gmbr/Spider-Man_ No Way Home.jpeg',
            'The Matrix': 'https://image.tmdb.org/t/p/w500/f89SLjs2qmf15PlxBnsh3zz2mh4.jpg',
            'Interstellar': 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
            'Parasite': 'https://image.tmdb.org/t/p/w500/7liEU7WUYG39sY47EDynS39GWyM.jpg',
            'Joker': 'https://image.tmdb.org/t/p/w500/udDclJoHjfjb8Ekgsd4FDteOkCU.jpg',
            'Miracle In Cell No.7': 'gmbr/Miracle in Cell No_ 7.jpeg',
            'KKN di Desa Penari': 'gmbr/KKN Di Desa Penari.jpeg',
            'Dilan 1991': 'gmbr/dilan 1991.jpeg',
            'Agak Laen': 'gmbr/Agak Laen (2024).jpeg',
            'Pengabdi Setan': 'https://image.tmdb.org/t/p/w500/x8VERTesx04a353b22HPK53mBf.jpg',
            'Gundala': 'https://image.tmdb.org/t/p/w500/yagMGUzF4sQ902a522T8X7S0zNJ.jpg',
            'Marlina Si Pembunuh dalam Empat Babak': 'gmbr/marlina.jpeg',
            'Keluarga Cemara': 'gmbr/keluarga cemara.jpeg',
            'The Godfather': 'https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg',
            'Inception': 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
            'The Shawshank Redemption': 'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg',
            'The Dark Knight': 'gmbr/the darknight.jpeg',
            'Pulp Fiction': 'https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzrKYO7.jpg',
            'Forrest Gump': 'https://image.tmdb.org/t/p/w500/saHP97rTPS5eLmrLQEcANmKrsFl.jpg',
            'Spirited Away': 'https://image.tmdb.org/t/p/w500/39wmItIW2asIuyA23mKhrDiy8d6.jpg',
            'The Lion King': 'https://image.tmdb.org/t/p/w500/sKCr78MXSLixwmZ8DyJLrpMsd15.jpg'
        };

        // Get the image source based on the movie title
        const imgSrc = imageMap[movie.title] || (movie.poster_path ? `${IMAGE_BASE_URL}/w500${movie.poster_path}` : 'placeholder.jpg');

        card.innerHTML = `
            <img src="${imgSrc}" 
                 alt="${movie.title}" 
                 class="movie-poster">
            <div class="movie-info">
                <h3 class="movie-title">${movie.title}</h3>
                <div class="movie-rating">
                    <i class="fas fa-star"></i> ${movie.vote_average?.toFixed(1)}
                </div>
            </div>
        `;

        card.addEventListener('click', () => showMovieDetails(movie));
        return card;
    }
    
    // Show Movie Details
    function showMovieDetails(movie) {
        const modal = document.getElementById('movieModal');
        const modalContent = modal.querySelector('.modal-content');
        
        // Get trailer link
        const trailerLink = getTrailerLink(movie.title);
        
        // Create movie details HTML with proper styling
        modalContent.innerHTML = `
            <span class="close">&times;</span>
            <div class="modal-body">
                <div class="movie-details">
                    <div class="movie-poster-container">
                        <img src="${movie.img_src}" 
                             alt="${movie.title}" 
                             class="movie-poster-large">
                    </div>
                    <div class="movie-info-detailed">
                        <h2 class="movie-title-modal">${movie.title}</h2>
                        <div class="movie-meta">
                            <span class="movie-year"><i class="fas fa-calendar"></i> ${movie.year}</span>
                            <span class="movie-rating"><i class="fas fa-star"></i> ${movie.rating}</span>
                        </div>
                        <div class="movie-description">
                            <h3>Description</h3>
                            <p>${movie.description || 'No description available.'}</p>
                        </div>
                        <div class="movie-actions">
                            <a href="${trailerLink}" target="_blank" class="watch-trailer-btn">
                                <i class="fas fa-play"></i> Watch Trailer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add styles for the modal
        const style = document.createElement('style');
        style.textContent = `
            .movie-details {
                display: flex;
                gap: 2rem;
                padding: 1rem;
                color: white;
            }
            
            .movie-poster-container {
                flex: 0 0 300px;
            }
            
            .movie-poster-large {
                width: 100%;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }
            
            .movie-info-detailed {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .movie-title-modal {
                font-size: 2rem;
                margin: 0;
                color: #fff;
            }
            
            .movie-meta {
                display: flex;
                gap: 1rem;
                color: #ccc;
            }
            
            .movie-description {
                margin-top: 1rem;
            }
            
            .movie-description h3 {
                color: #fff;
                margin-bottom: 0.5rem;
            }
            
            .movie-description p {
                color: #ccc;
                line-height: 1.6;
                margin: 0;
            }
            
            .watch-trailer-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background-color: #e50914;
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 4px;
                text-decoration: none;
                margin-top: 1rem;
                transition: background-color 0.3s;
            }
            
            .watch-trailer-btn:hover {
                background-color: #f40612;
            }
            
            @media (max-width: 768px) {
                .movie-details {
                    flex-direction: column;
                }
                
                .movie-poster-container {
                    flex: 0 0 auto;
                }
            }
        `;
        document.head.appendChild(style);

        // Show modal
        modal.style.display = "block";

        // Close button functionality
        const closeBtn = modal.querySelector('.close');
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
    
    // Handle Search
    async function handleSearch() {
        const query = searchInput.value.trim();
        if (query) {
            const data = await fetchMovies('/search/movie', { query });
            if (data && data.results) {
                displayMovies(data.results, featuredMoviesContainer);
            }
        }
    }
    
    // Handle Filter Change
    async function handleFilterChange() {
        const genre = document.getElementById('genreFilter').value;
        const year = document.getElementById('yearFilter').value;
        const rating = document.getElementById('ratingFilter').value;
    
        const params = {};
        if (genre) params.with_genres = genre;
        if (year) params.primary_release_year = year;
        if (rating) params['vote_average.gte'] = rating;
    
        const data = await fetchMovies('/discover/movie', params);
        if (data && data.results) {
            displayMovies(data.results, featuredMoviesContainer);
        }
    }
    
    // Watchlist Functions
    function toggleWatchlist(movieId) {
        const index = watchlist.indexOf(movieId);
        if (index === -1) {
            watchlist.push(movieId);
        } else {
            watchlist.splice(index, 1);
        }
        localStorage.setItem('watchlist', JSON.stringify(watchlist));
        showMovieDetails({ id: movieId }); // Refresh the modal
    }
    
    // User Authentication (Basic Implementation)
    function login(username, password) {
        // In a real application, this would involve proper authentication
        currentUser = { username };
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        updateUIForLoggedInUser();
    }
    
    function logout() {
        currentUser = null;
        localStorage.removeItem('currentUser');
        updateUIForLoggedOutUser();
    }
    
    function updateUIForLoggedInUser() {
        // Update UI elements for logged-in state
        const userProfile = document.querySelector('.user-profile');
        if (userProfile) {
            userProfile.querySelector('img').src = `https://via.placeholder.com/32?text=${currentUser.username[0]}`;
        }
    }
    
    function updateUIForLoggedOutUser() {
        // Update UI elements for logged-out state
        const userProfile = document.querySelector('.user-profile');
        if (userProfile) {
            userProfile.querySelector('img').src = 'https://via.placeholder.com/32';
        }
    }
    
    // Initialize user state
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        updateUIForLoggedInUser();
    }
    
    // Helper function to get movie descriptions
    function getMovieDescription(title) {
        const descriptions = {
            "FAST X": "Dom Toretto dan keluarganya menjadi target balas dendam putra raja narkoba Hernan Reyes.",
            "Oppenheimer": "Kisah ilmuwan Amerika J. Robert Oppenheimer dan perannya dalam pengembangan bom atom.",
            "Avengers: Endgame": "Setelah peristiwa menghancurkan di Avengers: Infinity War, semesta berada dalam kehancuran. Dengan bantuan sekutu yang tersisa, Avengers berkumpul lagi untuk membalikkan tindakan Thanos.",
            "Spider-Man: No Way Home": "Dengan identitas Spider-Man yang terbongkar, Peter meminta bantuan Doctor Strange. Ketika mantra salah, musuh berbahaya dari dunia lain muncul.",
            "The Matrix": "Seorang hacker komputer belajar dari para pemberontak misterius tentang sifat sebenarnya dari realitasnya dan perannya dalam perang melawan para pengendalinya.",
            "Interstellar": "Tim penjelajah melakukan perjalanan melalui lubang cacing di luar angkasa dalam upaya memastikan kelangsungan hidup umat manusia. Sebuah perjalanan yang membingungkan melalui ruang dan waktu.",
            "Parasite": "Keluarga Ki-taek yang menganggur mulai tertarik dengan keluarga Park yang kaya dan glamor, saat mereka menyusup ke dalam kehidupan mereka dan terlibat dalam kejadian tak terduga.",
            "Joker": "Di Gotham City, komedian Arthur Fleck yang bermasalah mental diabaikan dan diperlakukan buruk oleh masyarakat. Dia kemudian memulai spiral ke bawah revolusi dan kejahatan berdarah.",
            "Miracle In Cell No.7": "Kisah mengharukan tentang ayah berkebutuhan khusus yang difitnah membunuh dan hubungannya dengan putri kecilnya.",
            "KKN di Desa Penari": "Sekelompok mahasiswa mengalami kejadian mistis saat KKN di desa terpencil.",
            "Dilan 1991": "Kisah cinta Dilan dan Milea di tahun 1991, penuh romansa masa SMA dan nostalgia.",
            "Agak Laen": "Film komedi tentang sekelompok teman yang terlibat dalam situasi kocak tak terduga.",
            "Pengabdi Setan": "Sebuah keluarga pindah ke rumah baru, hanya untuk menemukan bahwa rumah tersebut dihuni oleh roh jahat. Film horor Indonesia modern yang mendefinisikan ulang genre.",
            "Gundala": "Kisah asal usul superhero pertama Indonesia, Gundala. Seorang pria mendapatkan kekuatan listrik dan harus melindungi kotanya dari kekuatan jahat.",
            "Marlina Si Pembunuh dalam Empat Babak": "Seorang janda di desa terpencil harus mempertahankan diri dari sekelompok bandit. Kisah balas dendam feminis yang diceritakan dalam empat babak.",
            "Keluarga Cemara": "Kisah mengharukan tentang keluarga yang harus beradaptasi dengan kehidupan yang lebih sederhana setelah kehilangan kekayaan mereka. Adaptasi modern dari novel Indonesia yang dicintai.",
            "The Godfather": "Patriark mafia menyerahkan kekuasaan imperium kriminal pada anaknya yang enggan.",
            "Inception": "Pencuri yang mencuri rahasia perusahaan lewat teknologi berbagi mimpi diberi tugas sebaliknya.",
            "The Shawshank Redemption": "Dua narapidana membangun ikatan selama bertahun-tahun di penjara.",
            "The Dark Knight": "Batman menghadapi ujian terberat ketika Joker meneror Gotham.",
            "Pulp Fiction": "Kisah kehidupan dua pembunuh bayaran mafia, seorang petinju, seorang gangster dan istrinya, serta sepasang perampok restoran yang saling terhubung dalam empat cerita tentang kekerasan dan penebusan.",
            "Forrest Gump": "Kisah hidup Forrest Gump, seorang pria dengan IQ rendah yang secara tidak sengaja menjadi bagian dari peristiwa-peristiwa penting dalam sejarah Amerika, dari era Kennedy hingga era modern.",
            "Spirited Away": "Selama perpindahan keluarganya ke pinggiran kota, seorang gadis berusia 10 tahun yang murung tersesat ke dunia yang dikuasai oleh dewa-dewa, penyihir, dan roh, di mana manusia diubah menjadi binatang.",
            "The Lion King": "Pangeran singa Simba dan ayahnya menjadi target pamannya yang pahit, yang ingin naik takhta sendiri. Kisah abadi tentang keluarga, tanggung jawab, dan penebusan."
        };
        
        return descriptions[title] || "No description available.";
    }
    
    // Helper function to get trailer links
    function getTrailerLink(title) {
        const trailerLinks = {
            "FAST X": "https://youtu.be/32RAq6JzY-w?si=QSX4EaEmwpSepJD-",
            "Oppenheimer": "https://www.youtube.com/watch?v=uYPbbksJxIg",
            "Avengers: Endgame": "https://www.youtube.com/watch?v=TcMBFSGVi1c",
            "Spider-Man: No Way Home": "https://www.youtube.com/watch?v=JfVOs4VSpmA",
            "The Matrix": "https://youtu.be/vKQi3bBA1y8?si=iqFHAq2pZsLu3guC",
            "Interstellar": "https://www.youtube.com/watch?v=zSWdZVtXT7E",
            "Parasite": "https://www.youtube.com/watch?v=5xH0HfJHsaY",
            "Joker": "https://www.youtube.com/watch?v=zAGVQLHvwOY",
            "Miracle In Cell No.7": "https://youtu.be/0uf6QUacVgs?si=lcMsBxHycLwfzMQj",
            "KKN di Desa Penari": "https://youtu.be/PAMx9m4Z2V4?si=n9yQEE-ZPA09mgRI",
            "Dilan 1991": "https://youtu.be/nwhB2Hb7g5c?si=7KVobnFndDET-Omg",
            "Agak Laen": "https://youtu.be/0YLSPyGA4h0?si=xuxnqPrlMG0IAclI",
            "Pengabdi Setan": "https://youtu.be/0hSptYxWB3E?si=WmsM-5wOTB6NX4pB",
            "Gundala": "https://youtu.be/8rauD1vxMCw?si=-eT43_VmIpHmts48",
            "Marlina Si Pembunuh dalam Empat Babak": "https://youtu.be/Ikgy2Xukwng?si=ZpXrOL2JYMigdOi9",
            "Keluarga Cemara": "https://youtu.be/sGaeDzD_3o0?si=BlbiKmXo8C90dyvD",
            "The Godfather": "https://www.youtube.com/watch?v=sY1S34973zA",
            "Inception": "https://www.youtube.com/watch?v=YoHD9XEInc0",
            "The Shawshank Redemption": "https://www.youtube.com/watch?v=6hB3S9bIaco",
            "The Dark Knight": "https://www.youtube.com/watch?v=EXeTwQWrcwY",
            "Pulp Fiction": "https://www.youtube.com/watch?v=s7EdQ4FqbhY",
            "Forrest Gump": "https://www.youtube.com/watch?v=bLvqoHBptjg",
            "Spirited Away": "https://www.youtube.com/watch?v=ByXuk9QqQkk",
            "The Lion King": "https://www.youtube.com/watch?v=4sj1MT05lAA"
        };
        return trailerLinks[title] || "#";
    }
    
    // Remove the old event listeners since we're now using onclick in the HTML
    document.removeEventListener('DOMContentLoaded', function() {
        const movieCards = document.querySelectorAll('.movie-card');
        movieCards.forEach(card => {
            card.removeEventListener('click', function() {
                // ... old click handler ...
            });
        });
    }); 
