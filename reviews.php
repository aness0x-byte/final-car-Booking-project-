<?php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Customer Reviews';
$basePath  = '';

$db = getDB();

// Fetch all reviews, newest first
$stmt = $db->query("
    SELECT r.*, u.full_name, c.make, c.model, c.image_url 
    FROM ratings r 
    JOIN users u ON r.user_id = u.id 
    JOIN cars c ON r.car_id = c.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();

// Calculate global average rating
$avgRating = 0;
if (count($reviews) > 0) {
    $totalScore = array_sum(array_column($reviews, 'score'));
    $avgRating = round($totalScore / count($reviews), 1);
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Customer Reviews</h1>
        <p>See what our drivers are saying about our fleet.</p>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container">
        
        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 4rem; background: #f9fafb; border-radius: 12px;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                <h3 style="color: var(--dark);">No Reviews Yet</h3>
                <p style="color: var(--gray-mid); margin-top: 0.5rem;">Check back later to read customer experiences.</p>
            </div>
        <?php else: ?>
            <!-- Stats overview -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fff; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 3rem; text-align: center;">
                <div style="font-size: 4rem; font-weight: bold; color: var(--dark); line-height: 1;"><?= $avgRating ?></div>
                <div style="color: #fbbf24; font-size: 1.5rem; margin: 0.5rem 0;">
                    <?php 
                    for($i=1; $i<=5; $i++) {
                        echo $i <= round($avgRating) ? '★' : '☆';
                    }
                    ?>
                </div>
                <div style="color: var(--gray-mid);">Based on <?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?></div>
            </div>

            <!-- Reviews Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem;">
                <?php foreach ($reviews as $rev): ?>
                    <div style="background: #fff; border: 1px solid #eee; border-radius: 12px; padding: 1.5rem; display: flex; flex-direction: column; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                        <!-- User & Rating -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div>
                                <div style="font-weight: bold; font-size: 1.1rem; color: var(--dark);"><?= htmlspecialchars($rev['full_name']) ?></div>
                                <div style="font-size: 0.85rem; color: var(--gray-mid); margin-top: 0.2rem;">
                                    <?= date('F j, Y', strtotime($rev['created_at'])) ?>
                                </div>
                            </div>
                            <div style="color: #fbbf24; font-size: 1.2rem;">
                                <?php 
                                for($i=1; $i<=5; $i++) {
                                    echo $i <= $rev['score'] ? '★' : '☆';
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Review Text -->
                        <p style="color: #4b5563; line-height: 1.6; margin-bottom: 1.5rem; flex-grow: 1;">
                            "<?= nl2br(htmlspecialchars($rev['review_text'])) ?>"
                        </p>

                        <!-- Car Reference -->
                        <div style="display: flex; align-items: center; gap: 1rem; padding-top: 1rem; border-top: 1px solid #f3f4f6;">
                            <?php if ($rev['image_url']): ?>
                                <img src="<?= htmlspecialchars($rev['image_url']) ?>" alt="<?= htmlspecialchars($rev['make']) ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <div style="width: 60px; height: 40px; background: #e5e7eb; border-radius: 4px;"></div>
                            <?php endif; ?>
                            <div>
                                <div style="font-size: 0.85rem; color: var(--gray-mid);">Rented Car</div>
                                <a href="car-details.php?id=<?= $rev['car_id'] ?>" style="font-weight: 600; color: var(--primary-color); text-decoration: none;">
                                    <?= htmlspecialchars($rev['make'] . ' ' . $rev['model']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
