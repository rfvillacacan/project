<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database configuration loaded from environment variables with defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'db_projects');

// Database connection helper
function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");

    return $conn;
}

// Existing scripts expect a $conn variable
$conn = db_connect();

// Error reporting (disable in production)
error_reporting(E_ERROR | E_PARSE); // Only show fatal errors and parse errors
ini_set('display_errors', 0); // Don't display errors to the browser

// Time zone setting
date_default_timezone_set('Asia/Riyadh'); // KSA timezone 
