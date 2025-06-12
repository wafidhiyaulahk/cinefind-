// API Configuration
const API_KEY = 'ba6f7d3b063751fb2bea48683e263f63';
const BASE_URL = 'https://api.themoviedb.org/3';
const IMAGE_BASE_URL = 'https://image.tmdb.org/t/p';

// DOM Elements
const featuredMoviesContainer = document.getElementById('featuredMovies');
const topRatedMoviesContainer = document.getElementById('topRatedMovies');
const upcomingMoviesContainer = document.getElementById('upcomingMovies');
const searchInput = document.querySelector('.hero-search');
const searchButton = document.querySelector('.search-button');
const movieModal = document.getElementById('movieModal');
const movieModalClose = movieModal.querySelector('.close');
const modalBody = document.querySelector('.modal-body');

// State Management
let currentUser = null;
let watchlist = JSON.parse(localStorage.getItem('watchlist')) || [];
let registeredUsers = JSON.parse(localStorage.getItem('registeredUsers')) || [];

// Add genre mapping
const genreMap = {
    'action': 28,
    'comedy': 35,
    'drama': 18,
    'horror': 27,
    'romance': 10749
};

// Load More Functionality
let featuredPage = 1;
let topRatedPage = 1;
let upcomingPage = 1;
let indonesianPage = 1;
const moviesPerPage = 8;

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    loadFeaturedMovies();
    loadTopRatedMovies();
    loadUpcomingMovies();
    loadIndonesianMovies();
    setupEventListeners();
    initializeUserState();
    
    // Add event listeners for load more buttons
    document.getElementById('loadMoreFeatured').addEventListener('click', loadMoreFeatured);
    document.getElementById('loadMoreTopRated').addEventListener('click', loadMoreTopRated);
    document.getElementById('loadMoreUpcoming').addEventListener('click', loadMoreUpcoming);
    document.getElementById('loadMoreIndonesian').addEventListener('click', loadMoreIndonesian);
});

