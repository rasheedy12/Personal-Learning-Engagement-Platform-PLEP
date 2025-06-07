<?php
// auth_handlers/handle_profile_update.php
session_start();

// Define BASE_URL and include db config
if (!defined('BASE_URL')) {
    define('BASE_URL', '../');
}
require_once BASE_URL . 'includes/db_config.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: " . BASE_URL . "login.php");
    exit;
}

// Ensure it's a POST request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("location: " . BASE_URL . "profile.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = "";

// --- 1. Handle Basic Info Update ---
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$bio = trim($_POST['bio']);

// Basic validation
if (empty($first_name) || empty($last_name)) {
    $errors[] = "First name and last name cannot be empty.";
}

// --- 2. Handle Password Change ---
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];
$new_hashed_password = null;

if (!empty($current_password) || !empty($new_password) || !empty($confirm_new_password)) {
    if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
        $errors[] = "To change your password, you must fill all three password fields.";
    } elseif ($new_password !== $confirm_new_password) {
        $errors[] = "New password and confirmation password do not match.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long.";
    } else {
        // Verify current password
        $sql_check_pass = "SELECT password_hash FROM users WHERE user_id = ?";
        if ($stmt_pass = $mysqli->prepare($sql_check_pass)) {
            $stmt_pass->bind_param("i", $user_id);
            $stmt_pass->execute();
            $stmt_pass->bind_result($current_hashed_password);
            if ($stmt_pass->fetch() && password_verify($current_password, $current_hashed_password)) {
                // Current password is correct, hash the new one
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $errors[] = "The current password you entered is incorrect.";
            }
            $stmt_pass->close();
        } else {
            $errors[] = "Database error verifying password.";
        }
    }
}

// --- 3. Handle Avatar Upload ---
$new_avatar_path = null;
if (isset($_FILES["profile_avatar"]) && $_FILES["profile_avatar"]["error"] == 0) {
    $allowed_types = ["jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png"];
    $file_name = $_FILES["profile_avatar"]["name"];
    $file_type = $_FILES["profile_avatar"]["type"];
    $file_size = $_FILES["profile_avatar"]["size"];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Verify file extension and MIME type
    if (!array_key_exists($file_ext, $allowed_types) || !in_array($file_type, $allowed_types)) {
        $errors[] = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
    }

    // Verify file size (e.g., 2MB max)
    $max_size = 2 * 1024 * 1024;
    if ($file_size > $max_size) {
        $errors[] = "File size is too large. Maximum is 2MB.";
    }

    if (empty($errors)) {
        // Create a unique filename and define target path
        $upload_dir = BASE_URL . "images/avatars/"; // Ensure this directory exists and is writable
        // The directory needs to be relative to the file system, not the web root for move_uploaded_file
        $server_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/smartlearn_php_project/images/avatars/'; // Adjust path if needed
        if (!is_dir($server_upload_dir)) {
            mkdir($server_upload_dir, 0755, true);
        }
        $new_filename = "user_" . $user_id . "_" . uniqid() . "." . $file_ext;
        $target_path = $server_upload_dir . $new_filename;
        
        // Move the file
        if (move_uploaded_file($_FILES["profile_avatar"]["tmp_name"], $target_path)) {
            // Path to store in DB should be relative to web root
            $new_avatar_path = "images/avatars/" . $new_filename; 
        } else {
            $errors[] = "There was an error uploading your file.";
        }
    }
}

// --- 4. Update the Database ---
if (empty($errors)) {
    // Build the query dynamically
    $sql_update = "UPDATE users SET first_name = ?, last_name = ?, bio = ?";
    $params = [$first_name, $last_name, $bio];
    $types = "sss";

    if ($new_hashed_password) {
        $sql_update .= ", password_hash = ?";
        $params[] = $new_hashed_password;
        $types .= "s";
    }

    if ($new_avatar_path) {
        // Optional: Delete old avatar file from server
        // $sql_get_old_avatar = "SELECT avatar_url FROM users WHERE user_id = ?"; ... fetch and unlink old file ...

        $sql_update .= ", avatar_url = ?";
        $params[] = $new_avatar_path;
        $types .= "s";
    }

    $sql_update .= " WHERE user_id = ?";
    $params[] = $user_id;
    $types .= "i";

    if ($stmt_update = $mysqli->prepare($sql_update)) {
        $stmt_update->bind_param($types, ...$params);
        if ($stmt_update->execute()) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            // Update session variables if they changed
            $_SESSION['user_name'] = $first_name . " " . $last_name;
            if ($new_avatar_path) {
                $_SESSION['user_avatar'] = BASE_URL . $new_avatar_path;
            }
        } else {
            $_SESSION['error_message'] = "Database update failed. Please try again.";
        }
        $stmt_update->close();
    } else {
        $_SESSION['error_message'] = "Database error. Please try again.";
    }
} else {
    // If there were any errors, store them in the session
    $_SESSION['error_message'] = implode(" ", $errors);
}

// Redirect back to the profile page
header("location: " . BASE_URL . "profile.php");
exit;
?>

