<?php
$outOfStock =
    (($p['status'] ?? '') === 'Out of Stock') ||
    (int)($p['stock'] ?? 0) <= 0;
?>

<div class="prod-card">

    <div class="prod-image">

        <?php if ($outOfStock): ?>

            <div class="stock-tag">
                Out of Stock
            </div>

        <?php endif; ?>

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

    </div>

    <div class="prod-body">

        <div class="prod-cat">
            <?= htmlspecialchars($p['category_name'] ?? 'Gym Gear') ?>
        </div>

        <div class="prod-name">
            <?= htmlspecialchars($p['product_name']) ?>
        </div>

        <div class="prod-footer">

            <span class="prod-price">
                Rs. <?= number_format((float)$p['price'], 2) ?>
            </span>

            <form
                action="/Gym-Gear-Store/Cart/add_to_cart.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="product_id"
                    value="<?= (int)$p['product_id'] ?>"
                >

                <input
                    type="hidden"
                    name="quantity"
                    value="1"
                >

                <button
                    type="submit"
                    class="btn-add"
                    <?= $outOfStock ? 'disabled' : '' ?>
                >
                    Add
                </button>

            </form>

        </div>

    </div>

</div>