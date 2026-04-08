<?php
/* --------------------------------------------------------------------------
   BOOKING PAGE (booking.php)
   --------------------------------------------------------------------------
   This page allows users to reserve a car.
   It calculates the total price based on the selected dates.
   -------------------------------------------------------------------------- */

// Connect to the database
require_once 'config/db.php';

// Start the session (needed to check if user is logged in)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 1. ACCESS CONTROL — User must be logged in ───────────────────
if (!isset($_SESSION['user_id'])) {
    // If not logged in, remember which car they wanted and go to login page
    $carId = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;
    header('Location: login.php?redirect=' . urlencode('booking.php?car_id=' . $carId));
    exit;
}

$pageTitle = 'Book a Car';
$basePath  = '';
$userId    = (int)$_SESSION['user_id'];

// ── 2. GET CAR INFO FROM DATABASE ─────────────────────────────────
// The car ID is passed in the URL (e.g., booking.php?car_id=1)
$carId = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

if ($carId < 1) {
    header('Location: index.php'); // No ID? Go back home
    exit;
}

$db = getDB();

// Fetch car details from the 'cars' table
$stmt = $db->prepare("SELECT * FROM cars WHERE id = ? AND available = 1 LIMIT 1");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) {
    header('Location: index.php?error=car_not_found'); // Car not found? Go back home
    exit;
}

$error   = '';
$success = '';

// ── 3. HANDLE FORM SUBMISSION (Confirm Booking) ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pickupDate  = trim($_POST['pickup_date']  ?? '');
    $returnDate  = trim($_POST['return_date']  ?? '');
    $totalPrice  = (float)($_POST['total_price'] ?? 0);

    // Simple validation checks
    if (empty($pickupDate) || empty($returnDate)) {
        $error = 'Please select both pickup and return dates.';
    } elseif (strtotime($returnDate) <= strtotime($pickupDate)) {
        $error = 'Return date must be after the pickup date.';
    } elseif (strtotime($pickupDate) < strtotime('today')) {
        $error = 'Pickup date cannot be in the past.';
    } elseif ($totalPrice <= 0) {
        $error = 'Invalid price. Please select valid dates.';
    } else {
        // ✅ Success: Save the booking to the 'bookings' table
        $stmt = $db->prepare("
            INSERT INTO bookings (user_id, car_id, pickup_date, return_date, total_price, status)
            VALUES (?, ?, ?, ?, ?, 'confirmed')
        ");
        $stmt->execute([$userId, $carId, $pickupDate, $returnDate, $totalPrice]);

        $success = true;
        // Calculate number of days for the success message
        $days = (int)(( strtotime($returnDate) - strtotime($pickupDate) ) / 86400);
    }
}

// Include the header
require_once 'includes/header.php';
?>

<!-- ── PAGE HEADER ─────────────────────────────────────────── -->
<div class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">← Back to Cars</a>
        </div>
        <h1>Book Your Car</h1>
        <p>You are booking the <strong><?= htmlspecialchars($car['name']) ?></strong></p>
    </div>
</div>

<section class="section">
    <div class="container">

        <?php if ($success): ?>
            <!-- SHOW THIS MESSAGE AFTER SUCCESSFUL BOOKING -->
            <div class="success-box">
                <div class="success-icon">✅</div>
                <h2>Booking Confirmed!</h2>
                <p>
                    Your car is reserved for <strong><?= $days ?> day<?= $days > 1 ? 's' : '' ?></strong>.
                    <br>Total Price: <strong><?= number_format($totalPrice, 0, '.', ' ') ?> DZD</strong>
                </p>
                <div style="margin-top:2rem;">
                    <a href="my-bookings.php" class="btn-book">View My Bookings</a>
                </div>
            </div>

        <?php else: ?>
            <!-- SHOW THE BOOKING FORM -->
            <div style="max-width:600px; margin:0 auto; background:white; padding:30px; border-radius:8px; border:1px solid #ddd;">
                
                <?php if ($error): ?>
                    <div style="color:red; margin-bottom:20px;">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="booking.php?car_id=<?= $carId ?>">
                    <!-- This hidden field stores the total price calculated by JS -->
                    <input type="hidden" name="total_price" id="total_price" value="0">

                    <div style="margin-bottom:15px;">
                        <label>Pick-up Date:</label>
                        <input type="date" name="pickup_date" id="pickup_date" required style="width:100%; padding:10px; margin-top:5px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label>Return Date:</label>
                        <input type="date" name="return_date" id="return_date" required style="width:100%; padding:10px; margin-top:5px;">
                    </div>

                    <!-- Price calculation display -->
                    <div style="background:#f9f9f9; padding:15px; border-radius:5px; margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>Price per day:</span>
                            <span><?= number_format($car['price'], 0, '.', ' ') ?> DZD</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-weight:bold; margin-top:10px; font-size:1.2rem; color:var(--orange);">
                            <span>Total Price:</span>
                            <span id="totalDisplay">Select dates</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-book" style="width:100%; padding:12px; font-size:1rem; border:none; cursor:pointer;">
                        Confirm Reservation →
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Pass the car price to our JavaScript file -->
<input type="hidden" id="price_per_day" value="<?= (float)$car['price'] ?>">

<?php require_once 'includes/footer.php'; ?>
