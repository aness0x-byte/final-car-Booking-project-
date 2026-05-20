<?php
/* --------------------------------------------------------------------------
   HOMEPAGE (index.php) - WITH QUERY OPTIMIZATION
   --------------------------------------------------------------------------
   This is the main page where users can see available cars.
   ✅ OPTIMIZATION #4: Use column-specific SELECT instead of SELECT *
   -------------------------------------------------------------------------- */

require_once 'config/db.php';

$pageTitle = 'Home';
$basePath  = '';

// ✅ OPTIMIZATION #4: Select only needed columns instead of SELECT *
$db   = getDB();
$stmt = $db->prepare("SELECT id, name, brand, price, image_url FROM cars WHERE name IN ('Audi Q7', 'Honda Accord') AND available = 1 ORDER BY price ASC");
$stmt->execute();
$cars = $stmt->fetchAll();

$totalCars = count($cars);

require_once 'includes/header.php';
?>

<!-- ── HERO SECTION ────────────────────────────────────────── -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Find Your <span>Perfect Car</span></h1>
            <p>Welcome to our simple car rental service. Book your favorite car in just a few clicks.</p>
        </div>

        <div class="hero-stats">
            <div class="stat">
                <div class="stat-number"><?= $totalCars ?></div>
                <div class="stat-label">Available Models</div>
            </div>
            <div class="stat">
                <div class="stat-number">8 000 DZD</div>
                <div class="stat-label">Starting / Day</div>
            </div>
        </div>
    </div>
</section>

<!-- ── CARS SECTION ────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our Featured Cars</h2>
        </div>

        <div class="cars-grid">
            <?php if (empty($cars)): ?>
                <div class="no-results">
                    No cars found in the database. Please check the 'cars' table.
                </div>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <div class="car-card" data-name="<?= htmlspecialchars($car['name']) ?>" data-brand="<?= htmlspecialchars($car['brand']) ?>">
                        <div class="car-image-wrap">
                            <img
                                src="<?= htmlspecialchars($car['image_url']) ?>"
                                alt="<?= htmlspecialchars($car['name']) ?>"
                            >
                        </div>

                        <div class="car-body">
                            <div class="car-name"><?= htmlspecialchars($car['name']) ?></div>
                            <div class="car-brand"><?= htmlspecialchars($car['brand']) ?></div>
                        </div>

                        <div class="car-footer">
                            <div class="car-price"><?= number_format($car['price'], 0, '.', ' ') ?> DZD / day</div>
                            <a href="booking.php?car_id=<?= $car['id'] ?>" class="btn-book">Book Now →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
