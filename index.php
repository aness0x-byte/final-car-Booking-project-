<?php
/* --------------------------------------------------------------------------
   HOMEPAGE (index.php)
   --------------------------------------------------------------------------
   This is the main page where users can see available cars.
   We have simplified this page to show only 2 specific cars.
   -------------------------------------------------------------------------- */

// Include the database connection file
require_once 'config/db.php';

// Set the page title
$pageTitle = 'Home';
$basePath  = '';

// 1. Get ONLY the two specific cars from the database: "Audi Q7" and "Honda Accord"
$db   = getDB();
$stmt = $db->prepare("SELECT * FROM cars WHERE name IN ('Audi Q7', 'Honda Accord') AND available = 1 ORDER BY price ASC");
$stmt->execute();
$cars = $stmt->fetchAll();

// Count how many cars we found
$totalCars = count($cars);

// 2. Include the header (this adds the top menu bar to our page)
require_once 'includes/header.php';
?>

<!-- ── HERO SECTION — The big banner at the top ────────────────── -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <!-- Main heading -->
            <h1>Find Your <span>Perfect Car</span></h1>
            <!-- Short description -->
            <p>Welcome to our simple car rental service. Book your favorite car in just a few clicks.</p>
        </div>

        <!-- Stats bar — shows how many cars we have and the starting price -->
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

<!-- ── CARS SECTION — Shows the cars in a grid ─────────────────── -->
<section class="section">
    <div class="container">

        <!-- Section heading -->
        <div class="section-header">
            <h2 class="section-title">Our Featured Cars</h2>
        </div>

        <!-- Car grid — each car is shown inside a 'car-card' -->
        <div class="cars-grid">

            <?php if (empty($cars)): ?>
                <!-- If for some reason the database is empty -->
                <div class="no-results">
                    No cars found in the database. Please check the 'cars' table.
                </div>

            <?php else: ?>
                <!-- Loop through each car found in the database and create a card for it -->
                <?php foreach ($cars as $car): ?>

                    <div class="car-card">
                        <!-- 1. Car Image (Uses local image from 'images' folder) -->
                        <div class="car-image-wrap">
                            <img
                                src="<?= htmlspecialchars($car['image_url']) ?>"
                                alt="<?= htmlspecialchars($car['name']) ?>"
                            >
                        </div>

                        <!-- 2. Car Body (Shows name and brand only) -->
                        <div class="car-body">
                            <div class="car-name"><?= htmlspecialchars($car['name']) ?></div>
                            <div class="car-brand"><?= htmlspecialchars($car['brand']) ?></div>
                        </div>

                        <!-- 3. Car Footer (Shows price and the 'Book Now' button) -->
                        <div class="car-footer">
                            <div class="car-price">
                                <!-- Currency is changed to Algerian Dinar (DZD) -->
                                <span class="price-amount"><?= number_format($car['price'], 0, '.', ' ') ?> DZD</span>
                                <span class="price-unit">/day</span>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <!-- If user is logged in: go directly to booking page -->
                                <a href="booking.php?car_id=<?= (int)$car['id'] ?>" class="btn-book">
                                    Book Now →
                                </a>
                            <?php else: ?>
                                <!-- If NOT logged in: go to login first -->
                                <a href="login.php?redirect=booking.php%3Fcar_id%3D<?= (int)$car['id'] ?>" class="btn-book">
                                    Book Now →
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php endif; ?>

        </div><!-- end cars-grid -->
    </div>
</section>

<!-- Include the footer (the bottom part of the page) -->
<?php require_once 'includes/footer.php'; ?>
