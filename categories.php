<?php

session_start();

require $_SERVER['DOCUMENT_ROOT'] . '/Gym-Gear-Store/db_connect.php';

$currentPage = 'categories';

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

    $categories =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $categories = [];

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

    <title>
        Categories | Online Gym Gear Store
    </title>

    <link
        rel="stylesheet"
        href="/Gym-Gear-Store/partials/site.css"
    >

</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<main>

    <section class="page-header">

        <div class="wrap">

            <div class="eyebrow">
                Explore
            </div>

            <h1>
                Categories
            </h1>

            <p>
                Browse gym equipment and products by category.
            </p>

        </div>

    </section>


    <section class="section">

        <div class="wrap">

            <?php if (empty($categories)): ?>

                <div class="content-panel">

                    <h2>
                        No categories available
                    </h2>

                    <p>
                        Categories added from the admin panel
                        will appear here.
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
                                +
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
                                        $category['description']
                                        ?? 'Explore this collection.'
                                    ) ?>

                                </p>

                            </div>

                        </a>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>


<?php include __DIR__ . '/partials/footer.php'; ?>


</body>
</html>