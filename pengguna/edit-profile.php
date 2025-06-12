<?php
session_start();
require_once '../service/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT p.*, r.username, r.email as role_email, r.role 
               FROM pengguna p 
               JOIN role r ON p.role_id = r.id_role 
               WHERE r.id_role = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

if (!$user_data) {
    // If no data in pengguna table, get at least the role data
    $role_query = "SELECT * FROM role WHERE id_role = ?";
    $stmt_role = $conn->prepare($role_query);
    $stmt_role->bind_param("i", $user_id);
    $stmt_role->execute();
    $user_data = $stmt_role->get_result()->fetch_assoc();
    
    if (!$user_data) {
        die("User not found. Please make sure you are logged in correctly.");
    }
}

// Get user's watchlist
$watchlist_query = "SELECT w.movie_id, f.judul, f.poster_url, 
                   f.rating_avg, f.rating_count
                   FROM watchlist w 
                   INNER JOIN film f ON w.movie_id = f.id_film 
                   WHERE w.user_id = ?";
$stmt_watchlist = $conn->prepare($watchlist_query);
$stmt_watchlist->bind_param("i", $user_id);
$stmt_watchlist->execute();
$watchlist_result = $stmt_watchlist->get_result();

// Store watchlist in array for easy lookup
$user_watchlist = array();
while ($row = $watchlist_result->fetch_assoc()) {
    $user_watchlist[$row['movie_id']] = array(
        'judul' => $row['judul'],
        'poster_url' => $row['poster_url'],
        'rating' => $row['rating_avg'],
        'rating_count' => $row['rating_count']
    );
}


