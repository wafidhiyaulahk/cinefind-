// DOM Elements
const moviesTableBody = document.querySelector('#movies-management-table-body'); // Corrected ID
const reviewsTableBody = document.querySelector('#reviews-table-body');
const usersTableBody = document.querySelector('#users-table-body');
const reviewFilterSelect = document.querySelector('#review-filter'); // Corrected ID
// const refreshBtn = document.querySelector('.refresh-btn'); // If you have one
const userSearchInput = document.querySelector('#userSearch'); // Corrected ID for user search
const pageTitle = document.querySelector('#page-title');

// Navigation Elements
const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
const contentSections = document.querySelectorAll('.content-section');
const logoutCancelBtn = document.querySelector('#logout-section .cancel-btn');
const logoutConfirmBtn = document.querySelector('#logout-section .confirm-btn');

// Admin Profile Dropdown
const adminProfile = document.querySelector('.admin-profile');
const dropdownMenu = document.querySelector('.admin-dropdown .dropdown-menu');


// --- Load Functions ---

async function loadMoviesData() {
    if (!moviesTableBody) {
        console.error('Movies table body not found');
        return;
    }
    moviesTableBody.innerHTML = '<tr><td colspan="8" class="text-center">Loading movies...</td></tr>';
    try {
        const response = await fetch('get_movies.php');
        const result = await response.json();
        if (result.status === 'success') {
            moviesTableBody.innerHTML = ''; // Clear loading message
            if (result.data.length === 0) {
                moviesTableBody.innerHTML = '<tr><td colspan="8" class="text-center">No movies found in local database.</td></tr>';
                return;
            }
            result.data.forEach(movie => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><img src="${movie.poster_path || '../assets/images/default-poster.png'}" alt="${movie.title}" style="width: 50px; height: auto; border-radius: 4px;"></td>
                    <td>${movie.title}</td>
                    <td>${movie.genre || 'N/A'}</td>
                    <td><span class="rating-badge">${movie.rating}</span></td>
                    <td>${movie.release_date ? movie.release_date.substring(0,4) : 'N/A'}</td>
                    <td>${movie.runtime || 'N/A'} min</td>
                    <td><span class="status-badge ${movie.status ? movie.status.toLowerCase().replace(' ', '-') : ''}">${movie.status || 'N/A'}</span></td>
                    <td>
                        <button class="action-btn view" title="View Details" onclick="viewLocalMovieDetails(${movie.id})"><i class="fas fa-eye"></i></button>
                        <button class="action-btn edit" title="Edit Movie" onclick="editLocalMovie(${movie.id})"><i class="fas fa-edit"></i></button>
                        <button class="action-btn delete" title="Delete Movie" onclick="deleteLocalMovie(${movie.id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                moviesTableBody.appendChild(row);
            });
        } else {
            moviesTableBody.innerHTML = `<tr><td colspan="8" class="text-center">Error loading movies: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching movies:', error);
        moviesTableBody.innerHTML = '<tr><td colspan="8" class="text-center">Failed to load movies. Check console.</td></tr>';
    }
}


async function loadReviewsData(filter = 'all') {
    if (!reviewsTableBody) {
        console.error('Reviews table body not found');
        return;
    }
    reviewsTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Loading reviews...</td></tr>';
    try {
        const response = await fetch(`get_reviews.php?status=${filter}`); // Pass filter if API supports it
        const result = await response.json();
        if (result.status === 'success') {
            reviewsTableBody.innerHTML = '';
            const reviewsToDisplay = result.data.filter(review => filter === 'all' || review.status.toLowerCase() === filter);

            if (reviewsToDisplay.length === 0) {
                reviewsTableBody.innerHTML = `<tr><td colspan="7" class="text-center">No reviews found for status: ${filter}.</td></tr>`;
                return;
            }
            reviewsToDisplay.forEach(review => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${review.id}</td>
                    <td>${review.user_name}</td>
                    <td>
                         <div style="display: flex; align-items: center;">
                            <img src="${review.movie_poster ? 'https://image.tmdb.org/t/p/w92' + review.movie_poster : '../assets/images/default-poster.png'}" alt="${review.movie_title}" style="width: 30px; height: 45px; object-fit: cover; margin-right: 10px; border-radius: 3px;">
                            <span>${review.movie_title}</span>
                        </div>
                    </td>
                    <td title="${review.review_text}" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${review.review_text}</td>
                    <td>${review.rating}/5</td>
                    <td><span class="status-badge ${review.status ? review.status.toLowerCase() : ''}">${review.status || 'N/A'}</span></td>
                    <td>
                        <button class="action-btn approve" title="Approve Review" onclick="updateReviewStatus(${review.id}, 'approved')"><i class="fas fa-check"></i></button>
                        <button class="action-btn reject" title="Reject Review" onclick="updateReviewStatus(${review.id}, 'rejected')"><i class="fas fa-times"></i></button>
                        <button class="action-btn delete" title="Delete Review" onclick="deleteDatabaseReview(${review.id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                reviewsTableBody.appendChild(row);
            });
        } else {
            reviewsTableBody.innerHTML = `<tr><td colspan="7" class="text-center">Error loading reviews: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching reviews:', error);
        reviewsTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Failed to load reviews. Check console.</td></tr>';
    }
}

