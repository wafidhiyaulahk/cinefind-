<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

// Get user data for navbar
$user_id = $_SESSION['user_id'];
$user_query = "SELECT r.id_role, r.username, r.email, r.role, p.foto_profil 
               FROM role r 
               LEFT JOIN pengguna p ON r.id_role = p.role_id 
               WHERE r.id_role = ?";

$stmt = $conn->prepare($user_query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

if (!$user_data) {
    die("No user data found for ID: " . $user_id);
}

// Set default avatar
$default_avatar = 'cinefind.png';
$user_avatar = (!empty($user_data['foto_profil'])) ? '../' . htmlspecialchars($user_data['foto_profil']) : $default_avatar;

// Get user's watchlist
$watchlist_query = "SELECT w.id, w.movie_id, w.added_at, 
                   f.id_film, f.judul, f.poster_url, 
                   f.tahun_rilis, f.deskripsi,
                   f.rating_avg, f.rating_count,
                   GROUP_CONCAT(DISTINCT g.genre) as genres
                   FROM watchlist w 
                   INNER JOIN film f ON w.movie_id = f.id_film 
                   LEFT JOIN film_genre g ON f.id_film = g.id_film
                   WHERE w.user_id = ? 
                   GROUP BY w.id, f.id_film
                   ORDER BY w.added_at DESC";
$stmt_watchlist = $conn->prepare($watchlist_query);
$stmt_watchlist->bind_param("i", $user_id);
$stmt_watchlist->execute(); 
$watchlist_result = $stmt_watchlist->get_result();
?>

<nav class="navbar">
    <div class="navbar-brand">
        <a href="indexpengguna.php">
            <img src="cinefind.png" alt="CineFind Logo" class="logo" style="width: 100px; height: 100px;">
        </a>
    </div>
    <div class="navbar-nav" style="margin: auto;">
        <a href="indexpengguna.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
        <a href="my-watchlist.php" class="nav-link"><i class="fas fa-list"></i> My Watchlist</a>
        <a href="#" class="nav-link" onclick="openAboutModal()"><i class="fas fa-info-circle"></i> About</a>
    </div>
    
    <div class="navbar-profile">
        <div class="profile-dropdown">
            <button class="profile-button" onclick="toggleProfileDropdown()">
                <div class="profile-avatar">
                    <?php if (!empty($user_data['foto_profil'])): ?>
                        <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Profile Photo">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <span id="profileUsername"><?php echo htmlspecialchars($user_data['username']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </button>
            <div class="dropdown-menu" id="profileDropdown">
                <div class="profile-preview">
                    <div class="profile-preview-avatar">
                        <?php if (!empty($user_data['foto_profil'])): ?>
                            <img src="<?php echo htmlspecialchars($user_avatar); ?>" alt="Profile Photo">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="profile-preview-info">
                        <h4 id="previewName"><?php echo htmlspecialchars($user_data['username']); ?></h4>
                        <p id="previewEmail"><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                </div>
                <div class="dropdown-menu-items">
                    <a href="edit-profile.php" class="dropdown-item">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                    <a href="my-watchlist.php" class="dropdown-item">
                        <i class="fas fa-list"></i> My Watchlist
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="../logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    background: rgba(0, 0, 0, 0.9);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-brand .logo {
    color: #e50914;
    font-size: 1.5rem;
    font-weight: bold;
    text-decoration: none;
}

.navbar-menu {
    display: flex;
    gap: 2rem;
}

.nav-link {
    color: #fff;
    text-decoration: none;
    font-size: 1rem;
    transition: color 0.3s;
}

.nav-link:hover {
    color: #e50914;
}

.navbar-profile {
    position: relative;
}

.profile-dropdown {
    position: relative;
}

.profile-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.profile-button:hover {
    background: rgba(255, 255, 255, 0.1);
}

.profile-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.profile-username {
    font-size: 0.9rem;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: rgba(0, 0, 0, 0.95);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    min-width: 240px;
    display: none;
    overflow: hidden;
    z-index: 1000;
}

.dropdown-menu.show {
    display: block;
    animation: slideDown 0.3s ease-out;
}

.profile-preview {
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.05);
}

.preview-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.preview-info h4 {
    margin: 0;
    color: #fff;
    font-size: 1rem;
}

.preview-info p {
    margin: 0.25rem 0 0 0;
    color: #aaa;
    font-size: 0.9rem;
}

.dropdown-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 0.5rem 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.3s;
}

.dropdown-item:hover {
    background: rgba(255, 255, 255, 0.1);
}

.dropdown-item i {
    width: 16px;
    text-align: center;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .navbar {
        padding: 1rem;
    }
    
    .navbar-menu {
        display: none;
    }
    
    .profile-username {
        display: none;
    }
}
</style>

<script>
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('profileDropdown');
    const profileButton = document.querySelector('.profile-button');
    
    if (!profileButton.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});
</script> 