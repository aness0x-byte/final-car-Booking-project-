<?php
require_once 'config/db.php';
$pageTitle = 'Forgot Password';
$basePath  = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$resetLink = ''; // In a real app, this would be emailed. For this demo, we'll display it.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            // Set expiry to 1 hour from now
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $update = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?");
            $update->execute([$token, $expires, $user['id']]);

            // Construct the reset link
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            // remove script name to get directory
            $dir = dirname($_SERVER['PHP_SELF']);
            if ($dir === '/' || $dir === '\\') $dir = '';
            
            $resetUrl = $protocol . $domainName . $dir . '/reset-password.php?token=' . $token;

            $success = "A password reset link has been generated.";
            $resetLink = $resetUrl; // Display it for testing
        } else {
            // For security, don't reveal if the email exists or not
            $success = "If the email is registered, a password reset link has been generated.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — DriveEase</title>
    <link rel="stylesheet" href="<?= $basePath ?>css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <a href="index.php" class="auth-logo">
            <div class="logo-icon">D</div>
            DriveEase
        </a>

        <div class="form-title" style="text-align:center;">Reset Password</div>
        <div class="form-subtitle" style="text-align:center;margin-bottom:1.5rem;">Enter your email to receive a reset link</div>

        <?php if ($error): ?>
            <div class="alert alert-error">Error: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="background:#dcfce7;color:#166534;padding:1rem;border-radius:6px;margin-bottom:1.5rem;">
                Success: <?= htmlspecialchars($success) ?>
                
                <?php if ($resetLink): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.8); border: 1px dashed #166534; border-radius: 4px; word-break: break-all;">
                        <strong>[Demo Purpose] Click here to reset:</strong><br>
                        <a href="<?= htmlspecialchars($resetLink) ?>" style="color: #166534; text-decoration: underline; font-weight: bold;">Reset My Password</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <a href="login.php" class="btn btn-outline btn-full">Back to Login</a>
        <?php else: ?>
            <form method="post" action="forgot-password.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">
                    Send Reset Link
                </button>
            </form>
            <div class="form-footer">
                Remember your password? <a href="login.php">Sign in</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="<?= $basePath ?>js/main.js"></script>
</body>
</html>
