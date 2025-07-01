<?php
require_once 'includes/config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ?p=dashboard6.php");
    exit();
}

// Get any error messages
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AUTHMAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel='stylesheet' href='assets/login.css'>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="mb-0">Cyber Tracking Portal </br>Login</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form action="?p=verify_login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required placeholder="Enter your username">
                        </div>
                        <div class="mb-3">
                            <label for="code" class="form-label">Google Authenticator Code</label>
                            <input type="text" class="form-control" id="code" name="code" required pattern="[0-9]{6}" maxlength="6" placeholder="Enter 6-digit code from app">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    <div class="d-grid mt-3">
                        <a href="?p=verify_login.php&guest=1" class="btn btn-secondary">Login as Guest</a>
                    </div>
                    <div class="text-center mt-3">
                        <a href="?p=register.php" class="text-light">Don't have an account? Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 




