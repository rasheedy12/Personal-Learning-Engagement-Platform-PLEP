<?php
// auth_handlers/handle_profile_update.php
session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}
require_once BASE_URL . 'includes/db_config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("location: " . BASE_URL . "profile.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];

// --- 1. Basic Info Update ---
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$bio = trim($_POST['bio']);

if (empty($first_name) || empty($last_name)) {
    $errors[] = "First name and last name cannot be empty.";
}

// --- 2. Password Change ---
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];
$new_hashed_password = null;

if (!empty($current_password) || !empty($new_password) || !empty($confirm_new_password)) {
    if (empty($current_password)) {
        $errors[] = "To change your password, you must enter your current password.";
    } elseif (empty($new_password)) {
        $errors[] = "Please enter a new password.";
    } elseif ($new_password !== $confirm_new_password) {
        $errors[] = "New password and confirmation do not match.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long.";
    } else {
        $sql_check_pass = "SELECT password_hash FROM users WHERE user_id = ?";
        if ($stmt_pass = $mysqli->prepare($sql_check_pass)) {
            $stmt_pass->bind_param("i", $user_id);
            $stmt_pass->execute();
            $stmt_pass->store_result();
            if ($stmt_pass->num_rows === 1) {
                $stmt_pass->bind_result($current_hashed_password);
                $stmt_pass->fetch();
                if (password_verify($current_password, $current_hashed_password)) {
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                } else {
                    $errors[] = "The current password you entered is incorrect.";
                }
            } else {
                 $errors[] = "Could not find user to verify password.";
            }
            $stmt_pass->close();
        } else {
            $errors[] = "Database error (password check).";
            error_log($mysqli->error);
        }
    }
}

// --- 3. Avatar Upload ---
$new_avatar_path_for_db = null;
if (isset($_FILES["profile_avatar"]) && $_FILES["profile_avatar"]["error"] == 0) {
    $allowed_types = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
    $max_size = 2 * 1024 * 1024; // 2 MB

    $file_type = $_FILES["profile_avatar"]["type"];
    $file_size = $_FILES["profile_avatar"]["size"];
    $file_ext = strtolower(pathinfo($_FILES["profile_avatar"]["name"], PATHINFO_EXTENSION));

    if (!array_key_exists($file_ext, $allowed_types) || !in_array($file_type, $allowed_types)) {
        $errors[] = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
    } elseif ($file_size > $max_size) {
        $errors[] = "File size is too large. Maximum is 2MB.";
    } else {
        // More robust path finding from this script's location
        $server_upload_dir = dirname(__DIR__) . '/images/avatars/';
        if (!is_dir($server_upload_dir)) {
            if (!mkdir($server_upload_dir, 0775, true)) { // Use 0775 for better permissions
                $errors[] = "Failed to create upload directory. Please check server permissions.";
            }
        }
        
        if (is_writable($server_upload_dir)) {
            // Fetch old avatar path to delete it later
            $old_avatar_path_for_db = null;
            $sql_get_old = "SELECT avatar_url FROM users WHERE user_id = ?";
            if ($stmt_old = $mysqli->prepare($sql_get_old)) {
                $stmt_old->bind_param("i", $user_id);
                $stmt_old->execute();
                $stmt_old->bind_result($old_avatar_path_for_db);
                $stmt_old->fetch();
                $stmt_old->close();
            }

            $new_filename = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $target_path_on_server = $server_upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["profile_avatar"]["tmp_name"], $target_path_on_server)) {
                // Path to store in DB is relative to web root
                $new_avatar_path_for_db = "images/avatars/" . $new_filename;
                // Delete old file if it exists and is not the placeholder
                if ($old_avatar_path_for_db && file_exists(dirname(__DIR__) . '/' . $old_avatar_path_for_db)) {
                    unlink(dirname(__DIR__) . '/' . $old_avatar_path_for_db);
                }
            } else {
                $errors[] = "There was an error moving your uploaded file.";
            }
        } else {
            $errors[] = "Upload directory is not writable. Please check server permissions for " . htmlspecialchars($server_upload_dir);
        }
    }
}

// --- 4. Update the Database ---
if (empty($errors)) {
    // Build query dynamically
    $sql_parts = [];
    $params = [];
    $types = "";

    // Always update basic info
    $sql_parts[] = "first_name = ?"; $params[] = $first_name; $types .= "s";
    $sql_parts[] = "last_name = ?";  $params[] = $last_name;  $types .= "s";
    $sql_parts[] = "bio = ?";        $params[] = $bio;        $types .= "s";
    
    if ($new_hashed_password) {
        $sql_parts[] = "password_hash = ?"; $params[] = $new_hashed_password; $types .= "s";
    }
    if ($new_avatar_path_for_db) {
        $sql_parts[] = "avatar_url = ?"; $params[] = $new_avatar_path_for_db; $types .= "s";
    }

    if (!empty($sql_parts)) {
        $sql_update = "UPDATE users SET " . implode(", ", $sql_parts) . " WHERE user_id = ?";
        $params[] = $user_id;
        $types .= "i";

        if ($stmt_update = $mysqli->prepare($sql_update)) {
            $stmt_update->bind_param($types, ...$params);
            if ($stmt_update->execute()) {
                $_SESSION['success_message'] = "Profile updated successfully!";
                // Update session variables if they changed
                $_SESSION['user_name'] = $first_name . " " . $last_name;
                if ($new_avatar_path_for_db) {
                    $_SESSION['user_avatar'] = BASE_URL . $new_avatar_path_for_db;
                }
            } else {
                $errors[] = "Database update failed. Please try again.";
                error_log("Profile Update Error: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            $errors[] = "Database error preparing update. Please try again.";
            error_log("Profile Update Error: " . $mysqli->error);
        }
    } else {
        // This case would mean nothing was changed, which is not an error.
        // We can just set a neutral message if desired.
        $_SESSION['success_message'] = "No changes were made to your profile.";
    }
}

// If there were any errors, store the first one in the session to be displayed
if (!empty($errors)) {
    $_SESSION['error_message'] = $errors[0];
}

// Redirect back to the profile page
header("location: " . BASE_URL . "profile.php");
exit;
?>