function setupEventListeners() {
    searchInput.addEventListener('input', debounce(handleSearch, 300));
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleSearch();
    });
    movieModalClose.addEventListener('click', () => {
        movieModal.style.display = 'none';
    });
    window.addEventListener('click', (e) => {
        if (e.target === movieModal) {
            movieModal.style.display = 'none';
        }
    });
    document.getElementById('genreFilter').addEventListener('change', handleFilterChange);
    document.getElementById('yearFilter').addEventListener('change', handleFilterChange);
    document.getElementById('ratingFilter').addEventListener('change', handleFilterChange);
    
    // Add scroll event listener for navbar transparency
    window.addEventListener('scroll', () => {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Add input event listener to handle clearing
    searchInput.addEventListener('input', () => {
        if (searchInput.value === '') {
            // Reset page numbers
            featuredPage = 1;
            topRatedPage = 1;
            upcomingPage = 1;
            indonesianPage = 1;
            
            // Reload all sections
            loadFeaturedMovies();
            loadTopRatedMovies();
            loadUpcomingMovies();
            loadIndonesianMovies();
            
            // Show all sections
            document.querySelector('.top-rated-movies').style.display = 'block';
            document.querySelector('.coming-soon-movies').style.display = 'block';
            document.querySelector('.indonesian-movies').style.display = 'block';
            
            // Reset section titles
            document.querySelector('.featured-movies h2').textContent = 'Featured Movies';
            document.querySelector('.top-rated-movies h2').textContent = 'Top Rated Movies';
            document.querySelector('.coming-soon-movies h2').textContent = 'Coming Soon';
            document.querySelector('.indonesian-movies h2').textContent = 'Indonesian Movies';
            
            // Show all load more buttons
            document.getElementById('loadMoreFeatured').style.display = 'block';
            document.getElementById('loadMoreTopRated').style.display = 'block';
            document.getElementById('loadMoreUpcoming').style.display = 'block';
            document.getElementById('loadMoreIndonesian').style.display = 'block';
        }
    });
}

// Add debounce function to prevent too many API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

async function fetchMovies(endpoint, params = {}) {
    const queryParams = new URLSearchParams({ api_key: API_KEY, ...params });
    try {
        const response = await fetch(`${BASE_URL}${endpoint}?${queryParams}`);
        return await response.json();
    } catch (error) {
        console.error('Error fetching movies:', error);
        return null;
    }
}

async function loadFeaturedMovies() {
    try {
        const response = await fetch(`https://api.themoviedb.org/3/movie/popular?api_key=${API_KEY}&page=${featuredPage}`);
        const data = await response.json();
        
        const featuredMoviesContainer = document.getElementById('featuredMovies');
        
        // Clear container if it's the first page
        if (featuredPage === 1) {
            featuredMoviesContainer.innerHTML = '';
        }
        
        // Add movies to the container
        data.results.forEach(movie => {
            if (movie.poster_path && movie.title) { // Tambahkan pengecekan di sini
                const movieCard = createMovieCard(movie);
                featuredMoviesContainer.appendChild(movieCard);
            }
        });
        
        // Hide load more button if no more pages
        if (data.page >= data.total_pages) {
            document.getElementById('loadMoreFeatured').style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading featured movies:', error);
    }
}

async function loadTopRatedMovies() {
    try {
        const response = await fetch(`https://api.themoviedb.org/3/movie/top_rated?api_key=${API_KEY}&page=${topRatedPage}`);
        const data = await response.json();
        
        const topRatedMoviesContainer = document.getElementById('topRatedMovies');
        
        // Clear container if it's the first page
        if (topRatedPage === 1) {
            topRatedMoviesContainer.innerHTML = '';
        }
        
        // Add movies to the container
        data.results.forEach(movie => {
            if (movie.poster_path && movie.title) { // Tambahkan pengecekan di sini
                const movieCard = createMovieCard(movie);
                topRatedMoviesContainer.appendChild(movieCard);
            }
        });
        
        // Hide load more button if no more pages
        if (data.page >= data.total_pages) {
            document.getElementById('loadMoreTopRated').style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading top rated movies:', error);
    }
}

async function loadUpcomingMovies() {
    try {
        const response = await fetch(`https://api.themoviedb.org/3/movie/upcoming?api_key=${API_KEY}&page=${upcomingPage}`);
        const data = await response.json();
        
        const upcomingMoviesContainer = document.getElementById('upcomingMovies');
        
        // Clear container if it's the first page
        if (upcomingPage === 1) {
            upcomingMoviesContainer.innerHTML = '';
        }
        
        // Add movies to the container
        data.results.forEach(movie => {
            if (movie.poster_path && movie.title) { // Tambahkan pengecekan di sini
                const movieCard = createMovieCard(movie);
                upcomingMoviesContainer.appendChild(movieCard);
            }
        });
        
        // Hide load more button if no more pages
        if (data.page >= data.total_pages) {
            document.getElementById('loadMoreUpcoming').style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading upcoming movies:', error);
    }
}

async function loadIndonesianMovies() {
    try {
        const response = await fetch(`https://api.themoviedb.org/3/discover/movie?api_key=${API_KEY}&with_original_language=id&page=${indonesianPage}`);
        const data = await response.json();
        
        const indonesianMoviesContainer = document.getElementById('indonesianMovies');
        
        // Clear container if it's the first page
        if (indonesianPage === 1) {
            indonesianMoviesContainer.innerHTML = '';
        }
        
        // Add movies to the container
        data.results.forEach(movie => {
            if (movie.poster_path && movie.title) { // Tambahkan pengecekan di sini
                const movieCard = createMovieCard(movie);
                indonesianMoviesContainer.appendChild(movieCard);
            }
        });
        
        // Hide load more button if no more pages
        if (data.page >= data.total_pages) {
            document.getElementById('loadMoreIndonesian').style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading Indonesian movies:', error);
    }
}

function displayMovies(movies, container) {
    container.innerHTML = '';
    movies.forEach(movie => container.appendChild(createMovieCard(movie)));
}

function createMovieCard(movie) {
    const card = document.createElement('div');
    card.className = 'movie-card';
    
    const isInWatchlist = movie.in_watchlist || false;
    
    card.innerHTML = `
        <img src="${IMAGE_BASE_URL}/w500${movie.poster_path}" alt="${movie.title}" class="movie-poster">
        <div class="movie-info">
            <h3 class="movie-title">${movie.title}</h3>
            <div class="movie-rating"><i class="fas fa-star"></i> ${movie.vote_average.toFixed(1)}</div>
            <button class="watchlist-btn ${isInWatchlist ? 'in-list' : ''}" 
                    data-movie-id="${movie.id}"
                    onclick="event.stopPropagation(); toggleWatchlist('${movie.id}', '${movie.title.replace(/'/g, "\\'")}', '${movie.poster_path}')">
                <i class="fas ${isInWatchlist ? 'fa-check' : 'fa-plus'}"></i>
                ${isInWatchlist ? 'In My List' : 'Add to List'}
            </button>
        </div>`;
    
    card.addEventListener('click', () => showMovieDetails(movie));
    return card;
}

async function showMovieDetails(movie) {
    const data = await fetchMovies(`/movie/${movie.id}`, { append_to_response: 'credits,videos' });
    if (!data) return;

    const genres = data.genres?.map(g => `<span>${g.name}</span>`).join('') || '';
    const trailerKey = data.videos.results.find(v => v.type === 'Trailer')?.key;
    
    // Get director
    const director = data.credits.crew.find(person => person.job === 'Director');
    
    // Get top 5 cast members
    const cast = data.credits.cast.slice(0, 5).map(person => ({
        name: person.name,
        character: person.character,
        profile_path: person.profile_path
    }));
    
    const isInWatchlist = movie.in_watchlist || false;

    const modalContent = `
        <div class="modal-content">
            <span class="close" onclick="closeMovieModal()">&times;</span>
            <div class="movie-details">
                <div class="movie-header">
                    <img src="${movie.poster_path ? IMAGE_BASE_URL + '/w500' + movie.poster_path : 'https://via.placeholder.com/500x750?text=No+Image'}" 
                         alt="${movie.title || 'No Title'} Poster" 
                         class="movie-poster-large">
                    <div class="movie-info-detailed">
                        <div class="movie-title-rating">
                            <h2>${movie.title || 'No Title'}</h2>
                            <div class="movie-rating">
                                <i class="fas fa-star"></i>
                                ${movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A'}
                            </div>
                        </div>
                        <p class="release-date">${movie.release_date || 'Unknown Release Date'}</p>
                        <p class="overview">${movie.overview || 'No overview available.'}</p>
                        <div class="genres">${genres || ''}</div>
                        
                        <div class="movie-crew">
                            <div class="director">
                                <h3>Director</h3>
                                <p>${director ? director.name : 'Not available'}</p>
                            </div>
                            <div class="cast">
                                <h3>Cast</h3>
                                <div class="cast-list">
                                    ${cast && cast.length > 0 ? cast.map(person => `
                                        <div class="cast-member">
                                            <img src="${person.profile_path ? IMAGE_BASE_URL + '/w185' + person.profile_path : 'https://via.placeholder.com/185x278?text=No+Image'}" 
                                                 alt="${person.name || 'Unknown'}" 
                                                 class="cast-image">
                                            <div class="cast-info">
                                                <p class="cast-name">${person.name || 'Unknown'}</p>
                                                <p class="cast-character">${person.character || ''}</p>
                                            </div>
                                        </div>
                                    `).join('') : '<p>No cast information available.</p>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                ${trailerKey ? `
                    <div class="trailer">
                        <h3>Trailer</h3>
                        <iframe width="100%" height="400" src="https://www.youtube.com/embed/${trailerKey}" frameborder="0" allowfullscreen></iframe>
                    </div>
                ` : ''}
                <div class="movie-reviews-section" style="margin-top:2rem;">
                    <h3>Ulasan Pengguna</h3>
                    <div id="movieReviewsList"></div>
                    <div id="reviewFormContainer"></div>
                </div>
            </div>
        </div>
    `;
    
    movieModal.innerHTML = modalContent;
    movieModal.style.display = 'block';
    // Load reviews for this movie
    loadMovieReviews(movie.id);
}

async function handleSearch() {
    const query = searchInput.value.trim();
    
    if (query === '') {
        // If search is empty, reload all movie sections
        loadFeaturedMovies();
        loadTopRatedMovies();
        loadUpcomingMovies();
        loadIndonesianMovies();
        
        // Show all sections
        document.querySelector('.top-rated-movies').style.display = 'block';
        document.querySelector('.coming-soon-movies').style.display = 'block';
        document.querySelector('.indonesian-movies').style.display = 'block';
        
        // Reset the section title
        const sectionTitle = document.querySelector('.featured-movies h2');
        if (sectionTitle) {
            sectionTitle.textContent = 'Featured Movies';
        }
        return;
    }

    try {
        // Show loading state
        const featuredMoviesContainer = document.getElementById('featuredMovies');
        featuredMoviesContainer.innerHTML = '<div class="loading" style="text-align: center; padding: 1rem; width: 100%;">Searching movies...</div>';

        // Hide other sections
        document.querySelector('.top-rated-movies').style.display = 'none';
        document.querySelector('.coming-soon-movies').style.display = 'none';
        document.querySelector('.indonesian-movies').style.display = 'none';

        // Fetch search results from TMDB API with language parameter
        const response = await fetch(`${BASE_URL}/search/movie?api_key=${API_KEY}&query=${encodeURIComponent(query)}&language=en-US&include_adult=false&page=1`);
        const data = await response.json();
        
        if (data?.results && data.results.length > 0) {
            // Clear all movie sections
            document.getElementById('featuredMovies').innerHTML = '';
            document.getElementById('topRatedMovies').innerHTML = '';
            document.getElementById('upcomingMovies').innerHTML = '';
            document.getElementById('indonesianMovies').innerHTML = '';
            
            // Display search results in the featured movies section
            data.results.forEach(movie => {
                if (movie.poster_path) { // Only show movies with posters
                    const movieCard = createMovieCard(movie);
                    featuredMoviesContainer.appendChild(movieCard);
                }
            });
            
            // Update the section title with the search query
            const sectionTitle = document.querySelector('.featured-movies h2');
            if (sectionTitle) {
                sectionTitle.textContent = `Movies: "${query}"`;
            }

            // Hide other sections' load more buttons
            document.getElementById('loadMoreTopRated').style.display = 'none';
            document.getElementById('loadMoreUpcoming').style.display = 'none';
            document.getElementById('loadMoreIndonesian').style.display = 'none';

            // If no results with posters, show a message
            if (featuredMoviesContainer.children.length === 0) {
                showErrorState(featuredMoviesContainer, 'No movies found with posters for your search. Try a different search term.');
            }
        } else {
            showErrorState(featuredMoviesContainer, 'No movies found for your search. Try a different search term.');
        }
    } catch (error) {
        console.error('Error searching movies:', error);
        showErrorState(featuredMoviesContainer, 'Error searching movies. Please try again.');
    }
}

function showErrorState(container, message) {
    container.innerHTML = `
        <div class="error-message" style="
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
            margin: 1rem auto;
            max-width: 500px;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
        ">
            <i class="fas fa-exclamation-circle" style="
                font-size: 2rem;
                color: #ff4d4d;
                margin-bottom: 0.5rem;
            "></i>
            <p style="
                font-size: 1rem;
                margin-bottom: 0.25rem;
                color: #ffffff;
                line-height: 1.4;
                text-align: center;
                width: 100%;
            ">${message}</p>
            <p class="error-suggestion" style="
                font-size: 0.9rem;
                color: #cccccc;
                margin-top: 0.25rem;
                text-align: center;
                width: 100%;
            ">Try searching with different keywords or check your spelling.</p>
        </div>
    `;
}

async function handleFilterChange() {
    const genre = document.getElementById('genreFilter').value;
    const year = document.getElementById('yearFilter').value;
    const rating = document.getElementById('ratingFilter').value;
    
    const params = {};
    if (genre) {
        params.with_genres = genreMap[genre];
    }
    if (year) params.primary_release_year = year;
    if (rating) params['vote_average.gte'] = rating;
    
    // Show loading state
    const featuredMoviesContainer = document.getElementById('featuredMovies');
    featuredMoviesContainer.innerHTML = '<div class="loading">Loading movies...</div>';
    
    try {
        const data = await fetchMovies('/discover/movie', params);
        if (data?.results) {
            // Clear all movie sections
            document.getElementById('featuredMovies').innerHTML = '';
            document.getElementById('topRatedMovies').innerHTML = '';
            document.getElementById('upcomingMovies').innerHTML = '';
            
            // Display filtered results in the featured movies section
            displayMovies(data.results, featuredMoviesContainer);
            
            // Update the section title
            const sectionTitle = document.querySelector('.featured-movies h2');
            if (sectionTitle) {
                let filterText = 'Filtered Movies';
                if (genre || year || rating) {
                    filterText = 'Filtered Movies: ';
                    const filters = [];
                    if (genre) filters.push(`Genre: ${genre.charAt(0).toUpperCase() + genre.slice(1)}`);
                    if (year) filters.push(`Year: ${year}`);
                    if (rating) filters.push(`Rating: ${rating}+`);
                    filterText += filters.join(', ');
                }
                sectionTitle.textContent = filterText;
            }
        } else {
            showErrorState(featuredMoviesContainer, 'No movies found for the selected filters.');
        }
    } catch (error) {
        showErrorState(featuredMoviesContainer, 'Error loading movies. Please try again.');
    }
}

async function toggleWatchlist(movieId, movieTitle, posterPath) {
    try {
        const isInWatchlist = document.querySelector(`.watchlist-btn[data-movie-id="${movieId}"]`)?.classList.contains('in-list');
        const action = isInWatchlist ? 'remove' : 'add';
        
        // Validate required data
        if (!movieId) {
            throw new Error('Movie ID is required');
        }
        if (action === 'add' && (!movieTitle || !posterPath)) {
            throw new Error('Movie title and poster path are required for adding to watchlist');
        }
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('movie_id', movieId);
        formData.append('movie_title', movieTitle || '');
        formData.append('poster_path', posterPath || '');
        
        console.log('Sending watchlist request:', {
            action,
            movieId,
            movieTitle,
            posterPath
        });
        
        const response = await fetch('watchlist_handler.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Watchlist response:', data);
        
        if (data.success) {
            // Update all watchlist buttons for this movie
            const buttons = document.querySelectorAll(`.watchlist-btn[data-movie-id="${movieId}"], .add-to-watchlist[data-movie-id="${movieId}"]`);
            buttons.forEach(button => {
                if (button) {
                    const newState = action === 'add';
                    button.innerHTML = `<i class="fas ${newState ? 'fa-check' : 'fa-plus'}"></i> ${newState ? 'In My List' : 'Add to List'}`;
                    button.classList.toggle('in-list', newState);
                }
            });
            
            // Show notification
            showNotification(data.message, 'success');
            
            // If we're on the My List page, refresh the view
            if (window.location.pathname.includes('my-watchlist.php')) {
                location.reload();
            }
        } else {
            const errorMessage = data.debug ? 
                `${data.message}\nDebug info: ${JSON.stringify(data.debug, null, 2)}` : 
                data.message;
            console.error('Watchlist error:', errorMessage);
            showNotification(data.message || 'Failed to update watchlist', 'error');
        }
    } catch (error) {
        console.error('Error updating watchlist:', error);
        showNotification(`Failed to update watchlist: ${error.message}`, 'error');
    }
}

// Add notification function if not exists
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => notification.classList.add('show'), 100);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Login/Signup Functions
function toggleLoginSignup() {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    
    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        signupForm.style.display = 'none';
    } else {
        loginForm.style.display = 'none';
        signupForm.style.display = 'block';
    }
}

function openLoginModal() {
    const modal = document.getElementById('loginModal');
    modal.style.display = 'block';
    // Reset to login form when opening modal
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('signupForm').style.display = 'none';
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    modal.style.display = 'none';
}

async function handleLogin(event) {
    event.preventDefault();
    const form = event.target;
    const email = form.querySelector('input[type="email"]').value;
    const password = form.querySelector('input[type="password"]').value;

    // Find user in registered users
    const user = registeredUsers.find(u => u.email === email && u.password === password);

    if (user) {
        currentUser = { name: user.name, email: user.email };
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        updateUIForLoggedInUser();
        closeLoginModal();
        alert('Successfully logged in!');
    } else {
        alert('Invalid email or password. Please try again or sign up if you don\'t have an account.');
    }
}

async function handleSignup(event) {
    event.preventDefault();
    const form = event.target;
    const name = form.querySelector('input[type="text"]').value;
    const email = form.querySelector('input[type="email"]').value;
    const password = form.querySelector('input[type="password"]').value;
    const confirmPassword = form.querySelectorAll('input[type="password"]')[1].value;

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }

    // Check if email is already registered
    if (registeredUsers.some(user => user.email === email)) {
        alert('This email is already registered. Please use a different email or login.');
        return;
    }

    try {
        // Add new user to registered users
        const newUser = { name, email, password };
        registeredUsers.push(newUser);
        localStorage.setItem('registeredUsers', JSON.stringify(registeredUsers));

        // Log in the new user
        currentUser = { name, email };
        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        updateUIForLoggedInUser();
        closeLoginModal();
        alert('Account created successfully!');
    } catch (error) {
        alert('Signup failed. Please try again.');
    }
}

function updateUIForLoggedInUser() {
    const loginBtn = document.querySelector('.login-btn');
    const userDropdown = document.querySelector('.user-dropdown');
    const userName = document.querySelector('.user-name');
    
    if (loginBtn && userDropdown && userName) {
        const displayName = currentUser.name || currentUser.email.split('@')[0];
        loginBtn.innerHTML = `<i class="fas fa-user"></i>${displayName}`;
        userName.textContent = displayName;
        loginBtn.onclick = toggleUserDropdown;
        userDropdown.style.display = 'block';
    }
}

function updateUIForLoggedOutUser() {
    const loginBtn = document.querySelector('.login-btn');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (loginBtn && userDropdown) {
        loginBtn.innerHTML = 'Login';
        loginBtn.onclick = openLoginModal;
        userDropdown.style.display = 'none';
    }
}

function toggleUserDropdown() {
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdown) {
        userDropdown.style.display = userDropdown.style.display === 'none' ? 'block' : 'none';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.querySelector('.user-menu');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (userMenu && userDropdown && !userMenu.contains(event.target)) {
        userDropdown.style.display = 'none';
    }
});

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../logout.php';
    }
    return false;
}