// Function to display movie cards
function displayMovieCards($movies) {
    global $user_watchlist;
    while ($movie = $movies->fetch_assoc()) {
        $isInWatchlist = isset($user_watchlist[$movie['id_film']]);
        $watchlistClass = $isInWatchlist ? 'in-list' : '';
        $watchlistIcon = $isInWatchlist ? 'fa-check' : 'fa-plus';
        $watchlistText = $isInWatchlist ? 'In My List' : 'Add to List';
        
        // Format rating display
        $ratingDisplay = number_format($movie['rating'], 1);
        $ratingCount = $movie['rating_count'];
        
        echo '<div class="movie-card" 
            data-movie-id="' . htmlspecialchars($movie['id_film']) . '"
            data-title="' . htmlspecialchars($movie['judul']) . '" 
            data-year="' . htmlspecialchars($movie['tahun_rilis']) . '" 
            data-rating="' . htmlspecialchars($ratingDisplay) . '"
            data-description="' . htmlspecialchars($movie['deskripsi']) . '"
            data-poster-path="' . htmlspecialchars($movie['poster_url']) . '"
            onclick="showMovieDetails({
                title: \'' . htmlspecialchars($movie['judul']) . '\',
                year: \'' . htmlspecialchars($movie['tahun_rilis']) . '\',
                rating: \'' . htmlspecialchars($ratingDisplay) . '\',
                ratingCount: \'' . htmlspecialchars($ratingCount) . '\',
                description: \'' . htmlspecialchars($movie['deskripsi']) . '\',
                img_src: \'' . htmlspecialchars($movie['poster_url']) . '\'
            })">';
        echo '<img src="' . htmlspecialchars($movie['poster_url']) . '" 
              alt="' . htmlspecialchars($movie['judul']) . '" 
              class="movie-poster">';
        echo '<div class="movie-info">';
        echo '<h3 class="movie-title">' . htmlspecialchars($movie['judul']) . '</h3>';
        echo '<div class="movie-year">' . htmlspecialchars($movie['tahun_rilis']) . '</div>';
        if ($movie['rating'] > 0) {
            echo '<div class="movie-rating">
                    <i class="fas fa-star"></i>' . htmlspecialchars($ratingDisplay) . 
                    '<span class="rating-count">(' . htmlspecialchars($ratingCount) . ')</span>
                  </div>';
        }
        if (!empty($movie['deskripsi'])) {
            echo '<div class="movie-description">' . htmlspecialchars(substr($movie['deskripsi'], 0, 100)) . '...</div>';
        }
        echo '<button class="trailer-btn" onclick="event.stopPropagation(); searchTrailer(\'' . htmlspecialchars($movie['judul']) . '\')">
                    <i class="fas fa-play"></i> Watch Trailer
                  </button>';
        echo '<button class="watchlist-btn ' . $watchlistClass . '" 
                onclick="event.stopPropagation(); toggleWatchlist(' . $movie['id_film'] . ', this)"
                data-movie-id="' . htmlspecialchars($movie['id_film']) . '"
                data-movie-title="' . htmlspecialchars($movie['judul']) . '"
                data-poster-path="' . htmlspecialchars($movie['poster_url']) . '">
                <i class="fas ' . $watchlistIcon . '"></i> ' . $watchlistText . '
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
    <title>Edit Profile - CineFind</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0c0c0c 0%, #1a1a1a 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-header {
            background: linear-gradient(135deg, rgba(229, 9, 20, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .profile-avatar {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            background: linear-gradient(135deg, #e50914, #f40612);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(229, 9, 20, 0.3);
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 30px rgba(229, 9, 20, 0.5);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .profile-avatar .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            color: #fff;
            font-size: 1.5rem;
        }

        .profile-avatar:hover .avatar-overlay {
            opacity: 1;
        }

        .profile-avatar input[type="file"] {
            display: none;
        }

        .profile-info {
            flex: 1;
            min-width: 250px;
        }

        .profile-info h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.2rem;
            color: #fff;
            font-weight: 600;
        }

        .profile-info p {
            margin: 0 0 1rem 0;
            color: #aaa;
            font-size: 1.1rem;
        }

        .profile-badge {
            display: inline-block;
            background: linear-gradient(135deg, #e50914, #f40612);
            color: #fff;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            color: #e50914;
            margin-bottom: 0.5rem;
        }

        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            color: #fff;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .stat-card p {
            margin: 0;
            color: #aaa;
            font-size: 0.9rem;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
            color: #fff;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .section-title i {
            color: #e50914;
            font-size: 1.2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #fff;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-group .required {
            color: #e50914;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #e50914;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.1);
        }

        .form-group input[readonly] {
            background: rgba(255, 255, 255, 0.03);
            cursor: not-allowed;
            border-color: rgba(255, 255, 255, 0.08);
        }

        .form-group .input-icon {
            position: absolute;
            right: 1rem;
            top: 2.2rem;
            color: #aaa;
            pointer-events: none;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #dc3545; }
        .strength-fair { background: #ffc107; }
        .strength-good { background: #17a2b8; }
        .strength-strong { background: #28a745; }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .requirement {
            color: #aaa;
            margin: 0.2rem 0;
            transition: color 0.3s ease;
        }

        .requirement.met {
            color: #28a745;
        }

        .requirement i {
            width: 16px;
            margin-right: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #e50914, #f40612);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.15));
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.2));
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.1);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: #fff;
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            max-width: 350px;
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .alert-error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
        }

        .alert-info {
            background: linear-gradient(135deg, #17a2b8, #20c997);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .tooltip {
            position: relative;
            cursor: help;
        }

        .tooltip::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: #fff;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .tooltip:hover::after {
            opacity: 1;
            visibility: visible;
        }

        .avatar-upload-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .upload-info {
            color: #aaa;
            font-size: 0.9rem;
        }

        .upload-info strong {
            color: #fff;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .profile-container {
                margin: 1rem auto;
                padding: 0 0.5rem;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
            }

            .profile-info h1 {
                font-size: 1.8rem;
            }

            .form-section {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .profile-stats {
                grid-template-columns: 1fr;
            }
            
            .alert {
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }

        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, rgba(255,255,255,0.1) 25%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0.1) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .form-help {
            font-size: 0.85rem;
            color: #aaa;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
 

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="header-content">
                <div class="back-button-container" style="width: 100%; margin-bottom: 1rem;">
                    <a href="indexpengguna.php" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <div class="avatar-upload-area">
                    <div class="profile-avatar" id="profileAvatar">
                        <img src="../assets/images/default-avatar.png" alt="Profile Avatar" id="avatarImage">
                        <div class="avatar-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                        <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/gif" max-size="5242880">
                    </div>
                    <div class="upload-info">
                        <div><strong>Click to change photo</strong></div>
                        <div>JPG, PNG or GIF (max 5MB)</div>
                    </div>
                </div>
                
                <div class="profile-info">
                    <h1 id="profileName">
                        <span class="skeleton" style="display:inline-block;width:200px;height:2rem;border-radius:4px;"></span>
                    </h1>
                    <p id="profileEmail">
                        <span class="skeleton" style="display:inline-block;width:250px;height:1.2rem;border-radius:4px;"></span>
                    </p>
                    <span class="profile-badge">CineFind Member</span>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-bookmark"></i></div>
                <h3 id="watchlistCount">0</h3>
                <p>Movies in Watchlist</p>
            </div>
            
        </div>

        <!-- Profile Information Form -->
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-user-edit"></i>
                Profile Information
            </div>
            
            <form id="profileForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['nama_lengkap'] ?? ''); ?>" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email'] ?? $user_data['role_email'] ?? ''); ?>" required>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Change Form -->
        <div class="form-section">
            <div class="section-title">
                <i class="fas fa-lock"></i>
                Change Password
            </div>
            
            <form id="passwordForm">
                <div class="form-group">
                    <label for="currentPassword">Current Password <span class="required">*</span></label>
                    <input type="password" id="currentPassword" name="current_password" required>
                    <i class="fas fa-key input-icon"></i>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="newPassword">New Password <span class="required">*</span></label>
                        <input type="password" id="newPassword" name="new_password" required>
                        <i class="fas fa-lock input-icon"></i>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="password-requirements" id="passwordRequirements">
                            <div class="requirement" id="req-length">
                                <i class="fas fa-times"></i> At least 8 characters
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <i class="fas fa-times"></i> One uppercase letter
                            </div>
                            <div class="requirement" id="req-lowercase">
                                <i class="fas fa-times"></i> One lowercase letter
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="fas fa-times"></i> One number
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password <span class="required">*</span></label>
                        <input type="password" id="confirmPassword" name="confirm_password" required>
                        <i class="fas fa-check-circle input-icon" id="confirmIcon" style="display:none;color:#28a745;"></i>
                    </div>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-shield-alt"></i>
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let originalFormData = {};

        // Load profile data
        async function loadProfileData() {
            try {
                const response = await fetch('get_profile.php');
                const data = await response.json();
                
                if (data.error) {
                    showAlert(data.error, 'error');
                    return;
                }
                
                // Update profile information
                document.getElementById('profileName').textContent = data.name;
                document.getElementById('profileEmail').textContent = data.email;
                document.getElementById('name').value = data.name;
                document.getElementById('email').value = data.email;
                document.getElementById('username').value = data.username;
                
                // Store original data for reset functionality
                originalFormData = {
                    name: data.name,
                    username: data.username,
                    email: data.email
                };
                
                // Update avatar if exists
                if (data.avatar) {
                    document.getElementById('avatarImage').src = '../' + data.avatar;
                }
                
                // Load user stats
                loadUserStats();
                
            } catch (error) {
                showAlert('Failed to load profile data', 'error');
                console.error('Profile load error:', error);
            }
        }

        // Load user statistics
        async function loadUserStats() {
            try {
                const response = await fetch('get_user_stats.php');
                const data = await response.json();
                
                if (data.error) {
                    showAlert(data.error, 'error');
                    return;
                }
                
                // Animate counters
                animateCounter('watchlistCount', data.watchlist_count);
                animateCounter('reviewsCount', data.reviews_count);
                animateCounter('ratingsCount', data.ratings_count);
                
            } catch (error) {
                showAlert('Failed to load user statistics', 'error');
                console.error('Stats load error:', error);
            }
        }

        // Animate counter numbers
        function animateCounter(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const startValue = 0;
            const duration = 1000;
            const stepTime = 20;
            const steps = duration / stepTime;
            const increment = targetValue / steps;
            let currentValue = startValue;
            
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= targetValue) {
                    currentValue = targetValue;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(currentValue);
            }, stepTime);
        }

        // Handle profile form submission
        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Check if form data has changed
            const currentData = {
                name: form.name.value.trim(),
                username: form.username.value.trim(),
                email: form.email.value.trim()
            };
            
            if (currentData.name === originalFormData.name && 
                currentData.username === originalFormData.username &&
                currentData.email === originalFormData.email) {
                showAlert('No changes detected', 'info');
                return;
            }
            
            // Validate form
            if (!validateProfileForm(form)) {
                return;
            }
            
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('name', currentData.name);
                formData.append('username', currentData.username);
                formData.append('email', currentData.email);
                
                const response = await fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.error) {
                    showAlert(data.error, 'error');
                } else {
                    showAlert('Profile updated successfully!', 'success');
                    // Update displayed information
                    document.getElementById('profileName').textContent = data.data.name;
                    
                    // Update original data
                    originalFormData.name = data.data.name;
                    originalFormData.username = data.data.username;
                    originalFormData.email = data.data.email;
                }
                
            } catch (error) {
                showAlert('Failed to update profile. Please try again.', 'error');
                console.error('Profile update error:', error);
            } finally {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        });

        // Handle password form submission
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Validate passwords
            if (!validatePasswordForm(form)) {
                return;
            }
            
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('current_password', form.current_password.value);
                formData.append('new_password', form.new_password.value);
                
                const response = await fetch('update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.error) {
                    showAlert(data.error, 'error');
                } else {
                    showAlert('Password updated successfully!', 'success');
                    form.reset();
                    resetPasswordValidation();
                }
                
            } catch (error) {
                showAlert('Failed to update password. Please try again.', 'error');
                console.error('Password update error:', error);
            } finally {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        });

        // Form validation
        function validateProfileForm(form) {
            const name = form.name.value.trim();
            const username = form.username.value.trim();
            const email = form.email.value.trim();
            
            if (name.length < 2) {
                showAlert('Name must be at least 2 characters long', 'error');
                form.name.focus();
                return false;
            }
            
            if (username.length < 3) {
                showAlert('Username must be at least 3 characters long', 'error');
                form.username.focus();
                return false;
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                showAlert('Username can only contain letters, numbers, and underscores', 'error');
                form.username.focus();
                return false;
            }
            
            if (!email || !email.includes('@') || !email.includes('.')) {
                showAlert('Please enter a valid email address', 'error');
                form.email.focus();
                return false;
            }
            
            return true;
        }

        function validatePasswordForm(form) {
            const currentPassword = form.current_password.value;
            const newPassword = form.new_password.value;
            const confirmPassword = form.confirm_password.value;
            
            if (!currentPassword) {
                showAlert('Please enter your current password', 'error');
                form.current_password.focus();
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                showAlert('New passwords do not match', 'error');
                form.confirm_password.focus();
                return false;
            }
            
            if (!isPasswordStrong(newPassword)) {
                showAlert('Please choose a stronger password', 'error');
                form.new_password.focus();
                return false;
            }
            
            return true;
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password)
            };
            
            let strength = 0;
            Object.values(requirements).forEach(met => {
                if (met) strength++;
            });
            
            return { requirements, strength };
        }

        function isPasswordStrong(password) {
            const { strength } = checkPasswordStrength(password);
            return strength >= 4;
        }

        function updatePasswordStrength(password) {
            const { requirements, strength } = checkPasswordStrength(password);
            const strengthBar = document.getElementById('strengthBar');
            
            // Update strength bar
            const percentage = (strength / 4) * 100;
            strengthBar.style.width = percentage + '%';
            
            // Update bar color
            strengthBar.className = 'password-strength-bar';
            if (strength === 1) strengthBar.classList.add('strength-weak');
            else if (strength === 2) strengthBar.classList.add('strength-fair');
            else if (strength === 3) strengthBar.classList.add('strength-good');
            else if (strength === 4) strengthBar.classList.add('strength-strong');
            
            // Update requirements checklist
            updateRequirement('req-length', requirements.length);
            updateRequirement('req-uppercase', requirements.uppercase);
            updateRequirement('req-lowercase', requirements.lowercase);
            updateRequirement('req-number', requirements.number);
        }

        function updateRequirement(id, met) {
            const element = document.getElementById(id);
            const icon = element.querySelector('i');
            
            if (met) {
                element.classList.add('met');
                icon.className = 'fas fa-check';
            } else {
                element.classList.remove('met');
                icon.className = 'fas fa-times';
            }
        }

        function resetPasswordValidation() {
            const strengthBar = document.getElementById('strengthBar');
            strengthBar.style.width = '0%';
            strengthBar.className = 'password-strength-bar';
            
            // Reset all requirements
            ['req-length', 'req-uppercase', 'req-lowercase', 'req-number'].forEach(id => {
                updateRequirement(id, false);
            });
            
            document.getElementById('confirmIcon').style.display = 'none';
        }

        // Handle avatar upload
        document.getElementById('profileAvatar').addEventListener('click', () => {
            document.getElementById('avatarInput').click();
        });

        document.getElementById('avatarInput').addEventListener('change', async (e) => {
            if (e.target.files.length > 0) {
                const file = e.target.files[0];
                
                // Validate file
                if (!validateImageFile(file)) {
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('avatarImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // Upload file
                const formData = new FormData();
                formData.append('avatar', file);
                
                try {
                    showAlert('Uploading profile photo...', 'info');
                    
                    const response = await fetch('update_profile.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.error) {
                        showAlert(data.error, 'error');
                        // Revert to original image
                        loadProfileData();
                    } else {
                        showAlert('Profile photo updated successfully!', 'success');
                    }
                    
                } catch (error) {
                    showAlert('Failed to update profile photo', 'error');
                    console.error('Avatar upload error:', error);
                    // Revert to original image
                    loadProfileData();
                }
            }
        });

        // Validate image file
        function validateImageFile(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!allowedTypes.includes(file.type)) {
                showAlert('Please select a valid image file (JPG, PNG, or GIF)', 'error');
                return false;
            }
            
            if (file.size > maxSize) {
                showAlert('Image file is too large. Please select a file smaller than 5MB', 'error');
                return false;
            }
            
            return true;
        }

        // Reset form to original values
        function resetForm() {
            if (confirm('Are you sure you want to reset all changes?')) {
                document.getElementById('name').value = originalFormData.name;
                document.getElementById('username').value = originalFormData.username;
                document.getElementById('email').value = originalFormData.email;
                showAlert('Form reset to original values', 'info');
            }
        }

        // Password field event listeners
        document.getElementById('newPassword').addEventListener('input', (e) => {
            updatePasswordStrength(e.target.value);
            checkPasswordMatch();
        });

        document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const icon = document.getElementById('confirmIcon');
            
            if (confirmPassword && newPassword === confirmPassword) {
                icon.style.display = 'block';
                icon.style.color = '#28a745';
                icon.className = 'fas fa-check-circle input-icon';
            } else if (confirmPassword) {
                icon.style.display = 'block';
                icon.style.color = '#dc3545';
                icon.className = 'fas fa-times-circle input-icon';
            } else {
                icon.style.display = 'none';
            }
        }

        // Show alert message with auto-dismiss
        function showAlert(message, type = 'success') {
            // Remove existing alerts
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            
            // Add icon based on type
            let icon = '';
            switch(type) {
                case 'success': icon = 'fas fa-check-circle'; break;
                case 'error': icon = 'fas fa-exclamation-circle'; break;
                case 'info': icon = 'fas fa-info-circle'; break;
                default: icon = 'fas fa-bell';
            }
            
            alert.innerHTML = `<i class="${icon}"></i> ${message}`;
            document.body.appendChild(alert);
            
            // Auto-dismiss after 4 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.style.animation = 'slideOut 0.3s ease-out forwards';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 4000);
        }

        // Add slideOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Auto-save draft functionality
        let autoSaveTimer;
        function autoSaveDraft() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                const formData = {
                    name: document.getElementById('name').value.trim(),
                    username: document.getElementById('username').value.trim(),
                    email: document.getElementById('email').value.trim()
                };
                
                // Only save if data has changed
                if (formData.name !== originalFormData.name || 
                    formData.username !== originalFormData.username ||
                    formData.email !== originalFormData.email) {
                    localStorage.setItem('profileDraft', JSON.stringify(formData));
                }
            }, 2000);
        }

        // Load draft on page load
        function loadDraft() {
            const draft = localStorage.getItem('profileDraft');
            if (draft) {
                try {
                    const data = JSON.parse(draft);
                    if (confirm('You have unsaved changes. Would you like to restore them?')) {
                        document.getElementById('name').value = data.name || '';
                        document.getElementById('username').value = data.username || '';
                        document.getElementById('email').value = data.email || '';
                    } else {
                        localStorage.removeItem('profileDraft');
                    }
                } catch (error) {
                    console.error('Error loading draft:', error);
                    localStorage.removeItem('profileDraft');
                }
            }
        }

        // Clear draft when form is successfully submitted
        function clearDraft() {
            localStorage.removeItem('profileDraft');
        }

        // Add event listeners for auto-save
        document.getElementById('name').addEventListener('input', autoSaveDraft);
        document.getElementById('username').addEventListener('input', autoSaveDraft);
        document.getElementById('email').addEventListener('input', autoSaveDraft);

        // Handle form submission success
        const originalProfileSubmit = document.getElementById('profileForm').addEventListener;
        document.getElementById('profileForm').addEventListener('submit', async (e) => {
            // ... existing submit handler code ...
            // Add clearDraft() call on successful submission
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+S or Cmd+S to save profile
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('profileForm').dispatchEvent(new Event('submit'));
            }
            
            // Escape to reset form
            if (e.key === 'Escape') {
                resetForm();
            }
        });

        // Initialize tooltips
        function initTooltips() {
            const tooltips = document.querySelectorAll('.tooltip');
            tooltips.forEach(tooltip => {
                tooltip.addEventListener('mouseenter', () => {
                    // Custom tooltip logic if needed
                });
            });
        }

        // Form validation feedback
        function addValidationFeedback() {
            const inputs = document.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', validateInput);
                input.addEventListener('input', clearValidationError);
            });
        }

        function validateInput(e) {
            const input = e.target;
            const value = input.value.trim();
            
            // Remove existing validation styling
            input.classList.remove('invalid', 'valid');
            
            if (input.hasAttribute('required') && !value) {
                input.classList.add('invalid');
                return false;
            }
            
            // Specific validations
            if (input.name === 'username' && value.length < 3) {
                input.classList.add('invalid');
                return false;
            }
            
            if (input.name === 'name' && value.length < 2) {
                input.classList.add('invalid');
                return false;
            }
            
            input.classList.add('valid');
            return true;
        }

        function clearValidationError(e) {
            e.target.classList.remove('invalid');
        }

        // Add validation styles
        const validationStyles = document.createElement('style');
        validationStyles.textContent = `
            .form-group input.invalid {
                border-color: #dc3545;
                box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
            }
            
            .form-group input.valid {
                border-color: #28a745;
            }
            
            .form-group input.invalid + .input-icon {
                color: #dc3545;
            }
            
            .form-group input.valid + .input-icon {
                color: #28a745;
            }
        `;
        document.head.appendChild(validationStyles);

        // Load everything when page loads
        document.addEventListener('DOMContentLoaded', () => {
            loadProfileData();
            loadDraft();
            initTooltips();
            addValidationFeedback();
            
            // Add loading states to initial elements
            setTimeout(() => {
                document.querySelectorAll('.skeleton').forEach(skeleton => {
                    skeleton.style.display = 'none';
                });
            }, 1000);
        });

        // Handle page visibility change (auto-save when leaving page)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                autoSaveDraft();
            }
        });

        // Warn user about unsaved changes
        window.addEventListener('beforeunload', (e) => {
            const currentData = {   
                name: document.getElementById('name').value.trim(),
                username: document.getElementById('username').value.trim(),
                email: document.getElementById('email').value.trim()
            };
            
            if (currentData.name !== originalFormData.name || 
                currentData.username !== originalFormData.username ||
                currentData.email !== originalFormData.email) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                return e.returnValue;
            }
        });
    </script>
</body>
</html>