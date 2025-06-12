<?php
session_start();

// Jika sudah login, redirect ke halaman pengguna
if (isset($_SESSION['user_id'])) {
    header('Location: pengguna/index.php');
    exit;
}

// Proses registrasi
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    if (empty($nama_lengkap) || empty($email) || empty($username) || empty($password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Cek email sudah terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Cek username sudah terdaftar
            $stmt = $conn->prepare("SELECT id_role FROM role WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Username sudah digunakan!';
            } else {
                // Mulai transaksi
                $conn->begin_transaction();
                
                try {
                    // Insert ke tabel role untuk username dengan role 'pengguna'
                    $stmt = $conn->prepare("INSERT INTO role (username, email, password, role) VALUES (?, ?, ?, 'pengguna')");
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->bind_param("sss", $username, $email, $hashed_password);
                    $stmt->execute();
                    
                    $role_id = $conn->insert_id;
                    
                    // Insert ke tabel pengguna
                    $stmt = $conn->prepare("INSERT INTO pengguna (nama_lengkap, role_id) VALUES (?, ?)");
                    $stmt->bind_param("si", $nama_lengkap, $role_id);
                    $stmt->execute();
                    
                    $conn->commit();
                    $success = 'Registrasi berhasil! Silakan login.';
                    
                    // Redirect ke halaman login setelah 2 detik
                    header("refresh:2;url=login.php");
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = 'Terjadi kesalahan saat registrasi!';
                }
            }
        }
        $stmt->close();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CineFind</title>
    <link rel="icon" type="image/png" href="gmbr/cinefind.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .register-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 48px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3),
                        0 0 0 1px rgba(255, 255, 255, 0.05);
            width: 100%;
            max-width: 480px;
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

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 24px;
        }

        .register-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(229, 9, 20, 0.3);
            box-shadow: 0 8px 24px rgba(229, 9, 20, 0.2);
            transition: all 0.3s ease;
        }

        .register-header img:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 32px rgba(229, 9, 20, 0.3);
        }

        .register-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff 0%, #e50914 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .register-header p {
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin: 4px 0;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak .strength-fill { background: #ef4444; width: 33%; }
        .strength-medium .strength-fill { background: #f59e0b; width: 66%; }
        .strength-strong .strength-fill { background: #10b981; width: 100%; }

        .register-btn {
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
            position: relative;
            overflow: hidden;
            margin-bottom: 32px;
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .register-btn:hover::before {
            left: 100%;
        }

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(229, 9, 20, 0.3);
        }

        .register-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            font-weight: 400;
        }

        .login-link a {
            color: #e50914;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-link a::after {
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

        .login-link a:hover::after {
            width: 100%;
        }

        .login-link a:hover {
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

        /* Validation styles */
        .form-group.valid input {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-group.invalid input {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .validation-message {
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .validation-message.valid {
            color: #10b981;
        }

        .validation-message.invalid {
            color: #ef4444;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 32px 24px;
                margin: 16px;
                border-radius: 20px;
            }

            .register-header h1 {
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
            .register-container {
                padding: 24px 20px;
            }
        }

        /* Dark mode enhancements */
        @media (prefers-color-scheme: dark) {
            .register-container {
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

    <div class="register-container">
        <div class="register-header">
            <div class="logo-container">
                <img src="gmbr/cinefind.png" alt="CineFind Logo">
            </div>
            <h1>Bergabung dengan CineFind</h1>
            <p>Buat akun baru untuk mulai menjelajahi film</p>
        </div>

        <!-- Error/Success Messages -->
        <div id="error-message" class="alert alert-error" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="error-text">Semua field harus diisi!</span>
        </div>

        <div id="success-message" class="alert alert-success" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span id="success-text">Registrasi berhasil! Mengalihkan...</span>
        </div>

        <form id="registerForm" method="POST" action="register.php">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="nama_lengkap" 
                        name="nama_lengkap" 
                        required 
                        placeholder="Masukkan nama lengkap Anda"
                        autocomplete="name"
                    >
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrapper">
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="contoh@email.com"
                            autocomplete="email"
                        >
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div id="email-validation" class="validation-message" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            placeholder="Pilih username unik"
                            autocomplete="username"
                        >
                        <i class="fas fa-at input-icon"></i>
                    </div>
                    <div id="username-validation" class="validation-message" style="display: none;"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="Buat password yang kuat"
                            autocomplete="new-password"
                        >
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill"></div>
                        </div>
                        <div id="password-text" class="validation-message" style="display: none;"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                            placeholder="Ketik ulang password"
                            autocomplete="new-password"
                        >
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    <div id="confirm-validation" class="validation-message" style="display: none;"></div>
                </div>
            </div>

            <button type="submit" class="register-btn" id="registerButton">
                <i class="fas fa-user-plus"></i> Buat Akun
            </button>
        </form>

        <div class="login-link">
            Sudah memiliki akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>

    <script>
        // Form elements
        const form = document.getElementById('registerForm');
        const registerButton = document.getElementById('registerButton');
        const errorMessage = document.getElementById('error-message');
        const successMessage = document.getElementById('success-message');

        // Input elements
        const namaLengkap = document.getElementById('nama_lengkap');
        const email = document.getElementById('email');
        const username = document.getElementById('username');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        // Validation elements
        const emailValidation = document.getElementById('email-validation');
        const usernameValidation = document.getElementById('username-validation');
        const passwordText = document.getElementById('password-text');
        const confirmValidation = document.getElementById('confirm-validation');
        const strengthBar = document.querySelector('.strength-bar');

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

        // Email validation
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            let text = '';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthBar.className = 'strength-bar';
            
            if (strength < 2) {
                strengthBar.classList.add('strength-weak');
                text = 'Password lemah';
            } else if (strength < 4) {
                strengthBar.classList.add('strength-medium');
                text = 'Password sedang';
            } else {
                strengthBar.classList.add('strength-strong');
                text = 'Password kuat';
            }

            return { strength, text };
        }

        // Real-time validation
        email.addEventListener('input', function() {
            const emailGroup = this.closest('.form-group');
            if (validateEmail(this.value)) {
                emailGroup.classList.remove('invalid');
                emailGroup.classList.add('valid');
                emailValidation.style.display = 'flex';
                emailValidation.className = 'validation-message valid';
                emailValidation.innerHTML = '<i class="fas fa-check"></i> Email valid';
            } else if (this.value) {
                emailGroup.classList.remove('valid');
                emailGroup.classList.add('invalid');
                emailValidation.style.display = 'flex';
                emailValidation.className = 'validation-message invalid';
                emailValidation.innerHTML = '<i class="fas fa-times"></i> Format email tidak valid';
            } else {
                emailGroup.classList.remove('valid', 'invalid');
                emailValidation.style.display = 'none';
            }
        });

        username.addEventListener('input', function() {
            const usernameGroup = this.closest('.form-group');
            if (this.value.length >= 3) {
                usernameGroup.classList.remove('invalid');
                usernameGroup.classList.add('valid');
                usernameValidation.style.display = 'flex';
                usernameValidation.className = 'validation-message valid';
                usernameValidation.innerHTML = '<i class="fas fa-check"></i> Username tersedia';
            } else if (this.value) {
                usernameGroup.classList.remove('valid');
                usernameGroup.classList.add('invalid');
                usernameValidation.style.display = 'flex';
                usernameValidation.className = 'validation-message invalid';
                usernameValidation.innerHTML = '<i class="fas fa-times"></i> Minimal 3 karakter';
            } else {
                usernameGroup.classList.remove('valid', 'invalid');
                usernameValidation.style.display = 'none';
            }
        });

        password.addEventListener('input', function() {
            const passwordGroup = this.closest('.form-group');
            const result = checkPasswordStrength(this.value);
            
            if (this.value) {
                passwordText.style.display = 'flex';
                if (result.strength >= 3) {
                    passwordGroup.classList.remove('invalid');
                    passwordGroup.classList.add('valid');
                    passwordText.className = 'validation-message valid';
                    passwordText.innerHTML = `<i class="fas fa-check"></i> ${result.text}`;
                } else {
                    passwordGroup.classList.remove('valid');
                    passwordGroup.classList.add('invalid');
                    passwordText.className = 'validation-message invalid';
                    passwordText.innerHTML = `<i class="fas fa-info-circle"></i> ${result.text}`;
                }
            } else {
                passwordGroup.classList.remove('valid', 'invalid');
                passwordText.style.display = 'none';
                strengthBar.className = 'strength-bar';
            }
            
            // Re-validate confirm password if it has value
            if (confirmPassword.value) {
                validateConfirmPassword();
            }
        });

        function validateConfirmPassword() {
            const confirmGroup = confirmPassword.closest('.form-group');
            if (confirmPassword.value === password.value && password.value) {
                confirmGroup.classList.remove('invalid');
                confirmGroup.classList.add('valid');
                confirmValidation.style.display = 'flex';
                confirmValidation.className = 'validation-message valid';
                confirmValidation.innerHTML = '<i class="fas fa-check"></i> Password cocok';
            } else if (confirmPassword.value) {
                confirmGroup.classList.remove('valid');
                confirmGroup.classList.add('invalid');
                confirmValidation.style.display = 'flex';
                confirmValidation.className = 'validation-message invalid';
                confirmValidation.innerHTML = '<i class="fas fa-times"></i> Password tidak cocok';
            } else {
                confirmGroup.classList.remove('valid', 'invalid');
                confirmValidation.style.display = 'none';
            }
        }

        confirmPassword.addEventListener('input', validateConfirmPassword);

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            const nama = namaLengkap.value.trim();
            const emailVal = email.value.trim();
            const usernameVal = username.value.trim();
            const passwordVal = password.value;
            const confirmVal = confirmPassword.value;

            // Client-side validation
            if (!nama || !emailVal || !usernameVal || !passwordVal || !confirmVal) {
                e.preventDefault();
                showError('Semua field harus diisi!');
                return;
            }

            if (!validateEmail(emailVal)) {
                e.preventDefault();
                showError('Format email tidak valid!');
                return;
            }

            if (passwordVal !== confirmVal) {
                e.preventDefault();
                showError('Password tidak cocok!');
                return;
            }

            if (passwordVal.length < 6) {
                e.preventDefault();
                showError('Password minimal 6 karakter!');
                return;
            }

            // Add loading state
            registerButton.classList.add('loading');
            registerButton.innerHTML = '<span style="opacity: 0;">Memproses...</span>';
            registerButton.disabled = true;
        });

        // Reset form state on page load
        window.addEventListener('load', function() {
            registerButton.classList.remove('loading');
            registerButton.innerHTML = '<i class="fas fa-user-plus"></i> Buat Akun';
            registerButton.disabled = false;
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