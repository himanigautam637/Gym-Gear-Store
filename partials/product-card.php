<?php
$outOfStock =
    (($p['status'] ?? '') === 'Out of Stock') ||
    (int)($p['stock'] ?? 0) <= 0;

$stockMax = max(1, (int)($p['stock'] ?? 1));
$uid = 'pc_' . $p['product_id'];
?>

<div class="prod-card">

    <div class="prod-image">

        <button
            type="button"
            class="wishlist-heart"
            data-product-id="<?= (int)$p['product_id'] ?>"
            onclick="toggleWishlist(<?= (int)$p['product_id'] ?>, this)"
            title="Add to wishlist"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.8 4.6c-2-2-5.2-2-7.1 0L12 6.3l-1.7-1.7c-2-2-5.2-2-7.1 0-2 2-2 5.2 0 7.1L12 20.4l8.8-8.7c2-2 2-5.2 0-7.1z"/>
            </svg>
        </button>

        <?php if ($outOfStock): ?>

            <div class="stock-tag">
                Out of Stock
            </div>

        <?php endif; ?>

        <a href="/Gym-Gear-Store/product-details.php?id=<?= (int)$p['product_id'] ?>" class="prod-image-link">

        <?php if (!empty($p['thumbnail'])): ?>

            <img
                src="/Gym-Gear-Store/uploads/products/<?= htmlspecialchars($p['thumbnail']) ?>"
                alt="<?= htmlspecialchars($p['product_name']) ?>"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >

            <div
                class="placeholder-icon"
                style="display:none;"
            >
                +
            </div>

        <?php else: ?>

            <div class="placeholder-icon">
                +
            </div>

        <?php endif; ?>

        </a>

    </div>

    <div class="prod-body">

        <div class="prod-cat">
            <?= htmlspecialchars($p['category_name'] ?? 'Gym Gear') ?>
        </div>

        <a href="/Gym-Gear-Store/product-details.php?id=<?= (int)$p['product_id'] ?>" class="prod-name">
            <?= htmlspecialchars($p['product_name']) ?>
        </a>

        <div class="prod-footer">

            <span class="prod-price">
                Rs. <?= number_format((float)$p['price'], 2) ?>
            </span>

            <?php if (!$outOfStock): ?>

                <div class="prod-add-row">

                    <div class="qty-stepper">
                        <button type="button" class="qty-btn" onclick="pcDec('<?= $uid ?>')">&minus;</button>
                        <span class="qty-value" id="<?= $uid ?>_qty">1</span>
                        <button type="button" class="qty-btn" onclick="pcInc('<?= $uid ?>', <?= $stockMax ?>)">+</button>
                    </div>

                    <form
                        action="/Gym-Gear-Store/Cart/add_to_cart.php"
                        method="POST"
                        id="<?= $uid ?>_form"
                    >
                        <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                        <input type="hidden" name="quantity" id="<?= $uid ?>_input" value="1">
                        <button type="submit" class="btn-add">Add</button>
                    </form>

                </div>

            <?php else: ?>

                <button type="button" class="btn-add" disabled>Add</button>

            <?php endif; ?>

        </div>

    </div>

</div>

<script>
function pcInc(uid, max) {
    var el = document.getElementById(uid + '_qty');
    var val = Math.min(parseInt(el.textContent, 10) + 1, max);
    el.textContent = val;
    document.getElementById(uid + '_input').value = val;
}
function pcDec(uid) {
    var el = document.getElementById(uid + '_qty');
    var val = Math.max(parseInt(el.textContent, 10) - 1, 1);
    el.textContent = val;
    document.getElementById(uid + '_input').value = val;
}
</script>