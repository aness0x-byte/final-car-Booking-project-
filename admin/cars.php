<?php
require_once '../config/db.php';
$db = getDB();

$pageTitle = 'Manage Cars';
$error = '';
$success = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $make = trim($_POST['make'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $model_year = (int)($_POST['model_year'] ?? date('Y'));
        $number_of_seats = (int)($_POST['number_of_seats'] ?? 4);
        $category = trim($_POST['category'] ?? '');
        $fuel_type = trim($_POST['fuel_type'] ?? '');
        $transmission_type = trim($_POST['transmission_type'] ?? '');
        $daily_rate = (float)($_POST['daily_rate'] ?? 0);
        $booking_status = trim($_POST['booking_status'] ?? 'available');
        $image_url = trim($_POST['image_url'] ?? '');

        if (empty($make) || empty($model) || $daily_rate <= 0) {
            $error = "Make, Model, and a valid Daily Rate are required.";
        } else {
            if ($action === 'add') {
                $stmt = $db->prepare("
                    INSERT INTO cars (make, model, model_year, number_of_seats, category, fuel_type, transmission_type, daily_rate, booking_status, image_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$make, $model, $model_year, $number_of_seats, $category, $fuel_type, $transmission_type, $daily_rate, $booking_status, $image_url]);
                $success = "Car added successfully!";
            } else { // edit
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("
                    UPDATE cars SET 
                    make = ?, model = ?, model_year = ?, number_of_seats = ?, category = ?, 
                    fuel_type = ?, transmission_type = ?, daily_rate = ?, booking_status = ?, image_url = ?
                    WHERE id = ?
                ");
                $stmt->execute([$make, $model, $model_year, $number_of_seats, $category, $fuel_type, $transmission_type, $daily_rate, $booking_status, $image_url, $id]);
                $success = "Car updated successfully!";
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $db->prepare("DELETE FROM cars WHERE id = ?")->execute([$id]);
        $success = "Car deleted successfully!";
    }
}

// Fetch all cars
$cars = $db->query("SELECT * FROM cars ORDER BY id DESC")->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="admin-header">
    <h1>Manage Cars</h1>
    <button class="btn btn-primary" onclick="showForm('add')">➕ Add New Car</button>
</div>

<?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom: 1rem;">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom: 1rem; background: #dcfce7; color: #166534; padding: 1rem; border-radius: 4px;">✅ <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Car Form (Hidden by default) -->
<div id="car-form-container" class="admin-card" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0;">
    <h2 id="form-title" style="margin-bottom: 1.5rem;">Add New Car</h2>
    <form method="POST" action="cars.php">
        <input type="hidden" name="action" id="form-action" value="add">
        <input type="hidden" name="id" id="car-id" value="">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label>Make</label>
                <input type="text" name="make" id="make" required placeholder="e.g., Toyota">
            </div>
            <div class="form-group">
                <label>Model</label>
                <input type="text" name="model" id="model" required placeholder="e.g., Camry">
            </div>
            <div class="form-group">
                <label>Year</label>
                <input type="number" name="model_year" id="model_year" required value="<?= date('Y') ?>">
            </div>
            <div class="form-group">
                <label>Seats</label>
                <input type="number" name="number_of_seats" id="number_of_seats" required value="4">
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" id="category" required placeholder="e.g., Sedan, SUV">
            </div>
            <div class="form-group">
                <label>Fuel Type</label>
                <select name="fuel_type" id="fuel_type">
                    <option value="Gas">Gas</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Electric">Electric</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
            <div class="form-group">
                <label>Transmission</label>
                <select name="transmission_type" id="transmission_type">
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>
            </div>
            <div class="form-group">
                <label>Daily Rate (DZD)</label>
                <input type="number" step="0.01" name="daily_rate" id="daily_rate" required placeholder="5000">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="booking_status" id="booking_status">
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="rented">Rented</option>
                </select>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="text" name="image_url" id="image_url" placeholder="https://...">
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">Save Car</button>
            <button type="button" class="btn btn-secondary" onclick="hideForm()">Cancel</button>
        </div>
    </form>
</div>

<!-- Cars List -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Make & Model</th>
                    <th>Year/Seats</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cars)): ?>
                    <tr><td colspan="7" style="text-align: center;">No cars available.</td></tr>
                <?php else: ?>
                    <?php foreach ($cars as $car): ?>
                        <tr>
                            <td><?= $car['id'] ?></td>
                            <td>
                                <?php if ($car['image_url']): ?>
                                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="car" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <span style="color: #aaa;">No image</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($car['make']) ?></strong><br>
                                <span style="font-size: 0.85rem; color: var(--gray-mid);"><?= htmlspecialchars($car['model']) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($car['model_year']) ?><br>
                                <span style="font-size: 0.85rem; color: var(--gray-mid);"><?= htmlspecialchars($car['number_of_seats']) ?> seats</span>
                            </td>
                            <td><?= number_format($car['daily_rate'], 0, '.', ' ') ?> DZD</td>
                            <td>
                                <?php
                                $statusColor = $car['booking_status'] === 'available' ? 'badge-success' : ($car['booking_status'] === 'reserved' ? 'badge-warning' : 'badge-danger');
                                ?>
                                <span class="badge <?= $statusColor ?>"><?= ucfirst($car['booking_status']) ?></span>
                            </td>
                            <td>
                                <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" 
                                    onclick='editCar(<?= json_encode($car) ?>)'>Edit</button>
                                
                                <form method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this car?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $car['id'] ?>">
                                    <button type="submit" class="btn" style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showForm(action) {
    document.getElementById('car-form-container').style.display = 'block';
    document.getElementById('form-action').value = action;
    if (action === 'add') {
        document.getElementById('form-title').innerText = 'Add New Car';
        document.getElementById('car-id').value = '';
        // Reset inputs (except those with defaults)
        document.getElementById('make').value = '';
        document.getElementById('model').value = '';
        document.getElementById('category').value = '';
        document.getElementById('daily_rate').value = '';
        document.getElementById('image_url').value = '';
    }
    // Scroll to form
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function hideForm() {
    document.getElementById('car-form-container').style.display = 'none';
}

function editCar(car) {
    showForm('edit');
    document.getElementById('form-title').innerText = 'Edit Car: ' + car.make + ' ' + car.model;
    document.getElementById('car-id').value = car.id;
    document.getElementById('make').value = car.make;
    document.getElementById('model').value = car.model;
    document.getElementById('model_year').value = car.model_year;
    document.getElementById('number_of_seats').value = car.number_of_seats;
    document.getElementById('category').value = car.category;
    document.getElementById('fuel_type').value = car.fuel_type;
    document.getElementById('transmission_type').value = car.transmission_type;
    document.getElementById('daily_rate').value = car.daily_rate;
    document.getElementById('booking_status').value = car.booking_status;
    document.getElementById('image_url').value = car.image_url;
}
</script>

<?php require_once 'includes/admin_footer.php'; ?>
