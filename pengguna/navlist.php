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
    margin-left: auto;
}

.profile-dropdown {
    position: relative;
}

.profile-button {
    display: flex;
    align-items: center;
    gap: 10px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.profile-button:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.profile-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar i {
    color: #fff;
    font-size: 16px;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
}

.profile-info span {
    font-size: 14px;
    font-weight: 500;
}

.profile-info i {
    font-size: 12px;
    transition: transform 0.2s ease;
}

.profile-dropdown.active .profile-info i {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 280px;
    background: #181818;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.profile-dropdown.active .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.profile-preview {
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.profile-preview-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-preview-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-preview-avatar i {
    color: #fff;
    font-size: 24px;
}

.profile-preview-info {
    flex: 1;
}

.profile-preview-info h4 {
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.profile-preview-info p {
    color: #b3b3b3;
    font-size: 14px;
    margin: 0;
}

.dropdown-menu-items {
    padding: 8px 0;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #fff;
    text-decoration: none;
    transition: background-color 0.2s ease;
}

.dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.dropdown-item i {
    width: 20px;
    color: #b3b3b3;
    font-size: 16px;
}

.dropdown-divider {
    height: 1px;
    background-color: rgba(255, 255, 255, 0.1);
    margin: 8px 0;
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
    const dropdown = document.querySelector('.profile-dropdown');
    dropdown.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const dropdown = document.querySelector('.profile-dropdown');
    const profileButton = document.querySelector('.profile-button');
    
    if (!profileButton.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
    }
});
</script> 