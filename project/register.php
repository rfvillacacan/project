<?php
require_once 'includes/config.php';
require_once 'includes/GoogleAuth.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ?p=dashboard6.php");
    exit();
}

$googleAuth = new GoogleAuth($conn);
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token';
    } else {
        if (isset($_POST['register'])) {
            $username = trim($_POST['username']);
            $role = 'user'; // Default role for new registrations
        
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Determine if this is the first user
            $result = $conn->query("SELECT COUNT(*) as cnt FROM users");
            $row = $result->fetch_assoc();
            if ($row['cnt'] == 0) {
                $role = 'admin';
                $status = 'active';
            } else {
                $role = 'readonly';
                $status = 'pending';
            }
            // Generate Google Authenticator secret
            $secret = $googleAuth->createSecret();
            $_SESSION['temp_secret'] = $secret;
            $_SESSION['temp_username'] = $username;
            $_SESSION['temp_role'] = $role;
            $_SESSION['temp_status'] = $status;
        }
    } elseif (isset($_POST['verify'])) {
        $code = $_POST['code'];
        $secret = $_SESSION['temp_secret'];
        $username = $_SESSION['temp_username'];
        $role = $_SESSION['temp_role'];
        $status = $_SESSION['temp_status'];
        
        if ($googleAuth->verifyCode($secret, $code)) {
            // Create new user
            $stmt = $conn->prepare("INSERT INTO users (username, role, status, google_auth_secret, two_factor_enabled) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $username, $role, $status, $secret);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
                // Clear temporary data
                unset($_SESSION['temp_secret']);
                unset($_SESSION['temp_username']);
                unset($_SESSION['temp_role']);
                unset($_SESSION['temp_status']);
            } else {
                $error = "Registration failed. Please try again.";
            }
        } else {
            $error = "Invalid verification code";
        }
    }
}

// Get QR code URL if in verification step
$qrCodeUrl = null;
if (isset($_SESSION['temp_secret'])) {
    $qrCodeUrl = $googleAuth->getQRCodeUrl('NWC Hajj Monitoring (' . $_SESSION['temp_username'] . ')', $_SESSION['temp_secret']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber Tracking Portal - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel='stylesheet' href='assets/register.css'>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="card">
                <div class="card-header text-center">
                    <h3 class="mb-0">Cyber Tracking Portal - Register</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <div class="mt-3">
                                <a href="?p=login.php" class="btn btn-primary">Go to Login</a>
                            </div>
                        </div>
                    <?php elseif (isset($_SESSION['temp_secret'])): ?>
                        <div class="alert alert-info" style="background:#23272b; color:#f8f9fa; border:none;">
                            <strong>To continue, you need the Google Authenticator app.</strong><br>
                            If you haven't already, please install <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" style="color:#f8f9fa; text-decoration:underline;">Google Authenticator for Android</a> or <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" style="color:#f8f9fa; text-decoration:underline;">for iOS</a>.
                        </div>
                        <div class="text-center mb-4">
                            <div class="qr-code">
                                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
                            </div>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="mb-3">
                                <label for="code" class="form-label">Enter the 6-digit code from your Google Authenticator app:</label>
                                <input type="text" class="form-control" id="code" name="code" required pattern="[0-9]{6}" maxlength="6" placeholder="Enter 6-digit code from app">
                            </div>
                            <div class="d-grid mb-2">
                                <button type="submit" name="verify" class="btn btn-primary">Verify and Complete Registration</button>
                            </div>
                            <div class="d-grid">
                                <a href="?p=cancel_registration.php"  class="btn btn-secondary" style="background:#343a40; color:#fff; border:none;">Cancel Registration</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Choose a Username</label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="Enter your username">
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="register" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="?p=login.php" class="text-light">Already have an account? Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
