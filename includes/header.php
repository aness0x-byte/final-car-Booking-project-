<?php
/* --------------------------------------------------------------------------
   HEADER FILE (includes/header.php) - WITH GZIP COMPRESSION
   --------------------------------------------------------------------------
   This file is included at the top of every page.
   ✅ OPTIMIZATION #5: Added gzip compression and caching headers
   -------------------------------------------------------------------------- */

// ✅ OPTIMIZATION #5: Enable gzip compression for all responses
if (!ob_get_level()) {
    ob_start('ob_gzip_handler');
}

// ✅ Set caching headers to reduce bandwidth
header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
header('Content-Type: text/html; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName  = $_SESSION['user_name'] ?? '';
$pageTitle = ($pageTitle ?? 'DriveEase') . ' — DriveEase Car Rental';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DriveEase — Simple car rental service. Book cars easily.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Main stylesheet -->
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>css/style.css">
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <div class="navbar-inner">

            <a href="<?= $basePath ?? '' ?>index.php" class="navbar-logo">
                <div class="logo-icon">D</div>
                DriveEase
            </a>

            <ul class="navbar-links" id="navLinks">
                <li><a href="<?= $basePath ?? '' ?>index.php">Cars</a></li>
                <li><a href="<?= $basePath ?? '' ?>reviews.php">Reviews</a></li>
                <li><a href="<?= $basePath ?? '' ?>contact.php">Contact</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= $basePath ?? '' ?>my-bookings.php">My Bookings</a></li>
                    <li>
                        <span class="navbar-user">
                            <?= htmlspecialchars($userName) ?>
                        </span>
                    </li>
                    <li><a href="<?= $basePath ?? '' ?>logout.php" class="btn btn-outline btn-sm">Sign Out</a></li>
                <?php else: ?>
                    <li><a href="<?= $basePath ?? '' ?>login.php">Sign In</a></li>
                    <li><a href="<?= $basePath ?? '' ?>register.php" class="btn-nav-primary">Register</a></li>
                <?php endif; ?>
            </ul>

            <button class="navbar-toggle" id="navToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</nav>
