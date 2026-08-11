<?php
session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'index.php';

$categories = [];

try {
    $stmt = $pdo->query("
        SELECT
            c.category_id,
            c.category_name,
            c.description,
            (
                SELECT COUNT(*)
                FROM products p
                WHERE p.category_id = c.category_id
            ) AS product_count
        FROM categories c
        ORDER BY c.category_id ASC
    ");

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

$bestSellers = [];

try {
    $stmt = $pdo->query("
        SELECT
            p.product_id,
            p.product_name,
            p.price,
            p.stock,
            p.status,
            c.category_name,
            (
                SELECT pi.image_path
                FROM product_images pi
                WHERE pi.product_id = p.product_id
                ORDER BY pi.image_id ASC
                LIMIT 1
            ) AS thumbnail,
            COALESCE(
                (
                    SELECT SUM(oi.quantity)
                    FROM order_items oi
                    WHERE oi.product_id = p.product_id
                ),
                0
            ) AS sold_quantity
        FROM products p
        LEFT JOIN categories c
            ON c.category_id = p.category_id
        ORDER BY sold_quantity DESC, p.product_id DESC
        LIMIT 8
    ");

    $bestSellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $bestSellers = [];
}

$newArrivals = [];

try {
    $stmt = $pdo->query("
        SELECT
            p.product_id,
            p.product_name,
            p.price,
            p.stock,
            p.status,
            c.category_name,
            (
                SELECT pi.image_path
                FROM product_images pi
                WHERE pi.product_id = p.product_id
                ORDER BY pi.image_id ASC
                LIMIT 1
            ) AS thumbnail
        FROM products p
        LEFT JOIN categories c
            ON c.category_id = p.category_id
        ORDER BY p.product_id DESC
        LIMIT 8
    ");

    $newArrivals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $newArrivals = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Online Gym Gear Store</title>

    <link
        rel="stylesheet"
        href="/Gym-Gear-Store/partials/site.css"
    >

</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main>

    <!-- HERO -->

    <section class="hero">

        <div class="hero-inner">

            <div>

                <div class="pill">

                    <span class="dot"></span>

                    Built for the relentless

                </div>

                <h1>
                    Forge the body.<br>
                    Own the <span>iron.</span>
                </h1>

                <p class="lead">
                    Premium gym equipment, strength machines,
                    cardio equipment, accessories and supplements
                    built for athletes who never settle for average.
                </p>

                <div class="hero-ctas">

                    <a
                        href="/Gym-Gear-Store/shop.php"
                        class="btn-solid"
                    >
                        Shop Now
                        <span>→</span>
                    </a>

                    <a
                        href="/Gym-Gear-Store/categories.php"
                        class="btn-ghost"
                    >
                        Explore Categories
                    </a>

                </div>

            </div>

            <div class="hero-media">

                <div class="hero-frame">

                    <img
                        src="/Gym-Gear-Store/images/hero-athlete.jpg"
                        alt="Athlete training with gym equipment"
                    >

                </div>

                <div class="floating-card">

                    <div class="badge-icon">
                        ✓
                    </div>

                    <div>

                        <strong>
                            Quality Assured
                        </strong>

                        <small>
                            Every product checked
                        </small>

                    </div>

                </div>

                <div class="floating-chip">
                    🛡 10-Year Warranty
                </div>

            </div>

        </div>

    </section>


    <section class="section" id="best-sellers">

        <div class="wrap">

            <div class="section-head">

                <div>

                    <div class="eyebrow">
                        Most Wanted
                    </div>

                    <h2>
                        Best Selling Products
                    </h2>

                </div>

                <a
                    href="/Gym-Gear-Store/shop.php"
                    class="btn-ghost"
                    style="padding:12px 20px;font-size:14px;"
                >
                    View All
                </a>

            </div>

            <?php if (empty($bestSellers)): ?>

                <div class="content-panel">

                    <p>
                        Products will appear here once they are added
                        from the admin panel.
                    </p>

                </div>

            <?php else: ?>

                <div class="prod-grid">

                    <?php foreach ($bestSellers as $p): ?>

                        <?php include __DIR__ . '/partials/product-card.php'; ?>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <section class="section" id="categories">

        <div class="wrap">

            <div class="section-head">

                <div>

                    <div class="eyebrow">
                        Shop By Categories
                    </div>

                    <h2>
                        Everything Your Training Demands
                    </h2>

                </div>

                <a
                    href="/Gym-Gear-Store/categories.php"
                    class="btn-ghost"
                    style="padding:12px 20px;font-size:14px;"
                >
                    All Categories
                </a>

            </div>

            <?php if (empty($categories)): ?>

                <div class="content-panel">

                    <p>
                        Categories will appear here once they are
                        added from the admin panel.
                    </p>

                </div>

            <?php else: ?>

                <div class="cat-grid">

                    <?php foreach ($categories as $category): ?>

                        <a
                            href="/Gym-Gear-Store/shop.php?category=<?= (int)$category['category_id'] ?>"
                            class="cat-card"
                        >

                            <div class="cat-icon">

                                <svg
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="white"
                                    stroke-width="2"
                                >

                                    <rect
                                        x="1.5"
                                        y="9"
                                        width="3"
                                        height="6"
                                        rx="1"
                                    />

                                    <rect
                                        x="19.5"
                                        y="9"
                                        width="3"
                                        height="6"
                                        rx="1"
                                    />

                                    <line
                                        x1="6.7"
                                        y1="12"
                                        x2="17.3"
                                        y2="12"
                                    />

                                </svg>

                            </div>

                            <div>

                                <div class="cat-count">

                                    <?= (int)$category['product_count'] ?>

                                    products

                                </div>

                                <h3>
                                    <?= htmlspecialchars(
                                        $category['category_name']
                                    ) ?>
                                </h3>

                                <p>

                                    <?= htmlspecialchars(
                                        mb_strimwidth(
                                            $category['description']
                                            ?? 'Explore this collection.',
                                            0,
                                            80,
                                            '...'
                                        )
                                    ) ?>

                                </p>

                            </div>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>



    <section class="trust-section">

        <div class="wrap">

            <div
                style="text-align:center;margin-bottom:40px;"
            >

                <div class="eyebrow">
                    Why Online Gym Gear Store
                </div>

                <h2>
                    Premium In Every Detail
                </h2>

            </div>

            <div class="trust-grid">

                <div class="trust-card">

                    <div class="trust-icon">
                        $
                    </div>

                    <h3>
                        Cash on Delivery
                    </h3>

                    <p>
                        Pay only when your gear arrives at your door.
                    </p>

                </div>

                <div class="trust-card">

                    <div class="trust-icon">
                        ✓
                    </div>

                    <h3>
                        Quality Assured
                    </h3>

                    <p>
                        Every product listed is checked for quality
                        before shipping.
                    </p>

                </div>

                <div class="trust-card">

                    <div class="trust-icon">
                        🚚
                    </div>

                    <h3>
                        Nationwide Delivery
                    </h3>

                    <p>
                        We deliver your equipment directly to your gym
                        or home.
                    </p>

                </div>

                <div class="trust-card">

                    <div class="trust-icon">
                        ?
                    </div>

                    <h3>
                        Dedicated Support
                    </h3>

                    <p>
                        Questions about an order? Our team is here to help.
                    </p>

                </div>

                <div class="trust-card">

                    <div class="trust-icon">
                        ★
                    </div>

                    <h3>
                        10-Year Warranty
                    </h3>

                    <p>
                        Long-term coverage on selected strength and
                        cardio equipment.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <?php if (!empty($newArrivals)): ?>

        <section class="section">

            <div class="wrap">

                <div class="section-head">

                    <div>

                        <div class="eyebrow">
                            Just Dropped
                        </div>

                        <h2>
                            New Arrivals
                        </h2>

                    </div>

                </div>

                <div class="prod-grid">

                    <?php foreach ($newArrivals as $p): ?>

                        <?php include __DIR__ . '/partials/product-card.php'; ?>

                    <?php endforeach; ?>

                </div>

            </div>

        </section>

    <?php endif; ?>

</main>


<?php include __DIR__ . '/partials/footer.php'; ?>


</body>
</html>