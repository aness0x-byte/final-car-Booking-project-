<?php
require_once '../config/db.php';
$db = getDB();

$pageTitle = 'Manage Users';
$error = '';
$success = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'change_role' && $id) {
        $role = $_POST['role'] ?? 'customer';
        if (in_array($role, ['admin', 'customer'])) {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $id]);
            $success = "User role updated successfully!";
        }
    } elseif ($action === 'delete' && $id) {
        // Ensure we don't delete ourselves
        if ($id === $_SESSION['user_id']) {
            $error = "You cannot delete your own account.";
        } else {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $success = "User deleted successfully!";
        }
    }
}

// Fetch all users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="admin-header">
    <h1>Manage Users</h1>
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
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Role</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" style="text-align: center;">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><strong><?= htmlspecialchars($user['full_name']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($user['email']) ?><br>
                                <span style="font-size: 0.85rem; color: var(--gray-mid);"><?= htmlspecialchars($user['phone_number']) ?></span>
                            </td>
                            <td>
                                <?php
                                $roleBadge = $user['role'] === 'admin' ? 'badge-danger' : 'badge-success';
                                ?>
                                <span class="badge <?= $roleBadge ?>"><?= ucfirst($user['role']) ?></span>
                            </td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display: inline-block;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <select name="role" onchange="this.form.submit()" class="form-control" style="padding: 0.2rem; font-size: 0.8rem;">
                                        <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </form>
                                
                                <form method="POST" style="display: inline-block; margin-left: 0.5rem;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="btn" style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; font-size: 0.8rem;" <?= $user['id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>Delete</button>
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
