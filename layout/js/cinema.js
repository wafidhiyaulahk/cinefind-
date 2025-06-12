// Movie-related functionality
class CinemaManager {
    constructor() {
        this.initializeEventListeners();
        this.setupTrailerButtons();
        this.setupMovieCards();
    }

    // Initialize all event listeners
    initializeEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const clearSearch = document.getElementById('clearSearch');

        if (searchInput && searchButton) {
            searchButton.addEventListener('click', () => this.handleSearch(searchInput.value));
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleSearch(searchInput.value);
                }
            });

            // Real-time search with debounce
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 300);
            });
        }

        if (clearSearch) {
            clearSearch.addEventListener('click', () => this.clearSearchResults());
        }
    }

    // Setup trailer buttons for all movie cards
    setupTrailerButtons() {
        document.querySelectorAll('.trailer-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const movieTitle = button.closest('.movie-card').dataset.title;
                this.searchTrailer(movieTitle);
            });
        });
    }

    // Setup movie card interactions
    setupMovieCards() {
        document.querySelectorAll('.movie-card').forEach(card => {
            card.addEventListener('click', () => {
                const movieData = {
                    title: card.dataset.title,
                    year: card.dataset.year,
                    rating: card.dataset.rating,
                    poster: card.querySelector('.movie-poster').src
                };
                this.showMovieDetails(movieData);
            });
        });
    }

    // Handle search functionality
    handleSearch(query) {
        query = query.toLowerCase().trim();
        
        if (!query) {
            this.clearSearchResults();
            return;
        }

        // Check if this is a trailer search
        if (this.isTrailerSearch(query)) {
            const movieTitle = this.getMovieTitleFromTrailerSearch(query);
            this.searchTrailer(movieTitle);
            return;
        }

        this.performMovieSearch(query);
    }

    // Check if search is for trailer
    isTrailerSearch(query) {
        return query.toLowerCase().startsWith('trailer ');
    }

    // Get movie title from trailer search
    getMovieTitleFromTrailerSearch(query) {
        return query.toLowerCase().replace('trailer ', '').trim();
    }

    // Search for movie trailer on YouTube
    searchTrailer(movieTitle) {
        const searchQuery = encodeURIComponent(movieTitle + ' official trailer');
        window.open(`https://www.youtube.com/results?search_query=${searchQuery}`, '_blank');
    }

    // Perform movie search
    performMovieSearch(query) {
        const movieCards = document.querySelectorAll('.movie-card');
        const movieSections = document.querySelectorAll('.featured-movies, .recommended-movies');
        const searchMessage = document.getElementById('searchMessage');
        const searchQuery = document.getElementById('searchQuery');
        const noResults = document.querySelector('.no-results');

        let hasResults = false;
        let totalResults = 0;

        // Search through all movie cards
        movieCards.forEach(card => {
            const title = card.dataset.title.toLowerCase();
            const year = card.dataset.year.toLowerCase();
            const rating = card.dataset.rating.toLowerCase();
            
            if (title.includes(query) || year.includes(query) || rating.includes(query)) {
                card.style.display = 'block';
                hasResults = true;
                totalResults++;
            } else {
                card.style.display = 'none';
            }
        });

        // Update section visibility
        movieSections.forEach(section => {
            const visibleCards = section.querySelectorAll('.movie-card[style="display: block"]');
            section.style.display = visibleCards.length > 0 ? 'block' : 'none';
        });

        // Update search message
        if (searchMessage && searchQuery) {
            searchMessage.style.display = 'block';
            searchQuery.textContent = query;
            searchMessage.querySelector('p').textContent = 
                `Found ${totalResults} movie${totalResults !== 1 ? 's' : ''} for: "${query}"`;
        }

        // Show/hide no results message
        if (noResults) {
            noResults.style.display = !hasResults ? 'block' : 'none';
        }

        // Scroll to search message smoothly
        if (searchMessage) {
            searchMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Clear search results
    clearSearchResults() {
        const movieCards = document.querySelectorAll('.movie-card');
        const movieSections = document.querySelectorAll('.featured-movies, .recommended-movies');
        const searchMessage = document.getElementById('searchMessage');
        const searchInput = document.getElementById('searchInput');
        const noResults = document.querySelector('.no-results');

        // Show all movie cards
        movieCards.forEach(card => {
            card.style.display = 'block';
        });

        // Show all sections
        movieSections.forEach(section => {
            section.style.display = 'block';
        });

        // Hide search message and no results
        if (searchMessage) searchMessage.style.display = 'none';
        if (noResults) noResults.style.display = 'none';

        // Clear search input
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
    }

    // Show movie details in modal
    showMovieDetails(movieData) {
        const modal = document.getElementById('movieModal');
        if (!modal) return;

        const modalBody = modal.querySelector('.modal-body');
        if (!modalBody) return;

        // Create movie details content
        modalBody.innerHTML = `
            <div class="movie-details">
                <img src="${movieData.poster}" alt="${movieData.title}" class="modal-poster">
                <div class="modal-info">
                    <h2>${movieData.title}</h2>
                    <div class="modal-year">${movieData.year}</div>
                    <div class="modal-rating">
                        <i class="fas fa-star"></i>${movieData.rating}
                    </div>
                    <button class="trailer-btn" onclick="cinemaManager.searchTrailer('${movieData.title}')">
                        <i class="fas fa-play"></i> Watch Trailer
                    </button>
                </div>
            </div>
        `;

        // Show modal
        modal.style.display = 'block';

        // Close modal when clicking outside
        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };

        // Close modal when clicking close button
        const closeBtn = modal.querySelector('.close');
        if (closeBtn) {
            closeBtn.onclick = () => {
                modal.style.display = 'none';
            };
        }
    }
}

// Initialize cinema manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.cinemaManager = new CinemaManager();
}); 