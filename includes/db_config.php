<?php
define('DB_SERVER', 'localhost'); 
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_NAME', 'Smartlearn');   

/* Attempt to connect to MySQL database */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($mysqli === false){
    // Don't display detailed errors in production for security reasons.
    // Log the error instead.
    error_log("ERROR: Could not connect to database. " . $mysqli->connect_error);
    die("ERROR: Could not connect. Please try again later."); // User-friendly message
}

// Optional: Set character set to utf8mb4 for full Unicode support
if (!$mysqli->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $mysqli->error);
    // You might not want to die here, but be aware of potential encoding issues.
}
?>