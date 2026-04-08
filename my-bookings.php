<?php
/* --------------------------------------------------------------------------
   MY BOOKINGS PAGE (my-bookings.php)
   --------------------------------------------------------------------------
   This page shows a list of all reservations made by the logged-in user.
   It uses a SQL 'JOIN' to get car names and images from the 'cars' table.
   -------------------------------------------------------------------------- */

require_once 'config/db.php';

// Start the session (needed to know which user is logged in)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 1. ACCESS CONTROL — User must be logged in ───────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my-bookings.php');
    exit;
}

$pageTitle = 'My Bookings';
$basePath  = '';
$userId    = (int)$_SESSION['user_id'];

$db = getDB();

// ── 2. FETCH BOOKINGS FROM DATABASE ──────────────────────────────
// We use 'JOIN' to connect the 'bookings' table with the 'cars' table.
// This allows us to see the car's name and image for each booking.
$stmt = $db->prepare("
    SELECT
        b.id,
        b.pickup_date,
        b.return_date,
        b.total_price,
        b.status,
        c.name AS car_name,
        c.brand AS car_brand,
        c.image_url
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.id DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

// Include the header
require_once 'includes/header.php';
?>

<!-- ── PAGE HEADER ─────────────────────────────────────────── -->
<div class="page-header">
    <div class="container">
        <h1>My Reservations</h1>
        <p>Hello <?= htmlspecialchars($_SESSION['user_name']) ?>, here are your bookings.</p>
    </div>
</div>

<section class="section">
    <div class="container">

        <?php if (empty($bookings)): ?>
            <!-- SHOW THIS IF THE USER HAS NO BOOKINGS YET -->
            <div style="text-align:center; padding:50px; background:white; border-radius:8px; border:1px solid #ddd;">
                <h2 style="color:#777;">You have no bookings yet.</h2>
                <a href="index.php" class="btn-book" style="display:inline-block; margin-top:20px;">Browse Cars</a>
            </div>

        <?php else: ?>
            <!-- SHOW THE TABLE OF BOOKINGS -->
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:white; border-radius:8px; overflow:hidden; border:1px solid #ddd;">
                    <thead style="background:var(--dark); color:white; text-align:left;">
                        <tr>
                            <th style="padding:15px;">Car</th>
                            <th style="padding:15px;">Dates</th>
                            <th style="padding:15px;">Total Price</th>
                            <th style="padding:15px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr style="border-bottom:1px solid #eee;">
                                <!-- Car info -->
                                <td style="padding:15px;">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <img src="<?= htmlspecialchars($b['image_url']) ?>" style="width:60px; height:40px; object-fit:cover; border-radius:4px;">
                                        <strong><?= htmlspecialchars($b['car_name']) ?></strong>
                                    </div>
                                </td>
                                <!-- Pickup and Return Dates -->
                                <td style="padding:15px;">
                                    <small style="color:#777;">From:</small> <?= $b['pickup_date'] ?><br>
                                    <small style="color:#777;">To:</small> <?= $b['return_date'] ?>
                                </td>
                                <!-- Total Price (in DZD) -->
                                <td style="padding:15px; font-weight:bold; color:var(--orange);">
                                    <?= number_format($b['total_price'], 0, '.', ' ') ?> DZD
                                </td>
                                <!-- Booking Status -->
                                <td style="padding:15px;">
                                    <span style="background:#dcfce7; color:#166534; padding:3px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold;">
                                        <?= strtoupper($b['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
