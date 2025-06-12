<?php
session_start();
require_once '../config/database.php'; // Path relatif dari update_profile.php

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id_session = $_SESSION['user_id']; // Ini adalah id_role

try {
    $conn->begin_transaction();
    $response_data = [];

    // Handle profile photo upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF are allowed.');
        }
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            throw new Exception('File is too large. Maximum size is 5MB.');
        }

        $stmt_old_photo = $conn->prepare("SELECT foto_profil FROM pengguna WHERE role_id = ?");
        if (!$stmt_old_photo) throw new Exception("Prepare statement (old photo) failed: " . $conn->error);
        $stmt_old_photo->bind_param("i", $user_id_session);
        $stmt_old_photo->execute();
        $old_photo_result = $stmt_old_photo->get_result();
        $old_photo_row = $old_photo_result->fetch_assoc();
        $old_photo_path_db = $old_photo_row ? $old_photo_row['foto_profil'] : null;
        $stmt_old_photo->close();

        $upload_dir_from_root = 'uploads/profiles/'; // Path dari root proyek
        $upload_dir_for_move = '../' . $upload_dir_from_root; // Path untuk move_uploaded_file, relatif dari update_profile.php

        if (!is_dir($upload_dir_for_move) && !mkdir($upload_dir_for_move, 0775, true) && !is_dir($upload_dir_for_move)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $upload_dir_for_move));
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('avatar_', true) . '.' . $file_extension; // Nama file unik
        $target_path_for_move = $upload_dir_for_move . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path_for_move)) {
            if ($old_photo_path_db && $old_photo_path_db !== 'assets/images/default-avatar.png' && file_exists('../' . $old_photo_path_db)) {
                @unlink('../' . $old_photo_path_db);
            }
            
            $relative_path_for_db = $upload_dir_from_root . $filename;
            
            $stmt_update_photo = $conn->prepare("UPDATE pengguna SET foto_profil = ? WHERE role_id = ?");
            if (!$stmt_update_photo) throw new Exception("Prepare statement (update photo) failed: " . $conn->error);
            $stmt_update_photo->bind_param("si", $relative_path_for_db, $user_id_session);
            if (!$stmt_update_photo->execute()) throw new Exception("Execute statement (update photo) failed: " . $stmt_update_photo->error);
            $stmt_update_photo->close();
            
            $_SESSION['foto_profil'] = $relative_path_for_db;
            $response_data['avatar'] = $relative_path_for_db; // Kirim path ini kembali ke JS
        } else {
            throw new Exception('Failed to upload profile photo. Error: ' . $_FILES['avatar']['error']);
        }
    }

    // Handle profile information update
    if (isset($_POST['name']) || isset($_POST['username']) || isset($_POST['email'])) {
        // Update pengguna table
        if (isset($_POST['name'])) {
            $stmt = $conn->prepare("UPDATE pengguna SET nama_lengkap = ? WHERE role_id = ?");
            $stmt->bind_param("si", $_POST['name'], $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $response_data['name'] = $_POST['name'];
        }

        // Update email in pengguna table
        if (isset($_POST['email'])) {
             // Optional: Add email validation here if needed
             // if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
             //    throw new Exception('Invalid email format');
             // }
             
             // Check if email is already taken by another user (excluding the current user)
             $stmt = $conn->prepare("SELECT id_pengguna FROM pengguna WHERE email = ? AND role_id != ?");
             $stmt->bind_param("si", $_POST['email'], $_SESSION['user_id']);
             $stmt->execute();
             if ($stmt->get_result()->num_rows > 0) {
                 throw new Exception('Email address is already taken');
             }
             $stmt->close();

            $stmt = $conn->prepare("UPDATE pengguna SET email = ? WHERE role_id = ?");
            $stmt->bind_param("si", $_POST['email'], $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();
            $response_data['email'] = $_POST['email'];
        }

        // Update username in role table
        if (isset($_POST['username'])) {
            $username_baru = trim($_POST['username']);
            if (empty($username_baru) || strlen($username_baru) < 3) throw new Exception('Username must be at least 3 characters.');
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $username_baru)) throw new Exception('Username can only contain letters, numbers, and underscores.');

            $stmt_check_username = $conn->prepare("SELECT id_role FROM role WHERE username = ? AND id_role != ?");
            if (!$stmt_check_username) throw new Exception("Prepare statement (check username) failed: " . $conn->error);
            $stmt_check_username->bind_param("si", $username_baru, $user_id_session);
            $stmt_check_username->execute();
            if ($stmt_check_username->get_result()->num_rows > 0) throw new Exception('Username already taken.');
            $stmt_check_username->close();

            $stmt_update_username = $conn->prepare("UPDATE role SET username = ? WHERE id_role = ?");
            if (!$stmt_update_username) throw new Exception("Prepare statement (username) failed: " . $conn->error);
            $stmt_update_username->bind_param("si", $username_baru, $user_id_session);
            if (!$stmt_update_username->execute()) throw new Exception("Execute statement (username) failed: " . $stmt_update_username->error);
            $stmt_update_username->close();
            $_SESSION['username'] = $username_baru;
            $response_data['username'] = $username_baru;
        }
        
        // Handle update password (jika field diisi)
        if (isset($_POST['current_password']) && !empty($_POST['current_password']) && isset($_POST['new_password']) && !empty($_POST['new_password'])) {
            // ... (logika update password Anda, pastikan validasi panjang password, dll.)
            // Contoh singkat:
            $stmt_pass = $conn->prepare("SELECT password FROM role WHERE id_role = ?");
            $stmt_pass->bind_param("i", $user_id_session);
            $stmt_pass->execute();
            $user_pass_data = $stmt_pass->get_result()->fetch_assoc();
            $stmt_pass->close();

            if ($user_pass_data && password_verify($_POST['current_password'], $user_pass_data['password'])) {
                if(strlen($_POST['new_password']) < 6) { // Sesuaikan dengan validasi di JS
                     throw new Exception('New password must be at least 6 characters long.');
                }
                $new_hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $stmt_update_pass = $conn->prepare("UPDATE role SET password = ? WHERE id_role = ?");
                $stmt_update_pass->bind_param("si", $new_hashed_password, $user_id_session);
                $stmt_update_pass->execute();
                $stmt_update_pass->close();
                $response_data['password_updated'] = true;
            } else {
                throw new Exception('Current password incorrect.');
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.', 'data' => $response_data]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Error in update_profile.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>