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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $secret = $googleAuth->getSecret($userId);
    
    if ($googleAuth->verifyCode($secret, $code)) {
        // Code is valid, disable 2FA
        $googleAuth->disableTwoFactor($userId);
        $_SESSION['success'] = "Two-factor authentication has been disabled successfully!";
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disable Two-Factor Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card bg-dark border-light">
                    <div class="card-header">
                        <h3 class="mb-0">Disable Two-Factor Authentication</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <p>To disable two-factor authentication, please enter the current code from your authenticator app:</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="code" class="form-label">Enter the 6-digit code:</label>
                                <input type="text" class="form-control" id="code" name="code" required pattern="[0-9]{6}" maxlength="6">
                            </div>
                            <button type="submit" class="btn btn-danger">Disable 2FA</button>
                            <a href="enable_2fa.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