function initializeUserState() {
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        updateUIForLoggedInUser();
    }
}

function closeMovieModal() {
    const movieModal = document.getElementById('movieModal');
    movieModal.style.display = 'none';
}

// About Modal Functions
function openAboutModal() {
    const modal = document.getElementById('aboutModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
}

function closeAboutModal() {
    const modal = document.getElementById('aboutModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable scrolling
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const aboutModal = document.getElementById('aboutModal');
    const loginModal = document.getElementById('loginModal');
    const movieModal = document.getElementById('movieModal');
    
    if (event.target == aboutModal) {
        closeAboutModal();
    }
    if (event.target == loginModal) {
        closeLoginModal();
    }
    if (event.target == movieModal) {
        closeMovieModal();
    }
}

// Load more functions
function loadMoreFeatured() {
    const button = document.getElementById('loadMoreFeatured');
    button.classList.add('loading');
    featuredPage++;
    
    setTimeout(() => {
        loadFeaturedMovies();
        button.classList.remove('loading');
    }, 500);
}

function loadMoreTopRated() {
    const button = document.getElementById('loadMoreTopRated');
    button.classList.add('loading');
    topRatedPage++;
    
    setTimeout(() => {
        loadTopRatedMovies();
        button.classList.remove('loading');
    }, 500);
}

function loadMoreUpcoming() {
    const button = document.getElementById('loadMoreUpcoming');
    button.classList.add('loading');
    upcomingPage++;
    
    setTimeout(() => {
        loadUpcomingMovies();
        button.classList.remove('loading');
    }, 500);
}

function loadMoreIndonesian() {
    const button = document.getElementById('loadMoreIndonesian');
    button.classList.add('loading');
    indonesianPage++;
    
    setTimeout(() => {
        loadIndonesianMovies();
        button.classList.remove('loading');
    }, 500);
}

// Function to show all movies
function showAllMovies() {
    // Show featured movies
    updateMovieGrid(featuredMovies, 'featuredMovies');
    
    // Show Indonesian movies
    updateMovieGrid(indonesianMovies, 'indonesianMovies');
    
    // Show top rated movies
    updateMovieGrid(topRatedMovies, 'topRatedMovies');
    
    // Show upcoming movies
    updateMovieGrid(upcomingMovies, 'upcomingMovies');
}

// Function to update movie grid
function updateMovieGrid(movies, containerId = 'featuredMovies') {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';
    
    movies.forEach(movie => {
        const movieCard = createMovieCard(movie);
        container.appendChild(movieCard);
    });
}

// Add event listener for My List navigation
document.querySelector('.nav-link[href="#"][textContent="My List"]').addEventListener('click', async (e) => {
    e.preventDefault();
    await showMyList();
});

async function showMyList() {
    // Hide other sections
    document.querySelector('.top-rated-movies').style.display = 'none';
    document.querySelector('.coming-soon-movies').style.display = 'none';
    document.querySelector('.indonesian-movies').style.display = 'none';

    // Update section title
    const sectionTitle = document.querySelector('.featured-movies h2');
    if (sectionTitle) {
        sectionTitle.textContent = 'My List';
    }

    // Clear and show loading state
    const featuredMoviesContainer = document.getElementById('featuredMovies');
    featuredMoviesContainer.innerHTML = '<div class="loading">Loading your movies...</div>';

    try {
        // Get watchlist from database
        const formData = new FormData();
        formData.append('action', 'get');
        
        const response = await fetch('watchlist_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.watchlist.length === 0) {
                showErrorState(featuredMoviesContainer, 'Your list is empty. Add some movies to get started!');
                return;
            }
            
            // Clear container and display movies
            featuredMoviesContainer.innerHTML = '';
            
            // Fetch additional movie details from TMDB API
            const moviePromises = data.watchlist.map(movie => 
                fetch(`${BASE_URL}/movie/${movie.movie_id}?api_key=${API_KEY}`).then(res => res.json())
            );
            
            const movies = await Promise.all(moviePromises);
            
            movies.forEach(movie => {
                if (movie.poster_path) {
                    movie.in_watchlist = true; // Mark as in watchlist
                    const movieCard = createMovieCard(movie);
                    featuredMoviesContainer.appendChild(movieCard);
                }
            });
        } else {
            showErrorState(featuredMoviesContainer, data.message || 'Failed to load watchlist');
        }
        
        // Hide load more buttons
        document.getElementById('loadMoreTopRated').style.display = 'none';
        document.getElementById('loadMoreUpcoming').style.display = 'none';
        document.getElementById('loadMoreIndonesian').style.display = 'none';
        document.getElementById('loadMoreFeatured').style.display = 'none';
        
    } catch (error) {
        console.error('Error loading watchlist:', error);
        showErrorState(featuredMoviesContainer, 'Error loading your list. Please try again.');
    }
}

function loadMovieReviews(movieId) {
    const reviewsList = document.getElementById('movieReviewsList');
    const reviewFormContainer = document.getElementById('reviewFormContainer');
    if (!reviewsList || !reviewFormContainer) return;
    reviewsList.innerHTML = '<div style="color:#aaa">Memuat ulasan...</div>';
    reviewFormContainer.innerHTML = '';

    // Ambil review dari backend
    fetch(`pengguna/review_handler.php?movie_id=${movieId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.reviews.length > 0) {
                reviewsList.innerHTML = data.reviews.map(r => `
                    <div class="review-item" style="border-bottom:1px solid #333;padding:0.5rem 0;">
                        <span class="review-username" style="color:#e50914;font-weight:bold;">${r.username}</span>
                        <span class="review-date" style="color:#aaa;font-size:0.9em;margin-left:8px;">${(new Date(r.created_at)).toLocaleString()}</span>
                        <div class="review-text" style="color:#fff;margin-top:2px;">${r.review_text}</div>
                    </div>
                `).join('');
            } else {
                reviewsList.innerHTML = '<div style="color:#aaa">Belum ada ulasan.</div>';
            }
        });

    // Cek login (dari session PHP, bisa juga dari JS jika ada)
    fetch('pengguna/check_login.php')
        .then(res => res.json())
        .then(data => {
            if (data.logged_in) {
                reviewFormContainer.innerHTML = `
                    <form id="reviewForm">
                        <textarea name="review_text" placeholder="Tulis ulasan Anda..." required style="width:100%;min-height:60px;margin-bottom:0.5rem;border-radius:4px;border:1px solid #333;background:#222;color:#fff;padding:8px;"></textarea>
                        <button type="submit" style="background:#e50914;color:#fff;border:none;padding:0.5rem 1.2rem;border-radius:4px;cursor:pointer;font-weight:500;">Kirim Ulasan</button>
                    </form>
                `;
                document.getElementById('reviewForm').onsubmit = function(e) {
                    e.preventDefault();
                    const reviewText = this.review_text.value.trim();
                    if (!reviewText) return;
                    fetch('pengguna/review_handler.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `movie_id=${encodeURIComponent(movieId)}&review_text=${encodeURIComponent(reviewText)}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.reset();
                            loadMovieReviews(movieId); // Refresh list
                        } else {
                            alert(data.message || 'Gagal menambah ulasan');
                        }
                    });
                }
            }
        });
}


