<?php
require_once 'config/db.php';
$pageTitle = 'Reset Password';
$basePath  = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$validToken = false;
$user_id = null;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid or missing password reset token.';
} else {
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $validToken = true;
        $user_id = $user['id'];
    } else {
        $error = 'This password reset link is invalid or has expired.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password and clear token
        $update = $db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $update->execute([$hashedPassword, $user_id]);

        $success = 'Your password has been successfully reset. You can now login.';
        $validToken = false; // Hide form
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — DriveEase</title>
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

        <div class="form-title" style="text-align:center;">Create New Password</div>
        
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:1.5rem;">Error: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" style="background:#dcfce7;color:#166534;padding:1rem;border-radius:6px;margin-bottom:1.5rem;text-align:center;">
                Success: <?= htmlspecialchars($success) ?>
            </div>
            <a href="login.php" class="btn btn-primary btn-full">Go to Sign In</a>
        <?php elseif ($validToken): ?>
            <div class="form-subtitle" style="text-align:center;margin-bottom:1.5rem;">Enter your new password below</div>
            
            <form method="post" action="reset-password.php?token=<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm New Password</label>
                    <input type="password" id="confirm" name="confirm" placeholder="Repeat new password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">
                    Reset Password
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="forgot-password.php" class="btn btn-outline btn-full">Request a New Link</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="<?= $basePath ?>js/main.js"></script>
</body>
</html>
