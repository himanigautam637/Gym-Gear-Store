<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'shop';

$productId = (int)($_GET['id'] ?? 0);

if ($productId <= 0) {
    header('Location: /Gym-Gear-Store/shop.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        p.product_id,
        p.product_name,
        p.description,
        p.price,
        p.stock,
        p.status,
        c.category_id,
        c.category_name
    FROM products p
    LEFT JOIN categories c ON c.category_id = p.category_id
    WHERE p.product_id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: /Gym-Gear-Store/shop.php');
    exit;
}

$imgStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY image_id ASC");
$imgStmt->execute([$productId]);
$images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

$outOfStock = ($product['status'] === 'Out of Stock') || (int)$product['stock'] <= 0;
$stockMax = max(1, (int)$product['stock']);

$relatedStmt = $pdo->prepare("
    SELECT
        p.product_id, p.product_name, p.price, p.stock, p.status, c.category_name,
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.image_id ASC LIMIT 1) AS thumbnail
    FROM products p
    LEFT JOIN categories c ON c.category_id = p.category_id
    WHERE p.category_id = ? AND p.product_id != ?
    ORDER BY p.product_id DESC
    LIMIT 4
");
$relatedStmt->execute([$product['category_id'], $productId]);
$related = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

$reviewStatsStmt = $pdo->prepare("SELECT COUNT(*) AS review_count, AVG(rating) AS avg_rating FROM reviews WHERE product_id = ?");
$reviewStatsStmt->execute([$productId]);
$reviewStats = $reviewStatsStmt->fetch(PDO::FETCH_ASSOC);
$avgRating = $reviewStats['avg_rating'] ? round((float)$reviewStats['avg_rating'], 1) : 0;
$reviewCount = (int)$reviewStats['review_count'];

$reviewsStmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.full_name
    FROM reviews r
    JOIN users u ON u.user_id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$productId]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

$myReview = null;
if (isset($_SESSION['user_id'])) {
    $myReviewStmt = $pdo->prepare("SELECT rating, comment FROM reviews WHERE product_id = ? AND user_id = ?");
    $myReviewStmt->execute([$productId, $_SESSION['user_id']]);
    $myReview = $myReviewStmt->fetch(PDO::FETCH_ASSOC);
}

$reviewMsg = $_GET['msg'] ?? '';
$reviewErr = $_GET['err'] ?? '';

$reviewsStmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.full_name
    FROM reviews r
    JOIN users u ON u.user_id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$productId]);
$reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

$reviewCount = count($reviews);
$avgRating = 0;
if ($reviewCount > 0) {
    $sum = 0;
    foreach ($reviews as $r) {
        $sum += (int)$r['rating'];
    }
    $avgRating = $sum / $reviewCount;
}

$userHasReviewed = false;
if (isset($_SESSION['user_id'])) {
    $checkStmt = $pdo->prepare("SELECT review_id FROM reviews WHERE product_id = ? AND user_id = ?");
    $checkStmt->execute([$productId, $_SESSION['user_id']]);
    $userHasReviewed = (bool) $checkStmt->fetch();
}

function renderStars($rating, $size = 16) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $filled = $i <= round($rating);
        $color = $filled ? '#FF6B35' : '#4a5665';
        $html .= '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="' . $color . '"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7L12 2z"/></svg>';
    }
    return $html;
}

