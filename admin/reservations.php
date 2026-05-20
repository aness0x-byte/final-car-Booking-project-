<?php
require_once '../config/db.php';
$db = getDB();

$pageTitle = 'Manage Reservations';
$error = '';
$success = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($action === 'update_status' && $id && in_array($status, ['pending', 'approved', 'rejected', 'completed', 'cancelled'])) {
        $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $success = "Reservation status updated to " . ucfirst($status) . ".";
        
        // If approved, maybe set car to reserved. If completed/cancelled/rejected, set car to available.
        // Get the car_id for this reservation
        $res = $db->prepare("SELECT car_id FROM reservations WHERE id = ?");
        $res->execute([$id]);
        $car = $res->fetch();
        
        if ($car) {
            if ($status === 'approved') {
                $db->prepare("UPDATE cars SET booking_status = 'reserved' WHERE id = ?")->execute([$car['car_id']]);
            } elseif (in_array($status, ['completed', 'cancelled', 'rejected'])) {
                $db->prepare("UPDATE cars SET booking_status = 'available' WHERE id = ?")->execute([$car['car_id']]);
            }
        }
    }
}

// Fetch all reservations
$reservations = $db->query("
    SELECT r.*, u.full_name as user_name, u.email as user_email, u.phone_number, 
           c.make, c.model, c.daily_rate 
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    ORDER BY r.created_at DESC
")->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="admin-header">
    <h1>Manage Reservations</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom: 1rem;">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom: 1rem; background: #dcfce7; color: #166534; padding: 1rem; border-radius: 4px;">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID / Date</th>
                    <th>Customer</th>
                    <th>Car / Dates</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr><td colspan="6" style="text-align: center;">No reservations found.</td></tr>
                <?php else: ?>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td>
                                <strong>#<?= $res['id'] ?></strong><br>
                                <span style="font-size: 0.85rem; color: var(--gray-mid);"><?= date('M j, Y', strtotime($res['created_at'])) ?></span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($res['user_name']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: var(--gray-mid);"><?= htmlspecialchars($res['user_email']) ?><br><?= htmlspecialchars($res['phone_number']) ?></span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($res['make'] . ' ' . $res['model']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: var(--gray-mid);">
                                    <?= date('M j', strtotime($res['pickup_date'])) ?> — <?= date('M j, Y', strtotime($res['return_date'])) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= number_format($res['total_price'], 0, '.', ' ') ?> DZD</strong><br>
                                <span style="font-size: 0.85rem; color: var(--gray-mid);"><?= htmlspecialchars(str_replace('_', ' ', $res['payment_method'])) ?></span>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'badge-warning';
                                if ($res['status'] === 'approved' || $res['status'] === 'completed') $badgeClass = 'badge-success';
                                if ($res['status'] === 'rejected' || $res['status'] === 'cancelled') $badgeClass = 'badge-danger';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= ucfirst($res['status']) ?></span>
                            </td>
                            <td>
                                <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                    <select name="status" class="form-control" style="padding: 0.25rem; font-size: 0.85rem;">
                                        <option value="pending" <?= $res['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="approved" <?= $res['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="rejected" <?= $res['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                        <option value="completed" <?= $res['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $res['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
