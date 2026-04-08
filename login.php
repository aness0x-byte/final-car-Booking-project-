<?php
// ============================================================
// LOGIN PAGE — login.php
// ============================================================
// GET  request: Show the login form
// POST request: Validate credentials and start a session
//
// Security measures used:
//   - password_verify() to check hashed passwords (never plain text)
//   - htmlspecialchars() to prevent XSS in displayed values
//   - Prepared statements to prevent SQL injection
//   - Session regeneration after login (prevents session fixation)
// ============================================================

require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Sign In';
$basePath  = '';
$error     = '';

// Where to go after successful login (passed via GET param)
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// ── HANDLE FORM SUBMISSION (POST) ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read form fields (trim = remove extra spaces)
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in both email and password.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } else {
        $db = getDB();

        // Look up the user by email
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Check if user exists AND password matches the stored hash
        if ($user && password_verify($password, $user['password'])) {

            // ✅ Login successful!
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Store user info in the session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect to the page they were trying to visit (or home)
            header('Location: ' . $redirect);
            exit;

        } else {
            // ❌ Wrong email or password
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

        <!-- Logo -->
        <a href="index.php" class="auth-logo">
            <div class="logo-icon">D</div>
            DriveEase
        </a>

        <div class="form-title" style="text-align:center;">Welcome back</div>
        <div class="form-subtitle" style="text-align:center;margin-bottom:1.5rem;">Sign in to your account to book cars</div>

        <!-- Error message (shown only when there's an error) -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <!-- method="post" sends data securely (not visible in URL) -->
        <form method="post" action="login.php?redirect=<?= urlencode($redirect) ?>" class="validated">

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
                <label for="password">Password</label>
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
                Sign In →
            </button>
        </form>

        <!-- Link to register page -->
        <div class="form-footer">
            Don't have an account?
            <a href="register.php">Create one free</a>
        </div>

        <!-- Demo credentials hint -->
        <div class="alert alert-info" style="margin-top:1.2rem;font-size:.82rem;">
            💡 <strong>Demo admin:</strong> admin@driveease.com / password
        </div>
    </div>
</div>

<script src="<?= $basePath ?>js/main.js"></script>
</body>
</html>