async function loadUsersData(searchTerm = '') {
    if (!usersTableBody) {
        console.error('Users table body not found');
        return;
    }
    usersTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading users...</td></tr>';
    try {
        const response = await fetch('get_users.php');
        const result = await response.json();

        if (result.status === 'success') {
            usersTableBody.innerHTML = '';
            let usersToDisplay = result.data;
            if (searchTerm) {
                usersToDisplay = result.data.filter(user =>
                    user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    user.username.toLowerCase().includes(searchTerm.toLowerCase())
                );
            }

            if (usersToDisplay.length === 0) {
                usersTableBody.innerHTML = `<tr><td colspan="6" class="text-center">No users found ${searchTerm ? 'for "' + searchTerm + '"' : ''}.</td></tr>`;
                return;
            }

            usersToDisplay.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="${user.foto_profil || '../assets/images/default-avatar.png'}" alt="${user.name}" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                            <span>${user.name}</span>
                        </div>
                    </td>
                    <td>${user.email}</td>
                    <td><span class="status-badge ${user.role_text ? user.role_text.toLowerCase() : ''}">${user.role_text || 'N/A'}</span></td>
                    <td>${user.join_date}</td>
                    <td>
                        <button class="action-btn delete" title="Delete User" onclick="deleteDatabaseUser(${user.id})"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                usersTableBody.appendChild(row);
            });
        } else {
            usersTableBody.innerHTML = `<tr><td colspan="6" class="text-center">Error loading users: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching users:', error);
        usersTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Failed to load users. Check console.</td></tr>';
    }
}

// --- Event Listeners ---
if (reviewFilterSelect) {
    reviewFilterSelect.addEventListener('change', (e) => {
        loadReviewsData(e.target.value);
    });
}

if (userSearchInput) {
    userSearchInput.addEventListener('input', (e) => {
        loadUsersData(e.target.value);
    });
}


// --- Navigation Functions ---
function showSection(sectionId) {
    contentSections.forEach(section => {
        section.classList.remove('active');
    });
    const targetSection = document.getElementById(`${sectionId}-section`);
    if (targetSection) {
        targetSection.classList.add('active');
        pageTitle.textContent = document.querySelector(`.sidebar-nav a[href="#${sectionId}"]`).textContent.trim();

        // Load data or initialize charts for the active section
        if (sectionId === 'dashboard') {
            updateUserGrowthChart(); // from charts.js
            updateGenreDistributionChart(); // from charts.js
            // You might want a function to update dashboard stats numbers too
            updateDashboardStatNumbers(); 
        } else if (sectionId === 'movies') {
            loadMoviesData();
        } else if (sectionId === 'reviews') {
            loadReviewsData();
        } else if (sectionId === 'users') {
            loadUsersData();
        }
    }
}

function initializeNavigation() {
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            sidebarLinks.forEach(item => item.parentElement.classList.remove('active'));
            this.parentElement.classList.add('active');
            showSection(targetId);
        });
    });

    if (logoutCancelBtn) {
        logoutCancelBtn.addEventListener('click', function() {
            showSection('dashboard'); // Go back to dashboard
            document.querySelector('.sidebar-nav li.active').classList.remove('active');
            document.querySelector('.sidebar-nav a[href="#dashboard"]').parentElement.classList.add('active');
        });
    }

    // Logout confirm button already has an href, so no JS needed for redirection.
}

// --- Dropdown Functionality ---
function initializeDropdown() {
    if (adminProfile && dropdownMenu) {
        adminProfile.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent click from bubbling to document
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!adminProfile.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }
}


// --- CRUD / Action Stubs (to be implemented fully) ---
function viewLocalMovieDetails(movieId) { alert(`Viewing local movie ID: ${movieId}. Modal to be implemented.`); }
function editLocalMovie(movieId) { alert(`Editing local movie ID: ${movieId}. Functionality to be implemented.`); }
function deleteLocalMovie(movieId) { 
    if(confirm(`Are you sure you want to delete local movie ID: ${movieId}?`)) {
        alert(`Deleting local movie ID: ${movieId}. Functionality to be implemented.`);
        // Add AJAX call to a PHP script to delete from database
    }
}

function updateReviewStatus(reviewId, status) { alert(`Updating review ID: ${reviewId} to status: ${status}. Functionality to be implemented.`); }
function deleteDatabaseReview(reviewId) { 
     if(confirm(`Are you sure you want to delete review ID: ${reviewId}?`)) {
        alert(`Deleting review ID: ${reviewId}. Functionality to be implemented.`);
    }
}

async function deleteDatabaseUser(userId) {
    if (confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.')) {
        try {
            const formData = new FormData();
            formData.append('user_id', userId);

            const response = await fetch('delete_user.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                alert('Pengguna berhasil dihapus');
                loadUsersData(); // Reload the users table
            } else {
                alert('Gagal menghapus pengguna: ' + result.message);
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            alert('Terjadi kesalahan saat menghapus pengguna. Silakan coba lagi.');
        }
    }
}

// --- Initial Load ---
document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeDropdown();
    
    // Show dashboard by default
    const defaultSection = 'dashboard';
    const defaultLink = document.querySelector(`.sidebar-nav a[href="#${defaultSection}"]`);
    if (defaultLink) {
        defaultLink.parentElement.classList.add('active');
    }
    showSection(defaultSection); 
});

// Function to update dashboard stat numbers (Total Pengguna, Total Film, etc.)
async function updateDashboardStatNumbers() {
    try {
        const usersResponse = await fetch('get_users.php');
        const usersResult = await usersResponse.json();
        if (usersResult.status === 'success') {
            const totalUsersElement = document.querySelector('.stats-section .stat-card:nth-child(1) .stat-number');
            const memberUsersElement = document.querySelector('.stats-section .stat-card:nth-child(2) .stat-number');
            if(totalUsersElement) totalUsersElement.textContent = usersResult.total;
            // Assuming role_text 'Admin' and 'Pengguna' for members
            if(memberUsersElement) memberUsersElement.textContent = usersResult.data.filter(u => u.role_text === 'Pengguna' || u.role_text === 'Admin').length;
        }

        const moviesResponse = await fetch('get_movies.php'); // for local movies
        const moviesResult = await moviesResponse.json();
        if (moviesResult.status === 'success') {
            const totalMoviesElement = document.querySelector('.stats-section .stat-card:nth-child(3) .stat-number');
            if(totalMoviesElement) totalMoviesElement.textContent = moviesResult.total;
        }

        const reviewsResponse = await fetch('get_reviews.php');
        const reviewsResult = await reviewsResponse.json();
        if (reviewsResult.status === 'success') {
            const totalReviewsElement = document.querySelector('.stats-section .stat-card:nth-child(4) .stat-number');
            if(totalReviewsElement) totalReviewsElement.textContent = reviewsResult.total;
        }

    } catch (error) {
        console.error('Error updating dashboard stat numbers:', error);
    }
}

// Movie Management specific search and filter
const movieSearchInput = document.querySelector('#movieSearch');
const movieTypeFilterSelect = document.querySelector('#movieTypeFilter');

if (movieSearchInput) {
    movieSearchInput.addEventListener('input', () => searchLocalMovies(movieSearchInput.value, movieTypeFilterSelect.value));
}
if (movieTypeFilterSelect) {
    movieTypeFilterSelect.addEventListener('change', () => searchLocalMovies(movieSearchInput.value, movieTypeFilterSelect.value));
}

let localMoviesCache = []; // Cache for local movies to filter/search client-side after initial load

async function loadAndCacheLocalMovies() {
    // This function could be called when the 'movies' section is first activated
    // or data could be passed from loadMoviesData if it already fetches all.
    // For now, assuming loadMoviesData loads all into a global/accessible variable.
    // Let's modify loadMoviesData to store in localMoviesCache
    if (!moviesTableBody) return;
    moviesTableBody.innerHTML = '<tr><td colspan="8" class="text-center">Loading movies...</td></tr>';
    try {
        const response = await fetch('get_movies.php');
        const result = await response.json();
        if (result.status === 'success') {
            localMoviesCache = result.data; // Cache the data
            renderLocalMovies(localMoviesCache); // Render all initially
        } else {
            moviesTableBody.innerHTML = `<tr><td colspan="8" class="text-center">Error loading movies: ${result.message}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching local movies:', error);
        moviesTableBody.innerHTML = '<tr><td colspan="8" class="text-center">Failed to load movies.</td></tr>';
    }
}


