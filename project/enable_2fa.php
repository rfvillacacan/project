<?php
require_once 'includes/config.php';
require_once 'includes/GoogleAuth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$googleAuth = new GoogleAuth($conn);
$userId = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_2fa'])) {
        $secret = $googleAuth->createSecret();
        $googleAuth->saveSecret($userId, $secret);
        $_SESSION['temp_secret'] = $secret; // Store temporarily for verification
    } elseif (isset($_POST['verify_code'])) {
        $code = $_POST['code'];
        $secret = $_SESSION['temp_secret'];
        
        if ($googleAuth->verifyCode($secret, $code)) {
            // Code is valid, 2FA is now enabled
            unset($_SESSION['temp_secret']);
            $_SESSION['success'] = "Two-factor authentication has been enabled successfully!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid verification code. Please try again.";
        }
    }
}

// Get current 2FA status
$isEnabled = $googleAuth->isTwoFactorEnabled($userId);
$secret = $_SESSION['temp_secret'] ?? $googleAuth->getSecret($userId);
$qrCodeUrl = $secret ? $googleAuth->getQRCodeUrl($_SESSION['username'], $secret) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enable Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-dark border-light">
                    <div class="card-header">
                        <h3 class="mb-0">Two-Factor Authentication</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (!$isEnabled && !isset($_SESSION['temp_secret'])): ?>
                            <p>Enhance your account security by enabling two-factor authentication.</p>
                            <form method="POST">
                                <button type="submit" name="enable_2fa" class="btn btn-primary">Enable 2FA</button>
                            </form>
                        <?php elseif (isset($_SESSION['temp_secret']) || $isEnabled): ?>
                            <p>Scan this QR code with your Google Authenticator app:</p>
                            <?php if ($qrCodeUrl): ?>
                                <div class="text-center mb-4">
                                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Enter the 6-digit code from your authenticator app:</label>
                                    <input type="text" class="form-control" id="code" name="code" required pattern="[0-9]{6}" maxlength="6">
                                </div>
                                <button type="submit" name="verify_code" class="btn btn-primary">Verify and Enable</button>
                            </form>
                        <?php else: ?>
                            <p>Two-factor authentication is already enabled for your account.</p>
                            <form method="POST" action="disable_2fa.php">
                                <button type="submit" class="btn btn-danger">Disable 2FA</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
