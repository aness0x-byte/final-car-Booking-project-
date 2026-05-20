<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$pageTitle = $pageTitle ?? 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — DriveEase Admin</title>
    <!-- We'll use the main stylesheet and maybe add some admin-specific styles -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 250px; background: var(--dark-bg); color: #fff; padding: 2rem 1rem; }
        .admin-sidebar a { color: #ccc; text-decoration: none; display: block; padding: 0.8rem 1rem; border-radius: 4px; margin-bottom: 0.5rem; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: var(--primary-color); color: #fff; }
        .admin-content { flex: 1; padding: 2rem; background: #f4f7f6; }
        .admin-card { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .table-responsive { overflow-x: auto; }
        table.admin-table { width: 100%; border-collapse: collapse; }
        table.admin-table th, table.admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        table.admin-table th { background: #f9fafb; font-weight: 600; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 500; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef08a; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <h2 style="margin-bottom: 2rem; padding-left: 1rem; font-size: 1.5rem; color: #fff;">
            🚗 DriveEase Admin
        </h2>
        <nav>
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📊 Dashboard Overview</a>
            <a href="cars.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cars.php' ? 'active' : '' ?>">🚘 Manage Cars</a>
            <a href="reservations.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : '' ?>">📅 Reservations</a>
            <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">👥 Manage Users</a>
            <a href="../index.php" style="margin-top: 2rem; background: rgba(255,255,255,0.1);">🔙 Back to Site</a>
            <a href="../logout.php">🚪 Sign Out</a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-content">
