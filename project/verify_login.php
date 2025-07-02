<?php
require_once 'includes/config.php';
require_once 'includes/GoogleAuth.php';

// Handle guest login
if (isset($_GET['guest']) && $_GET['guest'] == 1) {
    // Check if guest user exists
    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE username = 'guest'");
    $stmt->execute();
    $result = $stmt->get_result();
    $guest = $result->fetch_assoc();

    if (!$guest) {
        // Create guest user if doesn't exist
        $stmt = $conn->prepare("INSERT INTO users (username, role, status) VALUES ('guest', 'readonly', 'active')");
        $stmt->execute();
        $guest_id = $conn->insert_id;
        
        // Set guest user data
        $_SESSION['user_id'] = $guest_id;
        $_SESSION['username'] = 'guest';
        $_SESSION['role'] = 'readonly';
    } else {
        // Use existing guest user
        $_SESSION['user_id'] = $guest['id'];
        $_SESSION['username'] = $guest['username'];
        $_SESSION['role'] = $guest['role'];
    }
    
    header("Location: ?p=team_task_dashboard.php");
    exit();
}

// Only process if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header("Location: ?p=login.php");
        exit();
    }
    $username = $_POST['username'];
    $code = $_POST['code'];

    // First, get the user by username
    $stmt = $conn->prepare("SELECT id, username, role, status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Only allow login if user is active and has a valid role
        if ($user['status'] !== 'active' || !in_array($user['role'], ['readonly', 'operator', 'admin'])) {
            $_SESSION['error'] = "Your account is not active or does not have a valid role. Please contact the administrator.";
            header("Location: ?p=login.php");
            exit();
        }
        // Check if 2FA is enabled
        $googleAuth = new GoogleAuth($conn);
        $secret = $googleAuth->getSecret($user['id']);
        
        if ($secret && $googleAuth->verifyCode($secret, $code)) {
            // 2FA code is valid, complete login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header("Location: ?p=dashboard6.php");
            } else {
                header("Location: ?p=team_task_dashboard.php");
            }
            exit();
        } else {
            if (!$secret) {
                $_SESSION['error'] = "Two-factor authentication is not set up for this account";
            } else {
                $_SESSION['error'] = "Invalid authentication code. Please check your Google Authenticator app and try again.";
            }
            header("Location: ?p=login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Username not found. Please check your username and try again.";
        header("Location: ?p=login.php");
        exit();
    }
} else {
    // If not a POST request, redirect to login page
    header("Location: ?p=login.php");
    exit();
}
