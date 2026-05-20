<?php
require_once 'config/db.php';

$pageTitle = 'Find Your Perfect Ride';
$basePath  = '';

$db = getDB();

// Handle Advanced Search Filters
$whereClauses = ["1=1"];
$params = [];

if (!empty($_GET['make_model'])) {
    $whereClauses[] = "(make LIKE ? OR model LIKE ?)";
    $searchTerm = '%' . $_GET['make_model'] . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($_GET['category'])) {
    $whereClauses[] = "category = ?";
    $params[] = $_GET['category'];
}

if (!empty($_GET['max_budget'])) {
    $whereClauses[] = "daily_rate <= ?";
    $params[] = (float)$_GET['max_budget'];
}

$whereSql = implode(' AND ', $whereClauses);

$stmt = $db->prepare("SELECT * FROM cars WHERE $whereSql ORDER BY daily_rate ASC");
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Get unique categories for the filter dropdown
$categoriesStmt = $db->query("SELECT DISTINCT category FROM cars WHERE category != ''");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<style>
/* Background Video Styles */
.video-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -1;
    filter: brightness(0.4); /* Darken video to make text readable */
}

.hero {
    position: relative;
    padding: 6rem 0;
    color: #fff;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
    max-width: 800px;
}

.hero h1 {
    font-size: 3rem;
    margin-bottom: 1rem;
    font-weight: 800;
}

.hero p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    color: #eee;
}

/* Advanced Search Filter */
.advanced-search {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 2rem;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.search-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.search-group {
    display: flex;
    flex-direction: column;
    text-align: left;
}

.search-group label {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.search-group input, .search-group select {
    padding: 0.75rem 1rem;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    font-size: 1rem;
}

.search-group button {
    padding: 0.75rem 1.5rem;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
}

.search-group button:hover {
    background: #2563eb;
}
</style>

<section class="hero">
    <!-- Local Background Image -->
    <img src="images/hero.png" alt="Hero Background" class="video-background" style="object-position: center; filter: brightness(0.5);">

    <div class="container">
        <div class="hero-content">
            <h1>Find Your <span>Perfect Ride</span></h1>
            <p>Experience the thrill of driving with our premium car rental service.</p>

            <!-- Advanced Search Form -->
            <div class="advanced-search">
                <form method="GET" action="index.php" class="search-grid">
                    <div class="search-group">
                        <label for="make_model">Make & Model</label>
                        <input type="text" name="make_model" id="make_model" placeholder="e.g., Toyota Camry" value="<?= htmlspecialchars($_GET['make_model'] ?? '') ?>">
                    </div>
                    
                    <div class="search-group">
                        <label for="category">Category</label>
                        <select name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_GET['category']) && $_GET['category'] === $cat) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="search-group">
                        <label for="max_budget">Max Budget (DZD/Day)</label>
                        <input type="number" step="0.01" name="max_budget" id="max_budget" placeholder="e.g., 5000" value="<?= htmlspecialchars($_GET['max_budget'] ?? '') ?>">
                    </div>

                    <div class="search-group">
                        <button type="submit">Search Cars</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section" style="padding-top: 4rem;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Available Cars</h2>
        </div>

        <div class="cars-grid">
            <?php if (empty($cars)): ?>
                <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: #f9fafb; border-radius: 8px;">
                    <h3 style="color: var(--gray-mid);">No cars match your search criteria.</h3>
                    <p style="margin-top: 0.5rem;"><a href="index.php" style="color: var(--primary-color);">Clear filters</a> to see all available cars.</p>
                </div>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <div class="car-image-wrap" style="position: relative;">
                            <?php if ($car['booking_status'] !== 'available'): ?>
                                <div style="position: absolute; top: 1rem; right: 1rem; background: rgba(239, 68, 68, 0.9); color: white; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.85rem; font-weight: bold; z-index: 10;">
                                    Currently Booked
                                </div>
                            <?php endif; ?>
                            <?php if ($car['image_url']): ?>
                                <img src="<?= htmlspecialchars($car['image_url']) ?>" alt="<?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 200px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">No Image</div>
                            <?php endif; ?>
                        </div>

                        <div class="car-body" style="padding: 1.5rem;">
                            <div class="car-name" style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.25rem;"><?= htmlspecialchars($car['make'] . ' ' . $car['model']) ?></div>
                            <div class="car-brand" style="color: var(--gray-mid); font-size: 0.9rem; margin-bottom: 1rem;">
                                <?= htmlspecialchars($car['model_year']) ?> • <?= htmlspecialchars($car['category']) ?> • <?= htmlspecialchars($car['transmission_type']) ?>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                                <span class="badge" style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?= htmlspecialchars($car['fuel_type']) ?></span>
                                <span class="badge" style="background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?= htmlspecialchars($car['number_of_seats']) ?> Seats</span>
                            </div>
                        </div>

                        <div class="car-footer" style="padding: 1.5rem; border-top: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                            <div class="car-price">
                                <span class="price-amount" style="font-size: 1.25rem; font-weight: bold; color: var(--primary-color);"><?= number_format($car['daily_rate'], 0, '.', ' ') ?> DZD</span>
                                <span class="price-unit" style="color: var(--gray-mid); font-size: 0.85rem;">/day</span>
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                <a href="car-details.php?id=<?= (int)$car['id'] ?>" class="btn btn-outline" style="border: 1px solid var(--primary-color); color: var(--primary-color); padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none;">Details</a>
                                <?php if ($car['booking_status'] !== 'available'): ?>
                                    <button class="btn btn-primary" style="background: #9ca3af; cursor: not-allowed;" disabled>Booked</button>
                                <?php else: ?>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="booking.php?car_id=<?= (int)$car['id'] ?>" class="btn btn-primary">Book Now</a>
                                    <?php else: ?>
                                        <a href="login.php?redirect=booking.php%3Fcar_id%3D<?= (int)$car['id'] ?>" class="btn btn-primary">Book Now</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
