<?php
// ============================================================
// LOGIN PAGE — login.php
// ============================================================
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect based on role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$pageTitle = 'Sign In';
$basePath  = '';
$error     = '';

// Where to go after successful login for normal users
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// ── HANDLE FORM SUBMISSION (POST) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleType = trim($_POST['role_type'] ?? 'customer');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check password and role
        if ($user && password_verify($password, $user['password_hash'])) {
            
            // Validate that they selected the correct role
            if ($user['role'] !== $roleType) {
                $error = 'Access denied. You selected the wrong account type.';
            } else {
                // ✅ Login successful!
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect admins directly to the dashboard
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: ' . $redirect);
                }
                exit;
            }

        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — DriveEase</title>
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

        <div class="form-title" style="text-align:center;">Welcome back</div>
        <div class="form-subtitle" style="text-align:center;margin-bottom:1.5rem;">Sign in to your account</div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                Error: <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success" style="background:#dcfce7;color:#166534;padding:1rem;border-radius:6px;margin-bottom:1rem;">
                Success: Account created! Please sign in.
            </div>
        <?php endif; ?>

        <form method="post" action="login.php?redirect=<?= urlencode($redirect) ?>" class="validated">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>I am logging in as:</label>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem; background: #f9fafb; padding: 1rem; border-radius: 6px; border: 1px solid #eee;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer; flex: 1;">
                        <input type="radio" name="role_type" value="customer" <?= (isset($_POST['role_type']) && $_POST['role_type'] === 'admin') ? '' : 'checked' ?> style="width: auto;">
                        Normal User
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer; flex: 1;">
                        <input type="radio" name="role_type" value="admin" <?= (isset($_POST['role_type']) && $_POST['role_type'] === 'admin') ? 'checked' : '' ?> style="width: auto;">
                        Administrator
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label for="password" style="margin:0;">Password</label>
                    <a href="forgot-password.php" style="font-size:0.85rem; color:var(--primary-color);">Forgot password?</a>
                </div>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Your password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">
                Sign In
            </button>
        </form>

        <div class="form-footer">
            Don't have an account?
            <a href="register.php">Create one free</a>
        </div>

    </div>
</div>

<script src="<?= $basePath ?>js/main.js"></script>
</body>
</html>
