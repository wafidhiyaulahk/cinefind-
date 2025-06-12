<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

// Log database connection status
error_log("Database connection status: " . ($conn->connect_error ? "Failed" : "Success"));

// Perbaikan: Cek menggunakan $_SESSION['user_id']
if (isset($_SESSION['user_id'])) {
    // Pastikan $_SESSION['role'] juga sudah di-set jika ingin digunakan di sini
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: admin/admin.php");
                break;
            case 'pengguna':
                header("Location: pengguna/indexpengguna.php");
                break;
            default:
                header("Location: index.php");
        }
        exit();
    } else {
        // Jika role tidak ada di session, mungkin perlu logout atau handle error
        // Untuk sekarang, redirect ke index.php
        header("Location: index.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    error_log("Login attempt for username: " . $username);

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $stmt = $conn->prepare("SELECT r.*, p.id_pengguna, p.nama_lengkap, p.email AS pengguna_email 
                               FROM role r 
                               LEFT JOIN pengguna p ON r.id_role = p.role_id 
                               WHERE r.username = ?");
        if (!$stmt) { // Tambahkan pengecekan prepare statement
            error_log("Prepare statement failed: " . $conn->error);
            $error = "Terjadi kesalahan pada server.";
        } else {
            $stmt->bind_param("s", $username);
            error_log("Attempting login with username: " . $username);
            $stmt->execute();
            $result = $stmt->get_result();
            error_log("Database query for username '" . $username . "' returned " . $result->num_rows . " rows.");

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                error_log("User found. Role: " . $user['role']);
                
                if (password_verify($password, $user['password'])) {
                    error_log("Password verified successfully");
                    $_SESSION['user_id'] = $user['id_role'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['pengguna_id'] = $user['id_pengguna'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? $user['username'];
                    $_SESSION['email'] = $user['pengguna_email'] ?? $user['email'];
                    $_SESSION['foto_profil'] = $user['foto_profil'] ?? null;

                    error_log("Session data set: " . print_r($_SESSION, true));
                    $success = "Login berhasil! Mengalihkan...";
                    
                    switch ($user['role']) {
                        case 'admin':
                            header("refresh:1;url=admin/admin.php");
                            break;
                        case 'pengguna':
                            header("refresh:1;url=pengguna/indexpengguna.php");
                            break;
                        default:
                            $error = "Role pengguna tidak valid!";
                            error_log("Invalid role: " . $user['role']);
                    }
                    exit();
                } else {
                    $error = "Password salah!";
                    error_log("Password verification failed for user: " . $username);
                }
            } else {
                $error = "Username tidak ditemukan!";
                error_log("No user found with username: " . $username);
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CineFind</title>
    <link rel="icon" type="image/png" href="gmbr/cinefind.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="style/login.css" rel="stylesheet">
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(229, 9, 20, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(229, 9, 20, 0.1) 0%, transparent 50%);
            animation: pulse 4s ease-in-out infinite alternate;
        }

        @keyframes pulse {
            0% { opacity: 0.3; }
            100% { opacity: 0.6; }
        }

        .back-to-home {
            position: fixed;
            top: 24px;
            left: 24px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 100;
            padding: 8px 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-to-home:hover {
            color: #e50914;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .login-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 48px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3),
                        0 0 0 1px rgba(255, 255, 255, 0.05);
            width: 100%;
            max-width: 420px;
            color: white;
            position: relative;
            z-index: 10;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 24px;
        }

        .login-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(229, 9, 20, 0.3);
            box-shadow: 0 8px 24px rgba(229, 9, 20, 0.2);
            transition: all 0.3s ease;
        }

        .login-header img:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 32px rgba(229, 9, 20, 0.3);
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff 0%, #e50914 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            font-weight: 400;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 400;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #e50914;
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.1);
            transform: translateY(-1px);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-group input:focus + .input-icon {
            color: #e50914;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #e50914 0%, #f40612 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: none;
            letter-spacing: 0;
            position: relative;
            overflow: hidden;
            margin-bottom: 32px;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(229, 9, 20, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            font-weight: 400;
        }

        .register-link a {
            color: #e50914;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .register-link a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 50%;
            background: #e50914;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .register-link a:hover::after {
            width: 100%;
        }

        .register-link a:hover {
            color: #f40612;
        }

        /* Loading animation */
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
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                padding: 32px 24px;
                margin: 16px;
                border-radius: 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .back-to-home {
                top: 16px;
                left: 16px;
                padding: 6px 12px;
                font-size: 13px;
            }
        }

        @media (max-width: 360px) {
            .login-container {
                padding: 24px 20px;
            }
        }

        /* Dark mode enhancements */
        @media (prefers-color-scheme: dark) {
            .login-container {
                background: rgba(0, 0, 0, 0.7);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }
        }
    </style>
</head>
<body>
<a href="index.php" class="back-to-home" title="Kembali ke halaman utama">
        <i class="fas fa-arrow-left"></i>
        <span>Kembali ke Beranda</span>
    </a>


    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <img src="gmbr/cinefind.png" alt="CineFind Logo">
            </div>
            <h1>Selamat Datang</h1>
            <p>Masuk ke akun CineFind Anda</p>
        </div>

        <!-- Error/Success Messages -->
        <div id="error-message" class="alert alert-error" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="error-text">Username dan password harus diisi!</span>
        </div>

        <div id="success-message" class="alert alert-success" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span id="success-text">Login berhasil! Mengalihkan...</span>
        </div>

        <form id="loginForm" method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        placeholder="Masukkan username Anda"
                        autocomplete="username"
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Masukkan password Anda"
                        autocomplete="current-password"
                    >
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>

            <button type="submit" class="login-btn" id="loginButton">
                <i class="fas fa-sign-in-alt"></i> Masuk ke Akun
            </button>
        </form>

        <div class="register-link">
            Belum memiliki akun? <a href="register.php">Daftar sekarang</a>
        </div>
    </div>

    <script>
        // Enhanced form handling
        const form = document.getElementById('loginForm');
        const loginButton = document.getElementById('loginButton');
        const errorMessage = document.getElementById('error-message');
        const successMessage = document.getElementById('success-message');

        // Auto-hide alerts after 5 seconds
        function hideAlert(element) {
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }

        // Show error message
        function showError(message) {
            document.getElementById('error-text').textContent = message;
            errorMessage.style.display = 'flex';
            successMessage.style.display = 'none';
            hideAlert(errorMessage);
        }

        // Show success message
        function showSuccess(message) {
            document.getElementById('success-text').textContent = message;
            successMessage.style.display = 'flex';
            errorMessage.style.display = 'none';
        }

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                e.preventDefault();
                showError('Username dan password harus diisi!');
                return;
            }

            // Add loading state
            loginButton.classList.add('loading');
            loginButton.innerHTML = '<span style="opacity: 0;">Memproses...</span>';
            loginButton.disabled = true;
        });

        // Reset form state on page load
        window.addEventListener('load', function() {
            loginButton.classList.remove('loading');
            loginButton.innerHTML = '<i class="fas fa-sign-in-alt"></i> Masuk ke Akun';
            loginButton.disabled = false;
        });

        // Add smooth focus transitions
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Keyboard accessibility
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                form.submit();
            }
        });

        // Check URL parameters for messages (if using PHP redirect with messages)
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const success = urlParams.get('success');

        if (error) {
            showError(error);
        }
        if (success) {
            showSuccess(success);
        }
    </script>
</body>
</html>