$reviewMsg = $_GET['msg'] ?? '';
$reviewErr = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($product['product_name']) ?> | Online Gym Gear Store</title>
<link rel="stylesheet" href="/Gym-Gear-Store/partials/site.css">
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main>
    <section class="section">
        <div class="wrap">

            <a href="/Gym-Gear-Store/shop.php" class="btn-ghost" style="display:inline-flex;margin-bottom:24px;">&larr; Back to Shop</a>

            <div class="product-detail-grid">

                <div class="detail-gallery">
                    <div class="detail-main-image" id="mainImageWrap">
                        <?php if ($outOfStock): ?>
                            <div class="stock-tag">Out of Stock</div>
                        <?php endif; ?>
                        <?php if (!empty($images)): ?>
                            <img id="mainImage" src="/Gym-Gear-Store/uploads/products/<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                        <?php else: ?>
                            <div class="placeholder-icon" style="width:100%;height:100%;">+</div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($images) > 1): ?>
                        <div class="detail-thumbs">
                            <?php foreach ($images as $i => $img): ?>
                                <img
                                    class="detail-thumb <?= $i === 0 ? 'active' : '' ?>"
                                    src="/Gym-Gear-Store/uploads/products/<?= htmlspecialchars($img) ?>"
                                    onclick="swapMainImage(this)"
                                >
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="detail-info">

                    <div class="prod-cat"><?= htmlspecialchars($product['category_name'] ?? 'Gym Gear') ?></div>

                    <h1 class="detail-title"><?= htmlspecialchars($product['product_name']) ?></h1>

                    <div class="rating-summary">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= round($avgRating) ? 'filled' : '' ?>">&#9733;</span>
                        <?php endfor; ?>
                        <?php if ($reviewCount > 0): ?>
                            <span class="rating-text"><?= $avgRating ?> out of 5 (<?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?>)</span>
                        <?php else: ?>
                            <span class="rating-text">No reviews yet</span>
                        <?php endif; ?>
                    </div>

                    <div class="detail-price">Rs. <?= number_format((float)$product['price'], 2) ?></div>

                    <div class="detail-stock <?= $outOfStock ? 'out' : 'in' ?>">
                        <?= $outOfStock ? 'Out of Stock' : ((int)$product['stock'] . ' in stock') ?>
                    </div>

                    <div class="detail-description">
                        <?= nl2br(htmlspecialchars($product['description'] ?: 'No description available for this product yet.')) ?>
                    </div>

                    <?php if (!$outOfStock): ?>
                        <form action="/Gym-Gear-Store/Cart/add_to_cart.php" method="POST" class="detail-add-form">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <div class="qty-stepper">
                                <button type="button" class="qty-btn" onclick="detailDec()">&minus;</button>
                                <span class="qty-value" id="detailQty">1</span>
                                <button type="button" class="qty-btn" onclick="detailInc(<?= $stockMax ?>)">+</button>
                            </div>
                            <input type="hidden" name="quantity" id="detailQtyInput" value="1">
                            <button type="submit" name="redirect" value="cart" class="checkout-btn btn-add-to-cart">Add to Cart</button>
                            <button type="submit" name="redirect" value="checkout" class="checkout-btn btn-buy-now">Buy Now</button>
                            <button
                                type="button"
                                class="wishlist-heart static"
                                data-product-id="<?= $product['product_id'] ?>"
                                onclick="toggleWishlist(<?= $product['product_id'] ?>, this)"
                                title="Save to wishlist"
                            >
                                <svg viewBox="0 0 24 24" width="16" height="16"><path d="M12 21s-7.5-4.6-10-9.3C0.3 8 2 4 6 4c2 0 3.5 1 4.5 2.5C11.5 5 13 4 15 4c4 0 5.7 4 4 7.7C19.5 16.4 12 21 12 21z"/></svg>
                            </button>
                        </form>
                    <?php else: ?>
                        <button type="button" class="checkout-btn" disabled>Out of Stock</button>
                    <?php endif; ?>

                </div>

            </div>

            <section class="reviews-section">

                <div class="section-head">
                    <div>
                        <div class="eyebrow">Customer Reviews</div>
                        <h2>What people are saying</h2>
                    </div>
                </div>

                <?php if ($reviewMsg): ?><div class="alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($reviewMsg) ?></div><?php endif; ?>
                <?php if ($reviewErr): ?><div class="alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($reviewErr) ?></div><?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="review-form-box">
                        <h3><?= $myReview ? 'Update Your Review' : 'Write a Review' ?></h3>
                        <form action="/Gym-Gear-Store/Reviews/submit_review.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $productId ?>">

                            <div class="star-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= ($myReview && (int)$myReview['rating'] === $i) ? 'checked' : '' ?> required>
                                    <label for="star<?= $i ?>">&#9733;</label>
                                <?php endfor; ?>
                            </div>

                            <textarea name="comment" placeholder="Share your experience with this product (optional)" class="review-textarea"><?= $myReview ? htmlspecialchars($myReview['comment']) : '' ?></textarea>

                            <button type="submit" class="checkout-btn"><?= $myReview ? 'Update Review' : 'Submit Review' ?></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="review-form-box review-login-prompt">
                        <p>Want to leave a review? <a href="/Gym-Gear-Store/Authentication/client_login.php">Log in</a> first.</p>
                    </div>
                <?php endif; ?>

                <div class="review-list">
                    <?php if (empty($reviews)): ?>
                        <p class="no-reviews">Be the first to review this product.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="review-item">
                                <div class="review-item-head">
                                    <span class="review-name"><?= htmlspecialchars($r['full_name']) ?></span>
                                    <span class="review-date"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
                                </div>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= $r['rating'] ? 'filled' : '' ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </div>
                                <?php if (!empty($r['comment'])): ?>
                                    <p class="review-comment"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </section>

            <?php if (!empty($related)): ?>
                <div class="section-head" style="margin-top:70px;">
                    <div>
                        <div class="eyebrow">You May Also Like</div>
                        <h2>More in <?= htmlspecialchars($product['category_name'] ?? 'this category') ?></h2>
                    </div>
                </div>
                <div class="prod-grid">
                    <?php foreach ($related as $p): include __DIR__ . '/partials/product-card.php'; endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>

<script>
function swapMainImage(el) {
    document.getElementById('mainImage').src = el.src;
    document.querySelectorAll('.detail-thumb').forEach(function (t) { t.classList.remove('active'); });
    el.classList.add('active');
}
function detailInc(max) {
    var el = document.getElementById('detailQty');
    var val = Math.min(parseInt(el.textContent, 10) + 1, max);
    el.textContent = val;
    document.getElementById('detailQtyInput').value = val;
}
function detailDec() {
    var el = document.getElementById('detailQty');
    var val = Math.max(parseInt(el.textContent, 10) - 1, 1);
    el.textContent = val;
    document.getElementById('detailQtyInput').value = val;
}
</script>

</body>
</html>