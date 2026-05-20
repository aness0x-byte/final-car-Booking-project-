<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

$pageTitle = $car['make'] . ' ' . $car['model'];
$basePath  = '';

$error = '';
$success = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $score = (int)($_POST['score'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');

    if ($score < 1 || $score > 5) {
        $error = 'Please select a valid rating from 1 to 5 stars.';
    } else {
        // Check if user has already rated this car
        $checkStmt = $db->prepare("SELECT id FROM ratings WHERE user_id = ? AND car_id = ?");
        $checkStmt->execute([$_SESSION['user_id'], $carId]);
        
        if ($checkStmt->fetch()) {
            $error = 'You have already reviewed this car.';
        } else {
            // Check if user actually completed a reservation for this car
            // Usually we only let people review if they rented it.
            $bookCheck = $db->prepare("SELECT id FROM reservations WHERE user_id = ? AND car_id = ? AND status = 'completed' LIMIT 1");
            $bookCheck->execute([$_SESSION['user_id'], $carId]);
            
            if (!$bookCheck->fetch()) {
                $error = 'You can only review cars you have successfully rented and completed.';
            } else {
                $ins = $db->prepare("INSERT INTO ratings (user_id, car_id, score, review_text) VALUES (?, ?, ?, ?)");
                $ins->execute([$_SESSION['user_id'], $carId, $score, $review_text]);
                $success = 'Thank you! Your review has been submitted.';
            }
        }
    }
}

// Fetch all reviews
$reviewsStmt = $db->prepare("
    SELECT r.*, u.full_name 
    FROM ratings r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.car_id = ? 
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$carId]);
$reviews = $reviewsStmt->fetchAll();

// Calculate average rating
$avgRating = 0;
if (count($reviews) > 0) {
    $totalScore = array_sum(array_column($reviews, 'score'));
    $avgRating = round($totalScore / count($reviews), 1);
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <div class="breadcrumb"><a href="index.php">← Back to Cars</a></div>
        <h1><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></h1>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 4rem;">
            <!-- Car Image -->
            <div>
                <?php if ($car['image_url']): ?>
                    <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['make']) ?>" style="width: 100%; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                <?php else: ?>
                    <div style="width: 100%; height: 350px; background: #e5e7eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #9ca3af;">No Image</div>
                <?php endif; ?>
            </div>

            <!-- Car Details -->
            <div>
                <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></h2>
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="color: #fbbf24; font-size: 1.25rem;">
                        <?php 
                        for($i=1; $i<=5; $i++) {
                            echo $i <= round($avgRating) ? '★' : '☆';
                        }
                        ?>
                    </div>
                    <span style="color: var(--gray-mid); font-weight: bold;"><?= $avgRating ?>/5 (<?= count($reviews) ?> reviews)</span>
                </div>

                <div style="font-size: 2rem; color: var(--primary-color); font-weight: bold; margin-bottom: 2rem;">
                    <?= number_format($car['daily_rate'], 0, '.', ' ') ?> DZD <span style="font-size: 1rem; color: var(--gray-mid); font-weight: normal;">/ day</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        <div style="color: var(--gray-mid); font-size: 0.85rem; text-transform: uppercase;">Category</div>
                        <div style="font-weight: bold; font-size: 1.1rem;"><?= htmlspecialchars($car['category']) ?></div>
                    </div>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        <div style="color: var(--gray-mid); font-size: 0.85rem; text-transform: uppercase;">Year</div>
                        <div style="font-weight: bold; font-size: 1.1rem;"><?= htmlspecialchars($car['model_year']) ?></div>
                    </div>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        <div style="color: var(--gray-mid); font-size: 0.85rem; text-transform: uppercase;">Transmission</div>
                        <div style="font-weight: bold; font-size: 1.1rem;"><?= htmlspecialchars($car['transmission_type']) ?></div>
                    </div>
                    <div style="background: #f9fafb; padding: 1rem; border-radius: 8px;">
                        <div style="color: var(--gray-mid); font-size: 0.85rem; text-transform: uppercase;">Fuel & Seats</div>
                        <div style="font-weight: bold; font-size: 1.1rem;"><?= htmlspecialchars($car['fuel_type']) ?> • <?= htmlspecialchars($car['number_of_seats']) ?> Seats</div>
                    </div>
                </div>

                <?php if ($car['booking_status'] !== 'available'): ?>
                    <button class="btn btn-primary" style="width: 100%; text-align: center; font-size: 1.25rem; padding: 1rem; border-radius: 8px; background: #9ca3af; cursor: not-allowed;" disabled>Currently Booked</button>
                    <p style="text-align: center; color: var(--gray-mid); margin-top: 0.5rem; font-size: 0.9rem;">This car is currently out on a reservation.</p>
                <?php else: ?>
                    <a href="booking.php?car_id=<?= $carId ?>" class="btn btn-primary" style="width: 100%; text-align: center; font-size: 1.25rem; padding: 1rem; border-radius: 8px;">Reserve This Car →</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Reviews Section -->
        <div style="border-top: 1px solid #eee; padding-top: 3rem;">
            <h2>Customer Reviews</h2>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem; margin-top: 2rem;">
                <!-- Review Form -->
                <div>
                    <div class="form-card" style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h3 style="margin-bottom: 1.5rem;">Leave a Review</h3>
                        
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="alert alert-info" style="font-size: 0.9rem;">Please <a href="login.php" style="color:var(--primary-color); font-weight:bold;">sign in</a> to leave a review.</div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-error" style="margin-bottom:1rem;">Error: <?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success" style="background:#dcfce7;color:#166534;padding:1rem;border-radius:4px;margin-bottom:1rem;">Success: <?= htmlspecialchars($success) ?></div>
                            <?php endif; ?>

                            <form method="post" action="car-details.php?id=<?= $carId ?>">
                                <div class="form-group" style="margin-bottom: 1rem;">
                                    <label>Rating (1-5 Stars)</label>
                                    <select name="score" required style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1.2rem;">
                                        <option value="5">★★★★★ (5/5)</option>
                                        <option value="4">★★★★☆ (4/5)</option>
                                        <option value="3">★★★☆☆ (3/5)</option>
                                        <option value="2">★★☆☆☆ (2/5)</option>
                                        <option value="1">★☆☆☆☆ (1/5)</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 1.5rem;">
                                    <label>Your Review</label>
                                    <textarea name="review_text" rows="4" required style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; resize: vertical;" placeholder="Tell us about your experience..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Review</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Review List -->
                <div>
                    <?php if (empty($reviews)): ?>
                        <p style="color: var(--gray-mid);">No reviews yet. Be the first to review this car after renting!</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <?php foreach ($reviews as $rev): ?>
                                <div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                        <strong style="font-size: 1.1rem;"><?= htmlspecialchars($rev['full_name']) ?></strong>
                                        <span style="color: #fbbf24; font-size: 1.1rem;">
                                            <?php 
                                            for($i=1; $i<=5; $i++) {
                                                echo $i <= $rev['score'] ? '★' : '☆';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.85rem; color: var(--gray-mid); margin-bottom: 1rem;">
                                        <?= date('F j, Y', strtotime($rev['created_at'])) ?>
                                    </div>
                                    <p style="line-height: 1.5; color: #444;"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