function renderLocalMovies(moviesToRender) {
     if (!moviesTableBody) return;
    moviesTableBody.innerHTML = '';
    if (moviesToRender.length === 0) {
        moviesTableBody.innerHTML = '<tr><td colspan="8" class="text-center">No movies match your criteria.</td></tr>';
        return;
    }
    moviesToRender.forEach(movie => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><img src="${movie.poster_path || '../assets/images/default-poster.png'}" alt="${movie.title}" style="width: 50px; height: auto; border-radius: 4px;"></td>
            <td>${movie.title}</td>
            <td>${movie.genre || 'N/A'}</td>
            <td><span class="rating-badge">${movie.rating}</span></td>
            <td>${movie.release_date ? movie.release_date.substring(0,4) : 'N/A'}</td>
            <td>${movie.runtime || 'N/A'} min</td>
            <td><span class="status-badge ${movie.status ? movie.status.toLowerCase().replace(' ', '-') : ''}">${movie.status || 'N/A'}</span></td>
            <td>
                <button class="action-btn view" title="View Details" onclick="viewLocalMovieDetails(${movie.id})"><i class="fas fa-eye"></i></button>
                <button class="action-btn edit" title="Edit Movie" onclick="editLocalMovie(${movie.id})"><i class="fas fa-edit"></i></button>
                <button class="action-btn delete" title="Delete Movie" onclick="deleteLocalMovie(${movie.id})"><i class="fas fa-trash"></i></button>
            </td>
        `;
        moviesTableBody.appendChild(row);
    });
}

function searchLocalMovies(searchTerm, typeFilter) {
    const term = searchTerm.toLowerCase();
    const filtered = localMoviesCache.filter(movie => {
        const titleMatch = movie.title.toLowerCase().includes(term);
        const typeMatch = typeFilter === 'all' || (movie.type && movie.type.toLowerCase() === typeFilter); // Assuming movie object has a 'type'
        
        // If your local movies don't have a 'type' like popular/top_rated,
        // this part of filter needs adjustment or `movie.type` needs to be added in `get_movies.php`
        // For local films, 'type' might always be 'local' or based on some other criteria.
        // For now, let's assume `typeFilter` is more for TMDB results if you were to mix them.
        // If only local, typeFilter might be less relevant unless you categorize local films.
        // Let's simplify: if typeFilter is not 'all', and movie.type isn't set, it won't match.
        // This means for purely local films, typeFilter might not be useful unless get_movies.php adds a type.
        // Given get_movies.php now returns type: 'local', this logic is okay.

        return titleMatch && typeMatch;
    });
    renderLocalMovies(filtered);
}

// Modify showSection for movies
function showSection(sectionId) {
    contentSections.forEach(section => {
        section.classList.remove('active');
    });
    const targetSection = document.getElementById(`${sectionId}-section`);
    if (targetSection) {
        targetSection.classList.add('active');
        pageTitle.textContent = document.querySelector(`.sidebar-nav a[href="#${sectionId}"]`).textContent.trim();

        if (sectionId === 'dashboard') {
            updateUserGrowthChart();
            updateGenreDistributionChart();
            updateDashboardStatNumbers(); 
        } else if (sectionId === 'movies') {
            loadAndCacheLocalMovies(); // Use the caching function
        } else if (sectionId === 'reviews') {
            loadReviewsData();
        } else if (sectionId === 'users') {
            loadUsersData();
        }
    }
}