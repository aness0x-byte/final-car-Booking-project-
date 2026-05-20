<?php

require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my-bookings.php');
    exit;
}

$pageTitle = 'My Bookings';
$basePath  = '';
$userId    = (int)$_SESSION['user_id'];

$db = getDB();

// ── FETCH USER'S BOOKINGS ────────────────────────────────
$stmt = $db->prepare("
    SELECT
        b.id,
        b.pickup_date,
        b.return_date,
        b.total_price,
        b.status,
        b.created_at,
        c.make   AS car_make,
        c.model  AS car_model,
        c.image_url,
        DATEDIFF(b.return_date, b.pickup_date) AS days
    FROM reservations b
    JOIN cars c ON b.car_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Page header -->
<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="index.php">← Browse Cars</a></div>
        <h1>My Bookings</h1>
        <p>Hello, <?= htmlspecialchars($_SESSION['user_name']) ?>! Here are all your reservations.</p>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container">

        <?php if (empty($bookings)): ?>
            <!-- No bookings yet -->
            <div class="success-box" style="padding:3rem 2rem; background: #f9fafb; text-align: center; border-radius: 8px;">
                <h2 style="color:var(--dark);">No bookings yet!</h2>
                <p style="margin-bottom: 1.5rem;">You haven't made any reservations. Start browsing our cars!</p>
                <a href="index.php" class="btn btn-primary">Browse Cars</a>
            </div>

        <?php else: ?>
            <!-- Summary bar -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
                <span style="font-size:.95rem;color:var(--gray-mid);">
                    <?= count($bookings) ?> booking<?= count($bookings) !== 1 ? 's' : '' ?> found
                </span>
                <a href="index.php" class="btn btn-primary">+ Book Another Car</a>
            </div>

            <!-- Desktop table view -->
            <div class="table-card" style="overflow-x:auto; background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid #eee; text-align: left;">
                            <th style="padding: 1rem;">Car</th>
                            <th style="padding: 1rem;">Pickup Date</th>
                            <th style="padding: 1rem;">Return Date</th>
                            <th style="padding: 1rem;">Duration</th>
                            <th style="padding: 1rem;">Total Price</th>
                            <th style="padding: 1rem;">Status</th>
                            <th style="padding: 1rem;">Booked On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <!-- Car info with thumbnail -->
                                <td style="padding: 1rem;">
                                    <div style="display:flex;align-items:center;gap:15px;">
                                        <?php if ($b['image_url']): ?>
                                            <img
                                                src="<?= htmlspecialchars($b['image_url']) ?>"
                                                alt="<?= htmlspecialchars($b['car_make']) ?>"
                                                style="width:80px;height:50px;object-fit:cover;border-radius:6px;flex-shrink:0;"
                                            >
                                        <?php else: ?>
                                            <div style="width:80px;height:50px;background:#e5e7eb;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:0.75rem;">No Image</div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight:600;color:var(--dark); font-size: 1.1rem;">
                                                <?= htmlspecialchars($b['car_make'] . ' ' . $b['car_model']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td style="padding: 1rem;"><?= date('M d, Y', strtotime($b['pickup_date'])) ?></td>
                                <td style="padding: 1rem;"><?= date('M d, Y', strtotime($b['return_date'])) ?></td>
                                <td style="padding: 1rem;"><?= (int)$b['days'] ?> day<?= $b['days'] > 1 ? 's' : '' ?></td>
                                <td style="padding: 1rem;"><strong style="color:var(--primary-color);"><?= number_format($b['total_price'], 0, '.', ' ') ?> DZD</strong></td>

                                <td style="padding: 1rem;">
                                    <?php
                                    $badgeColor = '#f59e0b'; // pending warning
                                    $badgeBg = '#fef3c7';
                                    if ($b['status'] === 'approved' || $b['status'] === 'completed') {
                                        $badgeColor = '#10b981'; // success
                                        $badgeBg = '#d1fae5';
                                    } elseif ($b['status'] === 'rejected' || $b['status'] === 'cancelled') {
                                        $badgeColor = '#ef4444'; // danger
                                        $badgeBg = '#fee2e2';
                                    }
                                    ?>
                                    <span style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.85rem; font-weight: bold;">
                                        <?= ucfirst(htmlspecialchars($b['status'])) ?>
                                    </span>
                                </td>

                                <td style="padding: 1rem; color:var(--gray-mid);font-size:.85rem;">
                                    <?= date('M d, Y', strtotime($b['created_at'])) ?>
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
