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