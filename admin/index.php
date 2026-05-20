<?php
require_once '../config/db.php';

$pageTitle = 'Overview';
require_once 'includes/admin_header.php';

$db = getDB();

// Fetch statistics
$carsCount = $db->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$reservationsCount = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$pendingCount = $db->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();

// Fetch recent reservations
$recentReservations = $db->query("
    SELECT r.id, r.status, r.total_price, u.full_name as user_name, c.make, c.model, r.created_at 
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<div class="admin-header">
    <h1>Dashboard Overview</h1>
    <div>
        <span style="color: var(--gray-mid);">Welcome back, Admin!</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="admin-card" style="border-left: 4px solid var(--primary-color);">
        <h3 style="color: var(--gray-mid); font-size: 1rem; margin-bottom: 0.5rem;">Total Cars</h3>
        <div style="font-size: 2rem; font-weight: bold;"><?= $carsCount ?></div>
    </div>
    <div class="admin-card" style="border-left: 4px solid #10b981;">
        <h3 style="color: var(--gray-mid); font-size: 1rem; margin-bottom: 0.5rem;">Total Users</h3>
        <div style="font-size: 2rem; font-weight: bold;"><?= $usersCount ?></div>
    </div>
    <div class="admin-card" style="border-left: 4px solid #3b82f6;">
        <h3 style="color: var(--gray-mid); font-size: 1rem; margin-bottom: 0.5rem;">Total Reservations</h3>
        <div style="font-size: 2rem; font-weight: bold;"><?= $reservationsCount ?></div>
    </div>
    <div class="admin-card" style="border-left: 4px solid #f59e0b;">
        <h3 style="color: var(--gray-mid); font-size: 1rem; margin-bottom: 0.5rem;">Pending Approvals</h3>
        <div style="font-size: 2rem; font-weight: bold;"><?= $pendingCount ?></div>
    </div>
</div>

<div class="admin-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Recent Reservations</h2>
        <a href="reservations.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">View All</a>
    </div>
    
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Car</th>
                    <th>Total Price</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentReservations)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--gray-mid);">No reservations found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentReservations as $res): ?>
                        <tr>
                            <td>#<?= $res['id'] ?></td>
                            <td><?= htmlspecialchars($res['user_name']) ?></td>
                            <td><?= htmlspecialchars($res['make'] . ' ' . $res['model']) ?></td>
                            <td><?= number_format($res['total_price'], 0, '.', ' ') ?> DZD</td>
                            <td><?= date('M j, Y', strtotime($res['created_at'])) ?></td>
                            <td>
                                <?php
                                $badgeClass = 'badge-warning'; // pending
                                if ($res['status'] === 'approved' || $res['status'] === 'completed') $badgeClass = 'badge-success';
                                if ($res['status'] === 'rejected' || $res['status'] === 'cancelled') $badgeClass = 'badge-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($res['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
