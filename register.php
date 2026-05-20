<?php
// ============================================================
// REGISTER PAGE — register.php
// ============================================================
// GET  request: Show the registration form
// POST request: Validate, hash password, insert user, redirect
//
// Security:
//   - password_hash() with PASSWORD_DEFAULT (bcrypt) — NEVER store plain passwords
//   - Prepared statements — prevent SQL injection
//   - htmlspecialchars() — prevent XSS
//   - Email uniqueness checked before insert
// ============================================================

require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Already logged in? Go home.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Create Account';
$basePath  = '';
$error     = '';
$success   = '';

// ── HANDLE FORM SUBMISSION ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    // ── Validation ──
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, email and password are required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';

    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';

    } else {
        $db = getDB();

        // Check if email is already registered
        $check = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'This email is already registered. Please sign in.';

        } else {
            // ✅ All good — hash the password and insert the user
            // password_hash() uses bcrypt automatically — safe and irreversible
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO users (full_name, email, phone_number, password_hash)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $phone, $hashedPassword]);

            // Redirect to login with a success message
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — DriveEase</title>
    <link rel="stylesheet" href="<?= $basePath ?>css/style.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>

<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">

        <!-- Logo -->
        <a href="index.php" class="auth-logo">
            <div class="logo-icon">D</div>
            DriveEase
        </a>

        <div class="form-title" style="text-align:center;">Create your account</div>
        <div class="form-subtitle" style="text-align:center;margin-bottom:1.5rem;">
            Join DriveEase and start booking premium cars
        </div>

        <!-- Error message -->
        <?php if ($error): ?>
            <div class="alert alert-error">
                Error: <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form method="post" action="register.php" class="validated">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="John Doe"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    required
                    autocomplete="name"
                >
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
                <label for="phone">Phone Number <span style="color:var(--gray-mid);font-weight:400;">(optional)</span></label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="+1 234 567 8900"
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                    autocomplete="tel"
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Min. 6 characters"
                        required
                        autocomplete="new-password"
                    >
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        placeholder="Repeat password"
                        required
                        autocomplete="new-password"
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">
                Create Account
            </button>
        </form>

        <div class="form-footer">
            Already have an account?
            <a href="login.php">Sign in</a>
        </div>
    </div>
</div>

<script src="<?= $basePath ?>js/main.js"></script>
</body>
</html>
