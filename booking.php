<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── ACCESS CONTROL — must be logged in ───────────────────
if (!isset($_SESSION['user_id'])) {
    $carId = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
    header('Location: login.php?redirect=' . urlencode('booking.php?car_id=' . $carId));
    exit;
}

$pageTitle = 'Book a Car';
$basePath  = '';
$userId    = (int)$_SESSION['user_id'];

$carId = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

if ($carId < 1) {
    header('Location: index.php');
    exit;
}

$db = getDB();

// Fetch car details
$stmt = $db->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: index.php?error=car_not_found');
    exit;
}

$error   = '';
$success = '';

// ── HANDLE FORM SUBMISSION ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickupDate  = trim($_POST['pickup_date']  ?? '');
    $returnDate  = trim($_POST['return_date']  ?? '');

    if (empty($pickupDate) || empty($returnDate)) {
        $error = 'Please select both pickup and return dates.';
    } elseif (strtotime($returnDate) <= strtotime($pickupDate)) {
        $error = 'Return date must be after the pickup date.';
    } elseif (strtotime($pickupDate) < strtotime('today')) {
        $error = 'Pickup date cannot be in the past.';
    } else {
        // Calculate price securely on the server
        $days = (int)ceil((strtotime($returnDate) - strtotime($pickupDate)) / 86400);
        $totalPrice = $days * (float)$car['daily_rate'];
        
        if ($totalPrice <= 0) {
            $error = 'Invalid price. Please select valid dates.';
        } else {
        // ── CHECK AVAILABILITY ──
        // Check if there are any overlapping reservations that are not rejected or cancelled
        $checkStmt = $db->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE car_id = ? 
            AND status IN ('pending', 'approved', 'completed')
            AND (
                (pickup_date <= ? AND return_date >= ?) OR
                (pickup_date <= ? AND return_date >= ?) OR
                (pickup_date >= ? AND return_date <= ?)
            )
        ");
        $checkStmt->execute([$carId, $pickupDate, $pickupDate, $returnDate, $returnDate, $pickupDate, $returnDate]);
        $overlaps = $checkStmt->fetchColumn();

        if ($overlaps > 0) {
            $error = 'This car is already booked for the selected dates. Please choose different dates.';
        } else {
            // ✅ Available — save booking
            // Default payment method is cash_on_delivery in DB
            $stmt = $db->prepare("
                INSERT INTO reservations (user_id, car_id, pickup_date, return_date, total_price, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$userId, $carId, $pickupDate, $returnDate, $totalPrice]);
            
            $success = true;
            $days    = (int)((strtotime($returnDate) - strtotime($pickupDate)) / 86400);
        }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">← Back to Cars</a>
        </div>
        <h1>Book Your Car</h1>
        <p>Complete your reservation for <?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></p>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container">
        <?php if ($success): ?>
            <div class="success-box" style="text-align: center; padding: 3rem; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <div class="success-icon" style="font-size: 3rem; margin-bottom: 1rem; color: #10b981;">Success</div>
                <h2>Booking Request Submitted!</h2>
                <p>
                    Your reservation for <strong><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></strong>
                    for <strong><?= $days ?> day<?= $days > 1 ? 's' : '' ?></strong> is pending approval.
                    <br>Total: <strong style="color:var(--primary-color);"><?= number_format($totalPrice, 0, '.', ' ') ?> DZD</strong>
                </p>
                <p style="margin-top: 1rem; color: var(--gray-mid);">Payment Method: Cash on Delivery</p>
                
                <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin-top: 2rem;">
                    <a href="my-bookings.php" class="btn btn-primary">View My Bookings</a>
                    <a href="index.php" class="btn btn-outline" style="border: 1px solid #ccc; padding: 0.75rem 1.5rem; text-decoration: none; color: #333; border-radius: 4px;">Browse More Cars</a>
                </div>
            </div>
        <?php else: ?>
            <div class="booking-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Booking Form -->
                <div>
                    <?php if ($error): ?>
                        <div class="alert alert-error" style="margin-bottom: 1rem;">Error: <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <div class="form-card" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h2 class="form-title" style="margin-bottom: 0.5rem;">Reservation Details</h2>
                        <p class="form-subtitle" style="color: var(--gray-mid); margin-bottom: 1.5rem;">Select your pickup and return dates</p>

                        <form method="post" action="booking.php?car_id=<?= $carId ?>" id="bookingForm">
                            <input type="hidden" name="total_price" id="total_price" value="0">

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group">
                                    <label for="pickup_date">Pickup Date</label>
                                    <input type="date" id="pickup_date" name="pickup_date" required value="<?= htmlspecialchars($_POST['pickup_date'] ?? '') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px;">
                                </div>
                                <div class="form-group">
                                    <label for="return_date">Return Date</label>
                                    <input type="date" id="return_date" name="return_date" required value="<?= htmlspecialchars($_POST['return_date'] ?? '') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Payment Method</label>
                                <input type="text" value="Cash on Delivery" readonly style="width: 100%; padding: 0.75rem; background: #f9fafb; border: 1px solid #eee; border-radius: 4px; color: #555;">
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label>Renter</label>
                                <input type="text" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" readonly style="width: 100%; padding: 0.75rem; background: #f9fafb; border: 1px solid #eee; border-radius: 4px; color: #555;">
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 4px; border: none; cursor: pointer;">
                                Submit Booking Request
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Car preview + Price breakdown -->
                <div>
                    <div class="booking-car-preview" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <?php if ($car['image_url']): ?>
                            <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['make']) ?>" style="width: 100%; border-radius: 8px; margin-bottom: 1rem;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af; border-radius: 8px; margin-bottom: 1rem;">No Image</div>
                        <?php endif; ?>
                        
                        <div>
                            <div style="font-size: 1.5rem; font-weight: bold; margin-bottom: 0.25rem;"><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></div>
                            <div style="color: var(--gray-mid); margin-bottom: 1.5rem;"><?= htmlspecialchars($car['category']) ?> • <?= htmlspecialchars($car['model_year']) ?></div>

                            <div style="border-top: 1px solid #eee; padding-top: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Daily rate</span>
                                    <strong><?= number_format($car['daily_rate'], 0, '.', ' ') ?> DZD</strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span>Duration</span>
                                    <strong id="daysDisplay">— </strong>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee; font-size: 1.25rem; font-weight: bold;">
                                    <span>Total</span>
                                    <span id="totalDisplay" style="color: var(--primary-color);">Select dates</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pickupDate = document.getElementById('pickup_date');
    const returnDate = document.getElementById('return_date');
    const totalDisplay = document.getElementById('totalDisplay');
    const daysDisplay = document.getElementById('daysDisplay');
    const priceInput = document.getElementById('total_price');
    const dailyRate = <?= (float)$car['daily_rate'] ?>;

    function updatePrice() {
        if (pickupDate.value && returnDate.value) {
            const start = new Date(pickupDate.value);
            const end = new Date(returnDate.value);
            const timeDiff = end.getTime() - start.getTime();
            
            if (timeDiff > 0) {
                const days = Math.ceil(timeDiff / (1000 * 3600 * 24));
                const total = days * dailyRate;
                
                daysDisplay.innerText = days + (days === 1 ? ' day' : ' days');
                totalDisplay.innerText = total.toFixed(0) + ' DZD';
                priceInput.value = total.toFixed(2);
            } else {
                daysDisplay.innerText = '—';
                totalDisplay.innerText = 'Invalid dates';
                priceInput.value = 0;
            }
        }
    }

    pickupDate.addEventListener('change', updatePrice);
    returnDate.addEventListener('change', updatePrice);
    
    // Run once on load if values exist
    updatePrice();
});
</script>

<?php require_once 'includes/footer.php'; ?>
