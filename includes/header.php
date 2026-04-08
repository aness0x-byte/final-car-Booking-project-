<?php
/* --------------------------------------------------------------------------
   HEADER FILE (includes/header.php)
   --------------------------------------------------------------------------
   This file is included at the top of every page. 
   It handles:
     1. Starting the session (to keep track of logged-in users).
     2. Setting default variables like $userName and $isLoggedIn.
     3. Displaying the navigation bar (navbar) at the top.
   -------------------------------------------------------------------------- */

// 1. Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Define user variables from the session
// If user_id is in the session, it means someone is logged in
$isLoggedIn = isset($_SESSION['user_id']); 
$userName   = $_SESSION['user_name'] ?? 'Guest';

// 3. Set the page title (use a default if not set in the page)
$pageTitle = ($pageTitle ?? 'DriveEase') . ' — DriveEase Car Rental';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DriveEase — Simple car rental service. Book cars easily.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Link to our CSS file (controls how the page looks) -->
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>css/style.css">
    
    <!-- A small icon for the browser tab -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>

<!-- ── NAVBAR — The top menu bar ────────────────── -->
<nav class="navbar">
    <div class="container">
        <div class="navbar-inner">

            <!-- Logo (links back to homepage) -->
            <a href="<?= $basePath ?? '' ?>index.php" class="navbar-logo">
                <div class="logo-icon">D</div>
                DriveEase
            </a>

            <!-- Navigation Links -->
            <ul class="navbar-links" id="navLinks">
                <li><a href="<?= $basePath ?? '' ?>index.php">🚗 Cars</a></li>

                <?php if ($isLoggedIn): ?>
                    <!-- If logged in: show My Bookings and Logout -->
                    <li><a href="<?= $basePath ?? '' ?>my-bookings.php">📋 My Bookings</a></li>
                    <li>
                        <span class="navbar-user">
                            👤 <?= htmlspecialchars($userName) ?>
                        </span>
                    </li>
                    <li><a href="<?= $basePath ?? '' ?>logout.php">Sign Out</a></li>
                <?php else: ?>
                    <!-- If NOT logged in: show Sign In and Register -->
                    <li><a href="<?= $basePath ?? '' ?>login.php">Sign In</a></li>
                    <li><a href="<?= $basePath ?? '' ?>register.php" class="btn-nav-primary">Register</a></li>
                <?php endif; ?>
            </ul>

            <!-- Mobile menu button (hamburger icon) -->
            <button class="navbar-toggle" id="navToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</nav>
